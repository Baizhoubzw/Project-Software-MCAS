<?php
session_start();
include 'db.php';

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 搜索与筛选
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$brand = isset($_GET['brand']) ? $_GET['brand'] : 'all';
$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$perPage = 12; 
$offset = ($page-1)*$perPage;

// 获取类别和品牌选项
$categoryResult = $conn->query("SELECT DISTINCT category FROM products ORDER BY category ASC");
$categories = [];
while ($row = $categoryResult->fetch_assoc()) $categories[] = $row['category'];

$brandResult = $conn->query("SELECT DISTINCT brand FROM products ORDER BY brand ASC");
$brands = [];
while ($row = $brandResult->fetch_assoc()) $brands[] = $row['brand'];

// SQL 动态构建
$sql = "FROM products WHERE status='active' AND name LIKE ?";
$params = ["%$search%"];
$types = "s";

if ($category !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}
if ($brand !== 'all') {
    $sql .= " AND brand = ?";
    $params[] = $brand;
    $types .= "s";
}

// 获取总数
$stmt = $conn->prepare("SELECT COUNT(*) as cnt $sql");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$totalProducts = $res['cnt'];
$totalPages = ceil($totalProducts/$perPage);

// 获取当前页产品
$sqlFull = "SELECT * $sql ORDER BY is_hot DESC, is_recommend DESC, is_new DESC, name ASC LIMIT ?, ?";
$params[] = $offset;
$params[] = $perPage;
$types .= "ii";

$stmt = $conn->prepare($sqlFull);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result();

