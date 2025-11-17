<?php
// checkout.php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// fetch cart + product info
$stmt = $conn->prepare("SELECT c.id as cart_id, c.product_id, c.quantity, p.name, p.price, p.discount_price, p.image
                        FROM cart c
                        JOIN products p ON c.product_id = p.id
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

$cart_items = [];
$total = 0.0;
while ($r = $res->fetch_assoc()) {
    $price = (!empty($r['discount_price']) && $r['discount_price'] < $r['price']) ? $r['discount_price'] : $r['price'];
    $r['unit_price'] = $price;
    $r['subtotal'] = $price * $r['quantity'];
    $cart_items[] = $r;
    $total += $r['subtotal'];
}
$tax = $total * 0.06;
$final = $total + $tax;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Checkout - MCAS</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
/* MCAS style header + clean checkout */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
*{box-sizing:border-box}
body{font-family:'Poppins',sans-serif;background:#f4f6f8;margin:0;color:#111}
header{background:#111;color:#00ffff;display:flex;justify-content:space-between;align-items:center;padding:15px 32px;position:sticky;top:0;z-index:1000}
.logo{font-weight:700;font-size:22px}
.container{max-width:1100px;margin:36px auto;padding:24px;display:flex;gap:24px}
.left{flex:2;background:#fff;padding:20px;border-radius:12px;box-shadow:0 8px 30px rgba(15,23,42,0.06)}
.right{flex:1;background:#fff;padding:20px;border-radius:12px;box-shadow:0 8px 30px rgba(15,23,42,0.06);height:max-content}
.cart-item{display:flex;gap:14px;padding:12px 0;border-bottom:1px solid #f0f0f3;align-items:center}
.cart-item img{width:84px;height:84px;object-fit:cover;border-radius:10px;background:#f0f0f0}
h2{margin:0 0 12px 0}
.small{color:#666;font-size:13px}
.select, select, input[type="text"]{width:100%;padding:10px;border-radius:8px;border:1px solid #e6e9ee}
.btn-primary{background:#00bfbf;border:0;color:#fff;padding:12px 18px;border-radius:10px;cursor:pointer;font-weight:600}
.summary-row{display:flex;justify-content:space-between;padding:8px 0}
.total{font-weight:700;color:#ff4500;font-size:18px}
.footer-note{font-size:13px;color:#5b5b5b;margin-top:10px}
@media(max-width:900px){.container{flex-direction:column;padding:16px}}
</style>
</head>
<body>
<header>
  <div class="logo">MCAS</div>
  <nav>
    <a href="dashboard.php" style="color:#00ffff;margin-right:14px">Dashboard</a>
    <a href="products.php" style="color:#00ffff;margin-right:14px">Products</a>
    <a href="cart.php" style="color:#00ffff;margin-right:14px">Cart</a>
    <a href="orders.php" style="color:#00ffff;margin-right:14px">Orders</a>
    <a href="logout.php" style="color:#00ffff">Logout</a>
  </nav>
</header>

<div class="container">
  <div class="left">
    <h2>Review your order</h2>
    <?php if(empty($cart_items)): ?>
      <p class="small">Your cart is empty. <a href="products.php">Continue shopping</a></p>
    <?php else: ?>
      <?php foreach($cart_items as $it): ?>
        <div class="cart-item">
          <img src="<?= htmlspecialchars($it['image']) ?>" alt="">
          <div style="flex:1">
            <div style="font-weight:600"><?= htmlspecialchars($it['name']) ?></div>
            <div class="small">Qty: <?= intval($it['quantity']) ?></div>
          </div>
          <div style="text-align:right">
            <div style="font-weight:700">RM <?= number_format($it['subtotal'],2) ?></div>
            <div class="small">RM <?= number_format($it['unit_price'],2) ?> / unit</div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="right">
    <h2>Payment</h2>
    <form id="checkoutForm" action="process_payment.php" method="POST">
      <input type="hidden" name="total_price" value="<?= number_format($final,2,'.','') ?>">

      <label class="small">Payment method</label>
      <select name="payment_method" class="select" required>
        <option value="">-- Select Payment Method --</option>
        <option>Stripe</option> <!-- 新增 Stripe -->
      </select>

      <label class="small" style="margin-top:10px">Payer name</label>
      <input type="text" name="payer_name" class="select" placeholder="Full name to appear on receipt" required>

      <div style="margin-top:16px;padding:12px;background:#fbfbfb;border-radius:8px">
        <div class="summary-row"><div>Subtotal</div><div>RM <?= number_format($total,2) ?></div></div>
        <div class="summary-row"><div>Tax (6% SST)</div><div>RM <?= number_format($tax,2) ?></div></div>
        <hr>
        <div class="summary-row total"><div>Total</div><div>RM <?= number_format($final,2) ?></div></div>
      </div>

      <div style="margin-top:16px">
        <button type="submit" class="btn-primary w-100">Proceed to Pay</button>
      </div>

      <div class="footer-note">You will be redirected to a simulated bank page or Stripe to complete the payment.</div>
    </form>
  </div>
</div>

<script>
// ✅ 支持 Stripe + 现有 fake payment
document.addEventListener('DOMContentLoaded', function(){
    const form = document.getElementById('checkoutForm');
    const pmSelect = form.querySelector('select[name="payment_method"]');
    form.addEventListener('submit', function(e){
        const pm = pmSelect.value.toLowerCase();
        if(pm.includes('stripe')) {
            form.action = 'stripe_create_session.php';
            form.method = 'POST';
        } else {
            form.action = 'process_payment.php'; // 保留原有 fake
            form.method = 'POST';
        }
    });
});
</script>

</body>
</html>
