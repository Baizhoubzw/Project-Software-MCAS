<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['order_id'] ?? 0);

$order_res = $conn->query("SELECT * FROM orders WHERE id=$order_id AND user_id=$user_id");
if ($order_res->num_rows == 0) die("Order not found.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
        $filename = "receipt_order_{$order_id}_".time().".$ext";
        $dest = "uploads/$filename";
        if (!is_dir("uploads")) mkdir("uploads", 0755, true);
        move_uploaded_file($_FILES['receipt']['tmp_name'], $dest);
        $conn->query("UPDATE orders SET status='Pending Verification', receipt_path='$dest' WHERE id=$order_id");
        header("Location: payment_success.php?order_id=$order_id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Upload Receipt</title></head>
<body>
<h2>Upload Receipt for Order #<?=$order_id?></h2>
<form method="POST" enctype="multipart/form-data">
  <input type="file" name="receipt" required>
  <button type="submit">Upload</button>
</form>
</body>
</html>