// 获取购物车数量
$cartCount = $conn->query("SELECT SUM(quantity) as cnt FROM cart WHERE user_id=$user_id")->fetch_assoc()['cnt'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Products - MCAS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

*{margin:0;padding:0;box-sizing:border-box;font-family:'Roboto',sans-serif;}
body{background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);color:#fff;min-height:100vh;display:flex;flex-direction:column;}

/* Header */
header{display:flex;justify-content:space-between;align-items:center;padding:15px 40px;background:linear-gradient(90deg,#111,#1a1a1a);position:sticky;top:0;z-index:1000;box-shadow:0 4px 20px rgba(0,0,0,0.6);}
header .logo{font-size:28px;font-weight:700;color:#00ffff;letter-spacing:1px;text-shadow:0 0 10px #00ffff;}
nav{display:flex;align-items:center;gap:15px;position:relative;}
nav a,.dropbtn{color:#00ffff;text-decoration:none;font-weight:500;padding:8px 14px;border-radius:8px;transition:0.3s;font-size:14px;}
nav a:hover,.dropbtn:hover{background:rgba(0,255,255,0.2);box-shadow:0 0 10px #00ffff;}
.dropdown{position:relative;}
.dropdown-content{display:none;position:absolute;right:0;top:42px;background:#1a1a1a;border-radius:12px;overflow:hidden;z-index:2000;box-shadow:0 8px 25px rgba(0,255,255,0.4);}
.dropdown-content a{color:#00ffff;padding:12px 18px;display:block;text-decoration:none;font-size:14px;transition:0.2s;}
.dropdown-content a:hover{background:rgba(0,255,255,0.2);}
.dropdown:hover .dropdown-content{display:block;}

/* Cart counter */
.cart-counter{background:#ff3c3c;color:#fff;padding:3px 7px;border-radius:50%;font-size:12px;position:absolute;top:5px;right:5px;font-weight:bold;box-shadow:0 0 6px #ff3c3c;}

/* Container */
.container{width:90%;max-width:1250px;margin:20px auto 50px;flex:1;display:flex;gap:25px;}

/* Sidebar */
.sidebar{width:220px;background:rgba(255,255,255,0.05);padding:20px;border-radius:14px;flex-shrink:0;box-shadow:0 4px 15px rgba(0,0,0,0.3);}
.sidebar h3{color:#00ffff;margin-bottom:15px;font-size:16px;border-bottom:1px solid #00ffff;padding-bottom:6px;}
.sidebar a{display:block;color:#00ffff;text-decoration:none;margin:6px 0;padding:8px 10px;border-radius:6px;transition:0.3s;}
.sidebar a:hover{background:linear-gradient(90deg,#00ffff44,#00ffff22);transform:scale(1.02);}

/* Main Section */
.product-section{flex:1;}
.breadcrumb{margin-bottom:20px;font-size:14px;color:#00ffff;}
.breadcrumb a{color:#00ffff;text-decoration:none;}
.breadcrumb a:hover{text-decoration:underline;}

/* Search bar */
.search-bar{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-bottom:30px;}
.search-bar input,.search-bar select{padding:10px 14px;border-radius:10px;border:1px solid #00ffff;background:#1a1a1a;color:#00ffff;font-size:14px;min-width:150px;transition:0.3s;}
.search-bar input:focus,.search-bar select:focus{border-color:#00ffff;box-shadow:0 0 8px #00ffff;}
.search-bar button{padding:10px 20px;background:linear-gradient(90deg,#00ffff,#00bfbf);border:none;border-radius:10px;cursor:pointer;font-weight:bold;color:#000;transition:0.3s;}
.search-bar button:hover{background:linear-gradient(90deg,#00bfbf,#00ffff);color:#fff;}
.reset-btn{background:transparent;border:1px solid #00ffff;color:#00ffff;padding:10px 18px;border-radius:8px;transition:0.3s;}
.reset-btn:hover{background:rgba(0,255,255,0.2);}

/* Product grid */
.product-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:25px;}
.product-card{background:rgba(255,255,255,0.08);border-radius:18px;padding:15px;text-align:center;transition:0.3s;position:relative;overflow:hidden;box-shadow:0 5px 20px rgba(0,0,0,0.3);}
.product-card:hover{transform:translateY(-6px) scale(1.02);box-shadow:0 15px 35px rgba(0,255,255,0.4);}
.product-card img{width:100%;height:200px;object-fit:contain;background:#111;border-radius:12px;padding:10px;transition:0.3s;}
.product-card:hover img{transform:scale(1.05);}
.product-card h3{color:#00ffff;font-size:17px;margin:10px 0 6px;font-weight:600;}
.product-brand{font-size:13px;color:#aaa;margin-bottom:6px;font-style:italic;}
.product-badges{display:flex;justify-content:center;gap:6px;margin-bottom:6px;}
.badge{padding:4px 8px;font-size:12px;font-weight:bold;border-radius:6px;color:#fff;}
.badge.new{background-color:#3498db;}
.badge.hot{background-color:#e74c3c;}
.badge.recommend{background-color:#f1c40f;color:#000;}
.price{margin-top:5px;}
.old-price{text-decoration:line-through;color:#bbb;font-size:14px;margin-right:6px;}
.new-price{color:#00ffff;font-weight:bold;}
.discount-tag{display:inline-block;background:#ff3c3c;color:#fff;font-size:12px;font-weight:bold;border-radius:6px;padding:2px 6px;margin-left:4px;}
.stock{font-size:13px;color:#aaa;margin-top:4px;}
.stock.out{color:#ff3c3c;font-weight:bold;}

/* Buttons */
.product-card .btns{display:flex;justify-content:center;gap:10px;margin-top:12px;}
.product-card .btns a{flex:1;padding:8px 0;border-radius:8px;font-weight:bold;text-decoration:none;text-align:center;color:#000;background:linear-gradient(90deg,#00ffff,#00bfbf);transition:0.3s;}
.product-card .btns a:hover{background:linear-gradient(90deg,#00bfbf,#00ffff);color:#fff;}

/* Pagination */
.pagination{margin:25px 0;text-align:center;}
.pagination a{padding:6px 12px;margin:0 4px;background:#00ffff;color:#000;text-decoration:none;border-radius:8px;font-weight:600;transition:0.3s;}
.pagination a.active{background:#00bfbf;color:#fff;}
.pagination a:hover{background:#00bfbf;color:#fff;}

/* Footer */
footer{text-align:center;padding:25px 0;background:linear-gradient(90deg,#111,#1a1a1a);color:#00ffff;font-weight:500;margin-top:auto;box-shadow:0 -3px 15px rgba(0,0,0,0.4);}

/* Scrollbar */
::-webkit-scrollbar{width:8px;}
::-webkit-scrollbar-thumb{background:rgba(0,255,255,0.3);border-radius:4px;}
::-webkit-scrollbar-track{background:rgba(0,0,0,0.1);}

/* Responsive */
@media(max-width:980px){.container{flex-direction:column;}.sidebar{width:100%;display:flex;flex-wrap:wrap;gap:10px;}}
</style>
</head>
<body>

<header>
    <div class="logo">MCAS</div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php" style="background:rgba(0,255,255,0.2);">Products</a>
        <div class="dropdown">
            <button class="dropbtn">My Account &#9662;</button>
            <div class="dropdown-content">
                <a href="cart.php">Cart <?php if($cartCount>0): ?><span class="cart-counter"><?= $cartCount ?></span><?php endif; ?></a>
                <a href="orders.php">My Orders</a>
                <a href="profile.php">Profile</a>
                <a href="maintenance.php">Maintenance</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
</header>

<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h3>Categories</h3>
        <a href="products.php?category=all">All</a>
        <?php foreach($categories as $c): ?>
        <a href="products.php?category=<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></a>
        <?php endforeach; ?>
        <h3>Brands</h3>
        <a href="products.php?brand=all">All</a>
        <?php foreach($brands as $b): ?>
        <a href="products.php?brand=<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Main Section -->
    <div class="product-section">
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a> &gt; Products
            <?php if($category!=='all'): ?> &gt; <?= htmlspecialchars($category) ?><?php endif; ?>
            <?php if($brand!=='all'): ?> &gt; <?= htmlspecialchars($brand) ?><?php endif; ?>
        </div>

        <form method="GET" class="search-bar">
            <input type="text" name="search" placeholder="🔍 Search product..." value="<?= htmlspecialchars($search) ?>">
            <select name="category">
                <option value="all" <?= $category=='all'?'selected':'' ?>>All Categories</option>
                <?php foreach($categories as $c): ?>
                <option value="<?= htmlspecialchars($c) ?>" <?= $category==$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="brand">
                <option value="all" <?= $brand=='all'?'selected':'' ?>>All Brands</option>
                <?php foreach($brands as $b): ?>
                <option value="<?= htmlspecialchars($b) ?>" <?= $brand==$b?'selected':'' ?>><?= htmlspecialchars($b) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Search</button>
            <a href="products.php" class="reset-btn">Reset</a>
        </form>

        <div class="product-grid">
        <?php if($products->num_rows>0): ?>
            <?php while($p=$products->fetch_assoc()):
                $hasDiscount = !empty($p['discount_price']) && $p['discount_price'] < $p['price'];
                $discountRate = $hasDiscount ? round((1 - $p['discount_price'] / $p['price']) * 100) : 0;
                $outStock = $p['stock'] <= 0;
            ?>
            <div class="product-card <?= $outStock ? 'outstock' : '' ?>">
                <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                <h3><?= htmlspecialchars($p['name']) ?></h3>
                <div class="product-brand">Brand: <?= htmlspecialchars($p['brand']) ?></div>
                <div class="product-badges">
                    <?php if($p['is_new']): ?><span class="badge new">🆕 NEW</span><?php endif; ?>
                    <?php if($p['is_hot']): ?><span class="badge hot">🔥 HOT</span><?php endif; ?>
                    <?php if($p['is_recommend']): ?><span class="badge recommend">⭐ RECOMMEND</span><?php endif; ?>
                </div>
                <div class="price">
                    <?php if($hasDiscount): ?>
                        <span class="old-price">RM <?= number_format($p['price'],2) ?></span>
                        <span class="new-price">RM <?= number_format($p['discount_price'],2) ?></span>
                        <span class="discount-tag">-<?= $discountRate ?>%</span>
                    <?php else: ?>
                        <span class="new-price">RM <?= number_format($p['price'],2) ?></span>
                    <?php endif; ?>
                </div>
                <div class="stock">
                    <?php if(!$outStock): ?>
                        In Stock: <?= $p['stock'] ?>
                    <?php else: ?>
                        <span class="out">Out of Stock</span>
                    <?php endif; ?>
                </div>
                <div class="btns">
                    <a href="product_detail.php?id=<?= $p['id'] ?>">View</a>
                    <a href="add_to_cart.php?id=<?= $p['id'] ?>">Add</a>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
        <p style="text-align:center;">❌ No products found.</p>
        <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if($totalPages>1): ?>
        <div class="pagination">
            <?php for($i=1;$i<=$totalPages;$i++): ?>
                <a href="?search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&brand=<?= urlencode($brand) ?>&page=<?= $i ?>" class="<?= $i==$page?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<footer>
    &copy; 2025 Machap Computer Accessories System (MCAS)
</footer>
</body>
</html>
