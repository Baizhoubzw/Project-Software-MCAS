<?php
session_start();
include 'db.php';

// 检查是否已登录管理员
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin_products.php");
    exit();
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM products WHERE id = $id");
if ($result->num_rows == 0) {
    header("Location: admin_products.php");
    exit();
}
$product = $result->fetch_assoc();

$success = $error = "";

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $brand = trim($_POST['brand']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $discount_price = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $stock = intval($_POST['stock']);
    $description = trim($_POST['description']);
    $image = trim($_POST['image']);
    $is_hot = isset($_POST['is_hot']) ? 1 : 0;
    $is_recommend = isset($_POST['is_recommend']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $status = $_POST['status'];

    if ($name && $brand && $category && $price > 0) {
        $stmt = $conn->prepare("UPDATE products SET name=?, brand=?, category=?, price=?, discount_price=?, stock=?, description=?, image=?, is_hot=?, is_recommend=?, is_new=?, status=? WHERE id=?");
        $stmt->bind_param("sssddissiiisi", $name, $brand, $category, $price, $discount_price, $stock, $description, $image, $is_hot, $is_recommend, $is_new, $status, $id);
        if ($stmt->execute()) {
            $success = "✅ Product updated successfully!";
            // 刷新数据
            $result = $conn->query("SELECT * FROM products WHERE id = $id");
            $product = $result->fetch_assoc();
        } else {
            $error = "❌ Failed to update product.";
        }
    } else {
        $error = "⚠️ Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Product - Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Roboto', sans-serif;
}
body {
    background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
    color: #fff;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Header */
header {
    background: #111;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 40px;
    position: sticky;
    top: 0;
    z-index: 1000;
}
header .logo {
    font-size: 24px;
    font-weight: 700;
    color: #00ffff;
}
nav a {
    color: #00ffff;
    text-decoration: none;
    font-weight: 500;
    margin-left: 20px;
    transition: 0.3s;
}
nav a:hover {
    background: rgba(0, 255, 255, 0.2);
    padding: 6px 10px;
    border-radius: 8px;
}
nav a.active {
    background: rgba(0, 255, 255, 0.2);
    border-radius: 8px;
}

/* Container */
.container {
    width: 85%;
    max-width: 900px;
    margin: 40px auto;
    background: rgba(255,255,255,0.1);
    padding: 25px 40px;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0,255,255,0.2);
}

/* Title */
h1 {
    text-align: center;
    color: #00ffff;
    margin-bottom: 25px;
}

/* Form */
form label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
}
form input, form textarea, form select {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
    border: none;
    border-radius: 8px;
    background: #1a1a1a;
    color: #00ffff;
}
form textarea {
    resize: none;
    height: 80px;
}
form .checkbox-group {
    display: flex;
    gap: 15px;
    margin-top: 10px;
}
form .checkbox-group label {
    font-weight: normal;
}
button {
    display: block;
    margin: 25px auto 0;
    padding: 10px 25px;
    background: #00ffff;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    color: #000;
    cursor: pointer;
    transition: 0.3s;
}
button:hover {
    background: #00bfbf;
    color: #fff;
}

/* Message */
.success { color: #00ff99; text-align:center; margin-top:10px; }
.error { color: #ff6666; text-align:center; margin-top:10px; }

/* Footer */
footer {
    text-align: center;
    padding: 20px 0;
    background: #111;
    color: #00ffff;
    margin-top: auto;
}
</style>
</head>
<body>

<header>
    <div class="logo">MCAS Admin</div>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_products.php" class="active">Products</a>
        <a href="admin_maintenance.php">Maintenance</a>
        <a href="admin_logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h1><i class="fa-solid fa-pen-to-square"></i> Edit Product</h1>

    <?php if($success): ?><p class="success"><?= $success ?></p><?php endif; ?>
    <?php if($error): ?><p class="error"><?= $error ?></p><?php endif; ?>

    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

        <label>Brand:</label>
        <input type="text" name="brand" value="<?= htmlspecialchars($product['brand']) ?>" required>

        <label>Category:</label>
        <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" required>

        <label>Price (RM):</label>
        <input type="number" name="price" value="<?= htmlspecialchars($product['price']) ?>" step="0.01" required>

        <label>Discount Price (RM):</label>
        <input type="number" name="discount_price" value="<?= htmlspecialchars($product['discount_price']) ?>" step="0.01">

        <label>Stock:</label>
        <input type="number" name="stock" value="<?= htmlspecialchars($product['stock']) ?>" required>

        <label>Description:</label>
        <textarea name="description"><?= htmlspecialchars($product['description']) ?></textarea>

        <label>Image Path:</label>
        <input type="text" name="image" value="<?= htmlspecialchars($product['image']) ?>">

        <label>Tags:</label>
        <div class="checkbox-group">
            <label><input type="checkbox" name="is_hot" <?= $product['is_hot'] ? 'checked' : '' ?>> Hot</label>
            <label><input type="checkbox" name="is_recommend" <?= $product['is_recommend'] ? 'checked' : '' ?>> Recommend</label>
            <label><input type="checkbox" name="is_new" <?= $product['is_new'] ? 'checked' : '' ?>> New</label>
        </div>

        <label>Status:</label>
        <select name="status">
            <option value="active" <?= $product['status']=='active'?'selected':'' ?>>Active</option>
            <option value="inactive" <?= $product['status']=='inactive'?'selected':'' ?>>Inactive</option>
        </select>

        <button type="submit"><i class="fa-solid fa-save"></i> Save Changes</button>
    </form>
</div>

<footer>
    &copy; 2025 Machap Computer Accessories System (MCAS) - Admin Panel
</footer>

</body>
</html>
