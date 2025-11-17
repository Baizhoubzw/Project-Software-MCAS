<?php
session_start();
include 'db.php';

// ✅ 确保用户已登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ 检查是否有 task id
if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // ✅ 只允许删除属于该用户且状态为 Pending 的任务
    $stmt = $conn->prepare("DELETE FROM maintenance WHERE id = ? AND user_id = ? AND status = 'Pending'");
    $stmt->bind_param("ii", $id, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Task deleted successfully.'); window.location='maintenance.php';</script>";
    } else {
        echo "<script>alert('Failed to delete task.'); window.location='maintenance.php';</script>";
    }
} else {
    header("Location: maintenance.php");
}
?>
