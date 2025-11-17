<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 删除单个商品
if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    $conn->query("DELETE FROM cart WHERE id=$cart_id AND user_id=$user_id");
}

// 删除全部商品
if (isset($_GET['remove_all'])) {
    $conn->query("DELETE FROM cart WHERE user_id=$user_id");
}

// 获取购物车商品（含商品信息）
$result = $conn->query("
    SELECT c.id AS cart_id, c.product_id, c.quantity, p.name, p.price, p.discount_price, p.description, p.image
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = $user_id
");
$cart_items = [];
$subtotal = 0;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['price_to_use'] = ($row['discount_price'] > 0 && $row['discount_price'] < $row['price']) ? $row['discount_price'] : $row['price'];
        $row['subtotal'] = $row['price_to_use'] * $row['quantity'];
        $cart_items[] = $row;
        $subtotal += $row['subtotal'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cart - MCAS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

*{margin:0;padding:0;box-sizing:border-box;font-family:'Roboto',sans-serif;}
body{background:#f4f6f8;color:#111;min-height:100vh;display:flex;flex-direction:column;}
a{text-decoration:none;}

/* Header */
header{
    background:#111;
    color:#00ffff;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 40px;
    position:sticky;
    top:0;
    z-index:1000;
    box-shadow:0 4px 6px rgba(0,0,0,0.3);
}
header .logo{font-size:26px;font-weight:700;}
header nav{display:flex;gap:20px;}
header nav a{color:#00ffff;font-weight:500;padding:8px 12px;border-radius:6px;transition:0.3s;}
header nav a:hover{background:rgba(0,255,255,0.2);}

/* Container */
.container{width:90%;max-width:1200px;margin:30px auto;display:flex;flex-wrap:wrap;gap:20px;}

/* Cart Items */
.cart-items{flex:2;display:flex;flex-direction:column;gap:15px;}
.cart-card{background:#fff;border-radius:12px;display:flex;align-items:center;padding:15px;gap:15px;box-shadow:0 4px 10px rgba(0,0,0,0.1);transition:transform 0.3s;}
.cart-card:hover{transform:translateY(-4px);}
.cart-card img{width:120px;height:120px;object-fit:contain;border-radius:10px;background:#f0f0f0;}
.cart-info{flex:1;display:flex;flex-direction:column;gap:6px;}
.cart-info h3{color:#111;font-size:18px;}
.cart-info .price{color:#ff4500;font-weight:700;font-size:16px;}
.cart-info .desc{color:#555;font-size:14px;}
.qty-control{display:flex;align-items:center;gap:6px;margin-top:8px;}
.qty-control button{padding:4px 10px;background:#00ffff;border:none;border-radius:4px;cursor:pointer;transition:0.2s;}
.qty-control button:hover{background:#00bfbf;color:#fff;}
.qty-control input{width:50px;text-align:center;border-radius:4px;border:1px solid #ccc;padding:4px;}
.remove-btn{padding:6px 10px;background:#ff4d4d;border:none;color:#fff;border-radius:6px;cursor:pointer;transition:0.3s;}
.remove-btn:hover{background:#e60000;}

/* Summary Card */
.summary{flex:1;background:#fff;border-radius:12px;padding:20px;box-shadow:0 4px 10px rgba(0,0,0,0.1);height:max-content;position:sticky;top:100px;}
.summary h2{font-size:22px;color:#111;margin-bottom:15px;}
.summary select{width:100%;padding:10px;margin-bottom:10px;border-radius:6px;border:1px solid #ccc;}
.details{margin-top:10px;}
.details summary{cursor:pointer;font-weight:500;margin-bottom:5px;}
.details p{margin-left:10px;margin:3px 0;color:#555;}
.summary .total{font-weight:700;color:#ff4500;font-size:18px;margin-top:15px;}
.summary button{width:100%;padding:12px 0;background:#00ffff;color:#111;font-weight:600;border:none;border-radius:8px;cursor:pointer;transition:0.3s;margin-top:15px;}
.summary button:hover{background:#00bfbf;color:#fff;}

/* Empty cart */
.empty{text-align:center;color:#888;font-size:18px;margin-top:50px;}

/* Responsive */
@media(max-width:900px){
    .container{flex-direction:column;}
    .summary{position:relative;top:0;}
    .cart-card{flex-direction:column;align-items:flex-start;}
    .cart-card img{width:100%;height:auto;}
}
</style>
</head>
<body>

<header>
<div class="logo">MCAS</div>
<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="cart.php" style="background:rgba(0,255,255,0.2)">Cart</a>
    <a href="orders.php">Orders</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
</nav>
</header>

<div class="container">
    <div class="cart-items">
        <?php if(empty($cart_items)): ?>
            <div class="empty">🛒 Your cart is empty.</div>
        <?php else: ?>
            <?php foreach($cart_items as $item): ?>
            <div class="cart-card">
                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                <div class="cart-info">
                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                    <?php if($item['discount_price']>0 && $item['discount_price']<$item['price']): ?>
                        <div class="price">RM <?= number_format($item['discount_price'],2) ?> <span style="text-decoration:line-through;color:#999;">RM <?= number_format($item['price'],2) ?></span></div>
                    <?php else: ?>
                        <div class="price">RM <?= number_format($item['price'],2) ?></div>
                    <?php endif; ?>
                    <div class="desc"><?= htmlspecialchars($item['description']) ?></div>
                    <div class="qty-control">
                        <button type="button" class="minus">-</button>
                        <input type="number" class="qty" value="<?= $item['quantity'] ?>" min="1" data-price="<?= $item['price_to_use'] ?>">
                        <button type="button" class="plus">+</button>
                    </div>
                </div>
                <button class="remove-btn" onclick="confirmRemove(<?= $item['cart_id'] ?>)"><i class="fa-solid fa-trash"></i></button>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if(!empty($cart_items)): ?>
    <div class="summary">
        <h2>Order Summary</h2>
        <label for="shipping">Shipping Method:</label>
        <select id="shipping" onchange="updateTotal()">
            <option value="0" selected>Self Pickup - Pick up at MCAS main counter, 123 Jalan Example, 9am–6pm</option>
            <option value="10">Delivery (RM10) - Delivered to your address within 3–5 business days</option>
        </select>

        <details class="details">
            <summary>View Detailed Calculation</summary>
            <?php foreach($cart_items as $item): ?>
                <p><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?> = RM <?= number_format($item['subtotal'],2) ?></p>
            <?php endforeach; ?>
            <p>Shipping: RM <span id="shipping-fee">0.00</span></p>
            <p>Tax (6% SST): RM <span id="tax-amount">0.00</span></p>
        </details>

        <div class="total">Total: RM <span id="total"><?= number_format($subtotal,2) ?></span></div>
        <button onclick="window.location='checkout.php'">Proceed to Checkout</button>
        <button style="margin-top:10px;background:#ff4d4d;color:#fff;" onclick="confirmRemoveAll()">Remove All Items</button>
    </div>
    <?php endif; ?>
</div>

<script>
function updateCartQuantity(cartId, newQty){
    fetch('update_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `cart_id=${cartId}&quantity=${newQty}`
    })
    .then(res => res.text())
    .then(data => {
        if (data.trim() === "success" || data.trim() === "nochange") {
            updateTotal(); // 更新前端总价显示
        } else {
            alert("❌ Failed to update quantity.");
        }
    })
    .catch(err => console.error(err));
}

function updateTotal(){
    let subtotal=0;
    document.querySelectorAll('.cart-card').forEach(card=>{
        const qty = parseInt(card.querySelector('.qty').value);
        const price = parseFloat(card.querySelector('.qty').dataset.price);
        subtotal += qty * price;
    });
    const shippingFee = parseFloat(document.getElementById('shipping').value);
    const tax = subtotal * 0.06;
    const total = subtotal + shippingFee + tax;

    document.getElementById('tax-amount').textContent = tax.toFixed(2);
    document.getElementById('shipping-fee').textContent = shippingFee.toFixed(2);
    document.getElementById('total').textContent = total.toFixed(2);
}

// 每个商品的数量控制
document.querySelectorAll('.cart-card').forEach(card=>{
    const input = card.querySelector('.qty');
    const cartId = card.querySelector('.remove-btn').getAttribute('onclick').match(/\d+/)[0];

    card.querySelector('.plus').addEventListener('click', ()=>{
        input.value = parseInt(input.value) + 1;
        updateCartQuantity(cartId, input.value);
    });

    card.querySelector('.minus').addEventListener('click', ()=>{
        if (input.value > 1) {
            input.value = parseInt(input.value) - 1;
            updateCartQuantity(cartId, input.value);
        }
    });

    input.addEventListener('change', ()=>{
        if (input.value < 1) input.value = 1;
        updateCartQuantity(cartId, input.value);
    });
});

// 删除按钮
function confirmRemove(id){ if(confirm("Remove this item?")) window.location='cart.php?remove='+id; }
function confirmRemoveAll(){ if(confirm("Remove ALL items?")) window.location='cart.php?remove_all=1'; }

updateTotal();
</script>


</body>
</html>
