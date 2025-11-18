<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 获取表单数据
$product_name = isset($_POST['product_name']) ? $conn->real_escape_string($_POST['product_name']) : '';
$priority = isset($_POST['priority']) ? $conn->real_escape_string($_POST['priority']) : 'Normal';
$issue_description = isset($_POST['issue_description']) ? $conn->real_escape_string($_POST['issue_description']) : '';

// 处理附件
$attachment_path = '';
if (!empty($_FILES['attachment']['name'])) {
    $filename = time().'_'.basename($_FILES['attachment']['name']);
    $target_dir = 'uploads/';
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $target_file = $target_dir . $filename;
    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
        $attachment_path = $target_file;
    }
}

// 插入数据库
$sql = "INSERT INTO maintenance_requests (user_id, product_name, priority, issue_description, attachment, status, created_at)
        VALUES ($user_id, '$product_name', '$priority', '$issue_description', '$attachment_path', 'Pending', NOW())";

if ($conn->query($sql)) {
    header("Location: maintenance.php");
    exit();
} else {
    echo "Error: " . $conn->error;
}
?>
