<?php
session_start();
include 'db.php';

// ✅ 确保用户已登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ 确认是否收到 ID
if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // ✅ 检查此记录是否属于该用户
    $check = $conn->query("SELECT * FROM maintenance_requests WHERE id = $id AND user_id = $user_id");
    if ($check->num_rows > 0) {
        // ✅ 删除记录
        $conn->query("DELETE FROM maintenance_requests WHERE id = $id");
        echo "<script>alert('Request deleted successfully!'); window.location.href='maintenance.php';</script>";
    } else {
        echo "<script>alert('Invalid request or no permission to delete.'); window.location.href='maintenance.php';</script>";
    }
} else {
    header("Location: maintenance.php");
    exit();
}
?>
