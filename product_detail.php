<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if (!isset($_GET['id'])) { header("Location: products.php"); exit(); }

$user_id = $_SESSION['user_id'];
$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) { echo "<p style='text-align:center;color:white;'>❌ Product not found.</p>"; exit(); }

// 评论提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    if ($comment !== "") {
        $insert = $conn->prepare("INSERT INTO comments (product_id, user_id, comment, created_at, likes) VALUES (?,?,?,?,0)");
        $insert->bind_param("iiss", $id, $user_id, $comment, date("Y-m-d H:i:s"));
        $insert->execute();
    }
    header("Location: product_detail.php?id=$id");
    exit();
}

// 点赞
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'like') {
    $comment_id = intval($_POST['comment_id']);
    $stmt = $conn->prepare("UPDATE comments SET likes = likes + 1 WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    header("Location: product_detail.php?id=$id");
    exit();
}

// 删除评论
if (isset($_GET['delete_comment'])) {
    $comment_id = intval($_GET['delete_comment']);
    $check = $conn->prepare("SELECT * FROM comments WHERE id=? AND user_id=?");
    $check->bind_param("ii", $comment_id, $user_id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $conn->query("DELETE FROM comments WHERE id=$comment_id");
    }
    header("Location: product_detail.php?id=$id");
    exit();
}

// 获取评论
$comments = $conn->prepare("SELECT c.*, u.username FROM comments c JOIN user u ON c.user_id=u.id WHERE c.product_id=? ORDER BY c.created_at DESC");
$comments->bind_param("i", $id);
$comments->execute();
$comment_result = $comments->get_result();

// 推荐产品
$recommend_stmt = $conn->prepare("SELECT * FROM products ORDER BY id DESC LIMIT 20");
$recommend_stmt->execute();
$recommend_result = $recommend_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($product['name']) ?> - MCAS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
*{margin:0;padding:0;box-sizing:border-box;font-family:'Roboto',sans-serif;}
body{background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);color:#fff;min-height:100vh;display:flex;flex-direction:column;}
header{display:flex;justify-content:space-between;align-items:center;padding:15px 40px;background:#111;position:sticky;top:0;z-index:1000;box-shadow:0 4px 15px rgba(0,0,0,0.6);}
header .logo{font-size:26px;font-weight:700;color:#00ffff;}
nav{display:flex;align-items:center;gap:15px;}
nav a,.dropbtn{color:#00ffff;text-decoration:none;font-weight:500;padding:8px 10px;border-radius:6px;transition:0.3s;font-size:14px;}
nav a:hover,.dropbtn:hover{background:rgba(0,255,255,0.2);}
.dropdown{position:relative;}
.dropdown-content{display:none;position:absolute;right:0;top:35px;background:#1a1a1a;border-radius:10px;overflow:hidden;z-index:2000;}
.dropdown-content a{color:#00ffff;padding:10px 16px;display:block;text-decoration:none;font-size:14px;}
.dropdown-content a:hover{background:rgba(0,255,255,0.2);}
.dropdown:hover .dropdown-content{display:block;}
.container{width:90%;max-width:1000px;margin:40px auto;background:rgba(255,255,255,0.08);border-radius:20px;padding:30px;display:flex;flex-wrap:wrap;gap:30px;align-items:flex-start;position:relative;box-shadow:0 8px 25px rgba(0,255,255,0.4);}
.image-box{flex:1 1 350px;background:#111;border-radius:15px;overflow:hidden;display:flex;justify-content:center;align-items:center;aspect-ratio:1/1;transition:0.3s;}
.image-box img{width:100%;height:100%;object-fit:contain;border-radius:10px;background:#000;transition:0.5s;}
.image-box:hover img{transform:scale(1.05);filter:drop-shadow(0 0 15px #00ffff);}
.details{flex:1 1 400px;}
.details h2{font-size:26px;color:#00ffff;margin-bottom:5px;}
.product-badges{margin-bottom:10px;display:flex;gap:8px;}
.badge{display:inline-block;padding:4px 8px;font-size:12px;font-weight:bold;border-radius:6px;color:#fff;}
.badge.new{background:#3498db;}
.badge.hot{background:#e74c3c;}
.badge.recommend{background:#f1c40f;color:#000;}
.price{margin:10px 0;}
.old-price{text-decoration:line-through;color:#bbb;font-size:15px;margin-right:6px;}
.new-price{color:#00ffff;font-size:20px;font-weight:bold;}
.discount-tag{background:#ff3c3c;color:#fff;font-size:12px;border-radius:6px;padding:2px 6px;margin-left:4px;}
.stock{font-size:15px;margin-top:5px;}
.stock.out{color:#ff3c3c;font-weight:bold;}
.description{margin-top:20px;line-height:1.6;color:#ddd;}
.buttons{display:flex;gap:15px;margin-top:20px;}
.buttons a,.buttons button{flex:1;text-align:center;padding:12px 0;border-radius:8px;font-weight:bold;text-decoration:none;transition:0.3s;cursor:pointer;}
.buttons .add{background:linear-gradient(90deg,#00ffff,#00bfbf);color:#000;box-shadow:0 4px 15px rgba(0,255,255,0.4);}
.buttons .add:hover{background:linear-gradient(90deg,#00bfbf,#00ffff);color:#fff;}
.buttons .back{background:transparent;border:1px solid #00ffff;color:#00ffff;}
.buttons .back:hover{background:rgba(0,255,255,0.2);}
.buttons .share-btn{background:#f1c40f;color:#000;}
.buttons .share-btn:hover{background:#e0b700;color:#000;}
.comment-section{width:90%;max-width:1000px;margin:40px auto;background:rgba(255,255,255,0.08);border-radius:20px;padding:25px;box-shadow:0 8px 25px rgba(0,255,255,0.4);}
.comment-section h3{font-size:22px;color:#00ffff;margin-bottom:15px;}
.comment-box{display:flex;flex-direction:column;gap:10px;}
.comment-box textarea{width:100%;padding:10px;border-radius:8px;border:none;resize:none;height:100px;font-size:14px;background:#111;color:#fff;}
.comment-box button{width:150px;padding:10px;border:none;background:linear-gradient(90deg,#00ffff,#00bfbf);color:#000;border-radius:8px;font-weight:bold;cursor:pointer;transition:0.3s;}
.comment-box button:hover{background:linear-gradient(90deg,#00bfbf,#00ffff);color:#fff;}
.comment-list{margin-top:20px;display:flex;flex-direction:column;gap:15px;}
.comment{background:rgba(0,0,0,0.4);border-radius:10px;padding:15px;transition:0.3s;}
.comment:hover{background:rgba(0,255,255,0.1);}
.comment .meta{font-size:13px;color:#aaa;margin-bottom:6px;}
.comment .text{font-size:15px;color:#fff;margin-bottom:8px;}
.comment .delete{float:right;color:#ff6666;text-decoration:none;font-size:13px;}
.comment .delete:hover{text-decoration:underline;}
.comment .like-btn{margin-right:10px;padding:4px 8px;border:none;border-radius:6px;font-size:13px;font-weight:bold;cursor:pointer;transition:0.3s;background:#00ffff;color:#000;}
.comment .like-btn:hover{background:#00bfbf;color:#fff;}
.recommend-section{width:90%;max-width:1000px;margin:60px auto;position:relative;}
.recommend-section h3{font-size:22px;margin-bottom:15px;color:#00ffff;text-align:left;}
.recommend-container{display:flex;overflow-x:auto;gap:20px;scroll-behavior:smooth;padding:10px;scrollbar-width:none;}
.recommend-container::-webkit-scrollbar{display:none;}
.recommend-card{flex:0 0 200px;background:rgba(255,255,255,0.08);border-radius:12px;text-align:center;padding:10px;transition:transform 0.3s;}
.recommend-card:hover{transform:translateY(-5px);box-shadow:0 6px 20px rgba(0,255,255,0.4);}
.recommend-card img{width:100%;height:140px;object-fit:contain;border-radius:8px;background:#000;transition:0.3s;}
.recommend-card img:hover{transform:scale(1.05);filter:drop-shadow(0 0 10px #00ffff);}
.recommend-card h4{font-size:15px;margin-top:8px;color:#fff;}
.recommend-card p{font-size:14px;color:#00ffff;}
.recommend-card a{display:inline-block;background:#00ffff;color:#000;padding:6px 10px;border-radius:6px;margin-top:6px;text-decoration:none;font-weight:bold;}
.recommend-card a:hover{background:#00bfbf;}
.arrow{position:absolute;top:50%;transform:translateY(-50%);font-size:22px;background:rgba(0,0,0,0.6);border:none;color:#00ffff;cursor:pointer;padding:10px;border-radius:50%;z-index:10;transition:0.3s;}
.arrow:hover{background:rgba(0,255,255,0.2);}
.arrow.left{left:-25px;}
.arrow.right{right:-25px;}
footer{text-align:center;padding:25px 0;background:#111;color:#00ffff;font-weight:500;margin-top:auto;}
@media(max-width:980px){.container{flex-direction:column;}.image-box,.details{flex:1 1 100%;}}
</style>
</head>
<body>

<header>
<div class="logo">MCAS</div>
<nav>
<a href="dashboard.php">Dashboard</a>
<a href="products.php">Products</a>
<div class="dropdown">
<button class="dropbtn">My Account &#9662;</button>
<div class="dropdown-content">
<a href="cart.php">Cart</a>
<a href="orders.php">My Orders</a>
<a href="profile.php">Profile</a>
<a href="maintenance.php">Maintenance</a>
<a href="logout.php">Logout</a>
</div>
</div>
</nav>
</header>

<!-- 产品主要信息 -->
<div class="container">
<div class="image-box">
<img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
</div>
<div class="details">
<h2><?= htmlspecialchars($product['name']) ?></h2>
<div class="product-badges">
<?php if($product['is_new']): ?><span class="badge new">🆕 NEW</span><?php endif; ?>
<?php if($product['is_hot']): ?><span class="badge hot">🔥 HOT</span><?php endif; ?>
<?php if($product['is_recommend']): ?><span class="badge recommend">⭐ RECOMMEND</span><?php endif; ?>
</div>
<div class="price">
<?php if(!empty($product['discount_price']) && $product['discount_price'] < $product['price']):
$discountRate = round((1 - $product['discount_price'] / $product['price']) * 100); ?>
<span class="old-price">RM <?= number_format($product['price'],2) ?></span>
<span class="new-price">RM <?= number_format($product['discount_price'],2) ?></span>
<span class="discount-tag">-<?= $discountRate ?>%</span>
<?php else: ?>
<span class="new-price">RM <?= number_format($product['price'],2) ?></span>
<?php endif; ?>
</div>
<div class="stock">
<?php if($product['stock']>0): ?>In Stock: <?= $product['stock'] ?><?php else: ?><span class="out">Out of Stock</span><?php endif; ?>
</div>
<div class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></div>
<div class="buttons">
<a href="add_to_cart.php?id=<?= $product['id'] ?>" class="add">Add to Cart</a>
<a href="products.php" class="back">Back to Products</a>
<button type="button" onclick="shareProduct()" class="share-btn">🔗 Share</button>
</div>
</div>
</div>

<!-- 评论区 -->
<div class="comment-section">
<h3>Customer Reviews</h3>
<form method="post" class="comment-box">
<textarea name="comment" placeholder="Write your comment..." required></textarea>
<button type="submit"><i class="fa-solid fa-paper-plane"></i> Submit</button>
</form>
<div class="comment-list">
<?php if($comment_result->num_rows>0):
while($c=$comment_result->fetch_assoc()): ?>
<div class="comment">
<div class="meta">
<strong><?= htmlspecialchars($c['username']) ?></strong> • <?= date("d M Y, h:i A", strtotime($c['created_at'])) ?>
<?php if($c['user_id']==$user_id): ?><a href="?id=<?= $id ?>&delete_comment=<?= $c['id'] ?>" class="delete">Delete</a><?php endif; ?>
</div>
<div class="text"><?= nl2br(htmlspecialchars($c['comment'])) ?></div>
<form method="post" style="display:inline;">
<input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
<button type="submit" name="action" value="like" class="like-btn">👍 <?= $c['likes'] ?></button>
</form>
</div>
<?php endwhile; else: ?>
<p style="color:#aaa;">No comments yet. Be the first to review!</p>
<?php endif; ?>
</div>
</div>

<!-- 推荐产品 -->
<?php if($recommend_result->num_rows>0): ?>
<div class="recommend-section">
<h3>Explore More Products</h3>
<button class="arrow left" onclick="scrollRecommend(-1)"><i class="fa-solid fa-chevron-left"></i></button>
<div class="recommend-container" id="recommendList">
<?php while($r=$recommend_result->fetch_assoc()): ?>
<div class="recommend-card">
<img src="<?= htmlspecialchars($r['image']) ?>" alt="<?= htmlspecialchars($r['name']) ?>">
<h4><?= htmlspecialchars($r['name']) ?></h4>
<p>RM <?= number_format($r['discount_price']?:$r['price'],2) ?></p>
<a href="product_detail.php?id=<?= $r['id'] ?>">View</a>
</div>
<?php endwhile; ?>
</div>
<button class="arrow right" onclick="scrollRecommend(1)"><i class="fa-solid fa-chevron-right"></i></button>
</div>
<?php endif; ?>

<footer>&copy; 2025 Machap Computer Accessories System (MCAS)</footer>

<script>
const container=document.getElementById('recommendList');
function scrollRecommend(direction){container.scrollBy({left:direction*220,behavior:'smooth'});}
setInterval(()=>{if(container)container.scrollBy({left:220,behavior:'smooth'});},5000);
function shareProduct(){const url=window.location.href;const title="Check out this product: <?= addslashes($product['name']) ?>";if(navigator.share){navigator.share({title:title,url:url});}else{prompt("Copy this link to share:",url);}}
</script>
</body>
</html>
