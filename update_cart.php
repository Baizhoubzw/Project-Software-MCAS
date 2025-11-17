<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Not logged in');
}

$user_id = $_SESSION['user_id'];
$cart_id = intval($_POST['cart_id']);
$new_qty = intval($_POST['quantity']);

if ($new_qty < 1) $new_qty = 1;

// ✅ 根据 cart_id 更新
$stmt = $conn->prepare("UPDATE cart SET quantity=? WHERE id=? AND user_id=?");
$stmt->bind_param("iii", $new_qty, $cart_id, $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "success";
} else {
    echo "nochange";
}
?>
