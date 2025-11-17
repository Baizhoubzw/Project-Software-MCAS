<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = intval($_GET['id']);

    // 获取产品详情
    $stmt = $conn->prepare("SELECT name, price, discount_price FROM products WHERE id=?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        header("Location: products.php");
        exit();
    }

    $product_name = $product['name'];
    $price = !empty($product['discount_price']) ? $product['discount_price'] : $product['price'];

    // 检查购物车是否已有此商品
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id=? AND product_id=?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        // 已有商品，数量+1
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
    } else {
        // 插入新商品
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("iisd", $user_id, $product_id, $product_name, $price);
        $stmt->execute();
    }
}

// 跳转到购物车
header("Location: cart.php");
exit();
?>
