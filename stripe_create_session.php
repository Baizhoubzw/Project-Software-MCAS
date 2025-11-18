<?php
// stripe_create_session.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db.php';
require 'vendor/autoload.php';

use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 确认 POST 数据存在
if (!isset($_POST['total_price'], $_POST['payer_name'], $_POST['payment_method'])) {
    die("Error: Missing form data.");
}

$total_price = floatval($_POST['total_price']);
$payer_name = trim($_POST['payer_name']);
$payment_method = trim($_POST['payment_method']);

// 获取购物车项目
$stmt = $conn->prepare("SELECT c.id as cart_id, c.product_id, c.quantity, p.name, p.price, p.discount_price
                        FROM cart c
                        JOIN products p ON c.product_id = p.id
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$cart_items = [];
$subtotal = 0;

while ($r = $res->fetch_assoc()) {
    $price = (!empty($r['discount_price']) && $r['discount_price'] < $r['price']) ? $r['discount_price'] : $r['price'];
    $subtotal += $price * $r['quantity'];
    $cart_items[] = [
        'name' => $r['name'],
        'unit_price' => $price,
        'quantity' => $r['quantity']
    ];
}

if (empty($cart_items)) {
    die("Error: Cart is empty.");
}

// 计算 6% 税
$tax = $subtotal * 0.06;
$grand_total = $subtotal + $tax;

// 创建临时订单（Pending）
$stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, payment_method, statuss, created_at) VALUES (?, ?, ?, 'Pending', NOW())");
$stmt->bind_param("ids", $user_id, $grand_total, $payment_method);
if(!$stmt->execute()) {
    die("DB Error (orders insert): ".$stmt->error);
}
$order_id = $stmt->insert_id;

// 插入 order_items
foreach ($cart_items as $it) {
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isdi", $order_id, $it['name'], $it['unit_price'], $it['quantity']);
    if(!$stmt->execute()) {
        die("DB Error (order_items insert): ".$stmt->error);
    }
}

// Stripe 初始化
Stripe::setApiKey('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');

$line_items = [];
foreach ($cart_items as $it) {
    $line_items[] = [
        'price_data' => [
            'currency' => 'myr',
            'product_data' => ['name' => $it['name']],
            'unit_amount' => intval($it['unit_price'] * 100),
        ],
        'quantity' => $it['quantity'],
    ];
}

// 添加税项
$line_items[] = [
    'price_data' => [
        'currency' => 'myr',
        'product_data' => ['name' => '6% SST Tax'],
        'unit_amount' => intval($tax * 100),
    ],
    'quantity' => 1,
];

try {
    $session = StripeSession::create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => "http://localhost/project_software/stripe_success.php?session_id={CHECKOUT_SESSION_ID}",
        'cancel_url' => "http://localhost/project_software/checkout.php",
        'customer_email' => $_SESSION['user_email'] ?? null,
    ]);

    // 更新订单 Stripe session
    $stmt = $conn->prepare("UPDATE orders SET stripe_session_id=? WHERE id=?");
    $stmt->bind_param("si", $session->id, $order_id);
    if(!$stmt->execute()) {
        die("DB Error (update stripe_session_id): ".$stmt->error);
    }

    // 清空购物车
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    if(!$stmt->execute()) {
        die("DB Error (clear cart): ".$stmt->error);
    }

    // 跳转 Stripe Checkout
    header("Location: ".$session->url);
    exit();

} catch (\Exception $e) {
    die("Stripe Error: ".$e->getMessage());
}
?>
