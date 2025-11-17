<?php
session_start();
include 'db.php';

// 检查是否已登录管理员
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// 检查是否传入产品 ID
if (!isset($_GET['id'])) {
    header("Location: admin_products.php");
    exit();
}

$id = intval($_GET['id']);

// 先检查产品是否存在
$result = $conn->query("SELECT * FROM products WHERE id = $id");
if ($result->num_rows == 0) {
    $_SESSION['error'] = "Product not found.";
    header("Location: admin_products.php");
    exit();
}

// 删除产品
$delete = $conn->query("DELETE FROM products WHERE id = $id");

if ($delete) {
    $_SESSION['success'] = "Product deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete product: " . $conn->error;
}

header("Location: admin_products.php");
exit();
?>
