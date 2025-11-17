<?php
session_start();
include 'db.php';

// 检查管理员登录
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// 初始化变量
$name = $description = $image = $category = $brand = "";
$price = $discount_price = $stock = 0;
$is_hot = $is_new = $is_recommend = 0;
$status = "active";
$errors = [];
$success = "";

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $image = trim($_POST['image']);
    $category = trim($_POST['category']);
    $brand = trim($_POST['brand']);
    $price = floatval($_POST['price']);
    $discount_price = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $stock = intval($_POST['stock']);
    $is_hot = isset($_POST['is_hot']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_recommend = isset($_POST['is_recommend']) ? 1 : 0;
    $status = $_POST['status'];

    // 验证
    if (empty($name)) $errors[] = "Product name is required.";
    if (empty($price) || $price <= 0) $errors[] = "Price must be greater than 0.";
    if ($discount_price !== null && $discount_price >= $price) $errors[] = "Discount price must be less than original price.";
    if ($stock < 0) $errors[] = "Stock cannot be negative.";

    // 没有错误则插入数据库
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO products 
        (name, description, image, category, brand, price, discount_price, stock, is_hot, is_new, is_recommend, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssdiiiiiis", $name, $description, $image, $category, $brand, $price, $discount_price, $stock, $is_hot, $is_new, $is_recommend, $status);
        if ($stmt->execute()) {
            $success = "✅ Product added successfully!";
            // 清空表单
            $name = $description = $image = $category = $brand = "";
            $price = $discount_price = $stock = 0;
            $is_hot = $is_new = $is_recommend = 0;
            $status = "active";
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Product - Admin MCAS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

*{margin:0;padding:0;box-sizing:border-box;font-family:'Roboto',sans-serif;}
body{background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);color:#fff;min-height:100vh;display:flex;flex-direction:column;}

/* Header */
header{display:flex;justify-content:space-between;align-items:center;padding:15px 40px;background:#111;position:sticky;top:0;z-index:1000;}
header .logo{font-size:26px;font-weight:700;color:#00ffff;}
nav a,.dropbtn{color:#00ffff;text-decoration:none;font-weight:500;padding:8px 10px;border-radius:6px;transition:0.3s;font-size:14px;}
nav a:hover,.dropbtn:hover{background:rgba(0,255,255,0.2);}
nav{display:flex;align-items:center;gap:15px;}
nav .dropdown{position:relative;}
nav .dropdown-content{display:none;position:absolute;right:0;top:35px;background:#1a1a1a;border-radius:10px;overflow:hidden;z-index:2000;}
nav .dropdown-content a{color:#00ffff;padding:10px 16px;display:block;text-decoration:none;font-size:14px;}
nav .dropdown-content a:hover{background:rgba(0,255,255,0.2);}
nav .dropdown:hover .dropdown-content{display:block;}

/* Container */
.container{width:90%;max-width:900px;margin:40px auto;background:rgba(255,255,255,0.08);border-radius:20px;padding:30px;position:relative;}

/* Page title and breadcrumb */
h2{color:#00ffff;margin-bottom:10px;}
.breadcrumb{margin-bottom:20px;font-size:14px;color:#00ffff;}
.breadcrumb a{color:#00ffff;text-decoration:none;}
.breadcrumb a:hover{text-decoration:underline;}

/* Form */
form label{display:block;margin:12px 0 4px;font-weight:500;}
form input[type=text], form input[type=number], form textarea, form select{width:100%;padding:10px;border-radius:8px;border:1px solid #00ffff;background:#1a1a1a;color:#00ffff;font-size:14px;}
form textarea{resize:vertical;height:80px;}
form .checkbox-group{display:flex;gap:20px;margin-top:8px;}
form .checkbox-group label{display:flex;align-items:center;gap:6px;font-size:14px;color:#00ffff;}
form button{margin-top:20px;padding:12px 18px;background:#00ffff;color:#000;font-weight:bold;border:none;border-radius:8px;cursor:pointer;transition:0.3s;}
form button:hover{background:#00bfbf;color:#fff;}

/* Messages */
.errors{background:#ff3c3c;padding:10px;border-radius:8px;margin-bottom:15px;color:#fff;}
.success{background:#00bfff;padding:10px;border-radius:8px;margin-bottom:15px;color:#000;}

/* Footer */
footer{text-align:center;padding:25px 0;background:#111;color:#00ffff;font-weight:500;margin-top:auto;}
</style>
</head>
<body>

<header>
    <div class="logo">MCAS Admin</div>
    <nav>
        <a href="admin_dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="admin_products.php" style="background:rgba(0,255,255,0.2);"><i class="fa-solid fa-box"></i> Products</a>
        <a href="admin_maintenance.php"><i class="fa-solid fa-tools"></i> Maintenance</a>
        <div class="dropdown">
            <button class="dropbtn"><i class="fa-solid fa-user"></i> Account &#9662;</button>
            <div class="dropdown-content">
                <a href="admin_logout.php">Logout</a>
            </div>
        </div>
    </nav>
</header>

<div class="container">
    <div class="breadcrumb">
        <a href="admin_dashboard.php">Dashboard</a> &gt; 
        <a href="admin_products.php">Products</a> &gt; 
        Add Product
    </div>

    <h2>Add New Product</h2>

    <?php if(!empty($errors)): ?>
    <div class="errors">
        <ul>
        <?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if($success): ?>
    <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Name*</label>
        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>

        <label>Description</label>
        <textarea name="description"><?= htmlspecialchars($description) ?></textarea>

        <label>Image URL*</label>
        <input type="text" name="image" value="<?= htmlspecialchars($image) ?>" required>

        <label>Category</label>
        <input type="text" name="category" value="<?= htmlspecialchars($category) ?>">

        <label>Brand</label>
        <input type="text" name="brand" value="<?= htmlspecialchars($brand) ?>">

        <label>Price (RM)*</label>
        <input type="number" name="price" step="0.01" value="<?= htmlspecialchars($price) ?>" required>

        <label>Discount Price (RM)</label>
        <input type="number" name="discount_price" step="0.01" value="<?= htmlspecialchars($discount_price) ?>">

        <label>Stock</label>
        <input type="number" name="stock" value="<?= htmlspecialchars($stock) ?>">

        <div class="checkbox-group">
            <label><input type="checkbox" name="is_hot" <?= $is_hot?'checked':'' ?>> Hot</label>
            <label><input type="checkbox" name="is_new" <?= $is_new?'checked':'' ?>> New</label>
            <label><input type="checkbox" name="is_recommend" <?= $is_recommend?'checked':'' ?>> Recommended</label>
        </div>

        <label>Status</label>
        <select name="status">
            <option value="active" <?= $status=='active'?'selected':'' ?>>Active</option>
            <option value="inactive" <?= $status=='inactive'?'selected':'' ?>>Inactive</option>
        </select>

                <button type="submit"><i class="fa-solid fa-plus"></i> Add Product</button>
    </form>
</div>

<footer>
    &copy; 2025 Machap Computer Accessories System (MCAS) - Admin Panel
</footer>

</body>
</html>

