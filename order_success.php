<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['order_id']);

// 获取订单
$stmt = $conn->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
if (!$order) {
    echo "Order not found.";
    exit();
}

// 获取订单项目
$stmt2 = $conn->prepare("SELECT * FROM order_items WHERE order_id=?");
$stmt2->bind_param("i", $order_id);
$stmt2->execute();
$items = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order Success - MCAS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body{margin:0;font-family:'Roboto',sans-serif;background:#0f2027;color:#fff;}
header{background:#111;padding:15px 40px;display:flex;justify-content:space-between;align-items:center;}
header .logo{font-size:26px;font-weight:700;color:#00ffff;}
header nav a{color:#00ffff;text-decoration:none;font-weight:500;margin-left:20px;transition:0.3s;}
header nav a:hover{background:rgba(0,255,255,0.2);padding:6px 10px;border-radius:8px;}
.container{width:90%;max-width:800px;margin:40px auto;background:rgba(255,255,255,0.1);padding:25px;border-radius:15px;box-shadow:0 0 20px rgba(0,255,255,0.2);}
h1{text-align:center;color:#00ffff;}
table{width:100%;border-collapse:collapse;margin-top:20px;color:#fff;}
th,td{padding:12px;border-bottom:1px solid rgba(255,255,255,0.3);}
th{color:#00ffff;}
.total{font-size:20px;text-align:right;margin-top:20px;color:#00bfbf;}
.button-download{display:inline-block;padding:10px 20px;background:#00ffff;color:#000;font-weight:bold;border:none;border-radius:8px;text-decoration:none;transition:0.3s;}
.button-download:hover{background:#00bfbf;color:#fff;}
</style>
</head>
<body>
<header>
    <div class="logo">MCAS</div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="cart.php">Cart</a>
        <a href="orders.php">Orders</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h1>✅ Payment Successful!</h1>
    <p><strong>Order ID:</strong> <?= htmlspecialchars($order['id']) ?></p>
    <p><strong>Transaction ID:</strong> <?= htmlspecialchars($order['transaction_id']) ?></p>
    <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($order['payment_status']) ?></p>

    <table>
        <tr><th>Product</th><th>Price (RM)</th><th>Qty</th><th>Subtotal (RM)</th></tr>
        <?php foreach($items as $it): ?>
        <tr>
            <td><?= htmlspecialchars($it['product_name']) ?></td>
            <td><?= number_format($it['price'],2) ?></td>
            <td><?= $it['quantity'] ?></td>
            <td><?= number_format($it['price'] * $it['quantity'],2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="total">Total Paid: RM <?= number_format($order['total_price'],2) ?></div>

    <a class="button-download" href="generate_receipt.php?order_id=<?= $order['id'] ?>" target="_blank">Download Receipt PDF</a>
</div>
</body>
</html>
