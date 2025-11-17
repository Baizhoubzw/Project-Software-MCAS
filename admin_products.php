<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// 读取所有产品
$query = "SELECT * FROM products ORDER BY id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Manage Products</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
* {
    box-sizing: border-box;
    font-family: 'Roboto', sans-serif;
}
body {
    background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
    color: #fff;
    margin: 0;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}
header {
    background: #111;
    padding: 15px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
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
    background: rgba(0,255,255,0.2);
    padding: 6px 10px;
    border-radius: 8px;
}
.container {
    width: 90%;
    margin: 40px auto;
    background: rgba(255,255,255,0.1);
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0,255,255,0.2);
}
h1 {
    text-align: center;
    color: #00ffff;
}
.add-product-btn {
    display: inline-block;
    background: #00ffff;
    color: #000;
    padding: 10px 18px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    transition: 0.3s;
}
.add-product-btn:hover {
    background: #00bfbf;
    color: #fff;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
    background: rgba(0,0,0,0.3);
    border-radius: 10px;
    overflow: hidden;
}
th, td {
    padding: 12px 15px;
    border-bottom: 1px solid rgba(0,255,255,0.2);
    text-align: left;
    vertical-align: middle;
}
th {
    background: rgba(0,255,255,0.1);
    color: #00ffff;
}
tr:hover {
    background: rgba(0,255,255,0.1);
}
.actions a {
    padding: 6px 12px;
    margin-right: 5px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    color: #fff;
}
.edit-btn {
    background: #007bff;
}
.delete-btn {
    background: #dc3545;
}
.edit-btn:hover {
    background: #0056b3;
}
.delete-btn:hover {
    background: #c82333;
}

/* 图片与标签 */
.image-wrapper {
    position: relative;
    display: inline-block;
    text-align: center;
}
.image-wrapper img {
    border-radius: 8px;
    display: block;
}
.badge-container {
    margin-top: 6px;
    display: flex;
    justify-content: center;
    gap: 5px;
}
.corner-badge {
    padding: 3px 6px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: bold;
    color: #fff;
    display: inline-block;
}
.badge-hot {
    background: #ff2e2e;
}
.badge-new {
    background: #1e90ff;
}
.badge-recommend {
    background: #ffbf00;
    color: #000;
}

footer {
    text-align: center;
    padding: 15px;
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
    <h1><i class="fa-solid fa-box"></i> Manage Products</h1>
    <p>Welcome back, <strong><?= htmlspecialchars($admin_name) ?></strong> 👋</p>
    <a class="add-product-btn" href="admin_add_product.php"><i class="fa-solid fa-plus"></i> Add New Product</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Brand</th>
            <th>Category</th>
            <th>Price (RM)</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td>
                        <div class="image-wrapper">
                            <?php if (!empty($row['image'])): ?>
                                <img src="<?= htmlspecialchars($row['image']) ?>" width="70" height="70">
                            <?php else: ?>
                                <span style="color:#aaa;">No Image</span>
                            <?php endif; ?>

                            <div class="badge-container">
                                <?php if ($row['is_hot']): ?>
                                    <span class="corner-badge badge-hot">🔥 HOT</span>
                                <?php endif; ?>
                                <?php if ($row['is_new']): ?>
                                    <span class="corner-badge badge-new">🆕 NEW</span>
                                <?php endif; ?>
                                <?php if ($row['is_recommend']): ?>
                                    <span class="corner-badge badge-recommend">⭐ RECOMMENDED</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['brand']) ?></td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td>RM <?= number_format($row['price'], 2) ?></td>
                    <td><?= $row['stock'] ?></td>
                    <td class="actions">
                        <a href="admin_edit_product.php?id=<?= $row['id'] ?>" class="edit-btn">Edit</a>
                        <a href="admin_delete_product.php?id=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Confirm delete this product?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8" style="text-align:center;color:#aaa;">No products found</td></tr>
        <?php endif; ?>
    </table>
</div>

<footer>
    &copy; 2025 Machap Computer Accessories System (MCAS) - Admin Panel
</footer>
</body>
</html>
