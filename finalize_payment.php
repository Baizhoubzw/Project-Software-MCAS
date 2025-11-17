<?php
// finalize_payment.php (optional)
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) { http_response_code(401); echo 'not logged'; exit(); }
if (!isset($_POST['order_id'])) { http_response_code(400); echo 'no id'; exit(); }

$order_id = intval($_POST['order_id']);
$user_id = $_SESSION['user_id'];
$txn = 'SIM' . time() . rand(100,999);
$ref = strtoupper(substr(md5($txn . $order_id), 0, 12));

$stmt = $conn->prepare("UPDATE orders SET statuss = 'Paid', payment_reference = ?, transaction_id = ?, payment_amount = total_price WHERE id = ? AND user_id = ?");
$stmt->bind_param("ssii", $ref, $txn, $order_id, $user_id);
$stmt->execute();
if ($conn->affected_rows > 0) echo 'ok'; else { http_response_code(500); echo 'fail'; }
