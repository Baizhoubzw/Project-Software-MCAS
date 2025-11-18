<?php
session_start();
include 'db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

// Check order ID
if (!isset($_GET['id'])) {
  header("Location: orders.php");
  exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Get order info
$order_query = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$order_query->bind_param("ii", $order_id, $user_id);
$order_query->execute();
$order = $order_query->get_result()->fetch_assoc();

if (!$order) {
  die("Order not found or you do not have permission to view it.");
}

// Get items with image
$item_query = $conn->prepare("
  SELECT oi.*, p.image 
  FROM order_items oi
  LEFT JOIN products p ON oi.product_name = p.name
  WHERE oi.order_id = ?
");
$item_query->bind_param("i", $order_id);
$item_query->execute();
$items = $item_query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Details - #<?= $order_id ?></title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
    * {margin:0; padding:0; box-sizing:border-box;}
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #0a0f1a;
      color: #e6edf3;
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* ===== Header ===== */
    header {
      background: rgba(10, 15, 25, 0.95);
      border-bottom: 1px solid rgba(88, 166, 255, 0.3);
      padding: 15px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 1000;
      backdrop-filter: blur(10px);
    }

    .logo {
      font-size: 22px;
      font-weight: 600;
      color: #58a6ff;
      text-shadow: 0 0 8px #58a6ff;
    }

    nav {
      display: flex;
      align-items: center;
      gap: 25px;
      position: relative;
      z-index: 1001;
    }

    nav a {
      color: #c9d1d9;
      text-decoration: none;
      font-weight: 500;
      position: relative;
      transition: 0.3s;
      padding: 6px 12px;
      border-radius: 6px;
    }

    nav a:hover,
    nav a.active {
      color: #58a6ff;
      background-color: rgba(88, 166, 255, 0.1);
    }

    /* ===== Dropdown ===== */
    .dropdown {
      position: relative;
      display: inline-block;
    }

    .dropdown-btn {
      background: none;
      border: none;
      color: #c9d1d9;
      font-size: 15px;
      font-weight: 500;
      cursor: pointer;
      padding: 6px 12px;
      border-radius: 6px;
      transition: 0.3s;
    }

    .dropdown-btn:hover {
      color: #58a6ff;
      background-color: rgba(88, 166, 255, 0.1);
    }

    .dropdown-content {
      display: none;
      position: absolute;
      background-color: #161b22;
      min-width: 160px;
      top: 38px;
      left: 0;
      border: 1px solid rgba(88, 166, 255, 0.2);
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0,0,0,0.4);
      z-index: 2000;
    }

    .dropdown-content a {
      color: #c9d1d9;
      padding: 10px 15px;
      display: block;
      text-decoration: none;
      transition: 0.3s;
    }

    .dropdown-content a:hover {
      background-color: rgba(88, 166, 255, 0.15);
      color: #58a6ff;
    }

    .dropdown.open .dropdown-content {
      display: block;
    }

    /* ===== Container ===== */
    .container {
      width: 90%;
      max-width: 950px;
      margin: 40px auto;
      background-color: #0d1117;
      border: 1px solid #30363d;
      border-radius: 10px;
      padding: 30px 40px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.4);
      position: relative;
      z-index: 1;
    }

    h2 {
      color: #58a6ff;
      text-align: center;
      margin-bottom: 25px;
      font-weight: 600;
      text-shadow: 0 0 8px rgba(88, 166, 255, 0.4);
    }

    .order-info {
      background: #161b22;
      border: 1px solid #30363d;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 25px;
    }

    .order-info p {
      margin-bottom: 8px;
      font-size: 15px;
      color: #c9d1d9;
    }

    .order-info strong {
      color: #58a6ff;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: #0d1117;
      border-radius: 8px;
      overflow: hidden;
    }

    th, td {
      padding: 12px 10px;
      text-align: center;
      border-bottom: 1px solid #30363d;
    }

    th {
      background-color: #161b22;
      color: #58a6ff;
      font-weight: 600;
      font-size: 14px;
    }

    td img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 6px;
      border: 1px solid #30363d;
    }

    tr:hover td {
      background-color: #1a2230;
      transition: 0.3s;
    }

    .total {
      text-align: right;
      margin-top: 20px;
      font-size: 18px;
      font-weight: 600;
      color: #58a6ff;
    }

    .btn {
      background-color: #58a6ff;
      color: #0d1117;
      border: none;
      border-radius: 6px;
      padding: 10px 20px;
      cursor: pointer;
      font-weight: 600;
      text-decoration: none;
      transition: 0.3s;
      display: inline-block;
      margin-top: 25px;
    }

    .btn:hover {
      background-color: #1f6feb;
      box-shadow: 0 0 12px #58a6ff;
    }

  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const dropdown = document.querySelector('.dropdown');
      const button = dropdown.querySelector('.dropdown-btn');
      button.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('open');
      });
      document.addEventListener('click', () => dropdown.classList.remove('open'));
    });
  </script>
</head>
<body>

<header>
  <div class="logo">MCAS System</div>
  <nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="products.php">Products</a>
    <a href="cart.php">Cart</a>
    <a href="orders.php" class="active">Orders</a>

    <div class="dropdown">
      <button class="dropdown-btn">More ▾</button>
      <div class="dropdown-content">
        <a href="profile.php">Profile</a>
        <a href="maintenance.php">Maintenance</a>
        <a href="about_us.php">About Us</a>
        <a href="contact_us.php">Contact Us</a>
        <a href="logout.php">Logout</a>
      </div>
    </div>
  </nav>
</header>

<div class="container">
  <h2>Order Details #<?= $order_id ?></h2>

  <div class="order-info">
    <p><strong>Order ID:</strong> #<?= $order_id ?></p>
    <p><strong>Date:</strong> <?= date("Y-m-d H:i:s", strtotime($order['created_at'])) ?></p>
    <p><strong>Status:</strong> <?= ucfirst($order['statuss']) ?></p>
    <p><strong>Total Price:</strong> RM <?= number_format($order['total_price'], 2) ?></p>
  </div>

  <table>
    <tr>
      <th>Image</th>
      <th>Product Name</th>
      <th>Quantity</th>
      <th>Price (RM)</th>
      <th>Subtotal (RM)</th>
    </tr>
    <?php 
      $total = 0;
      while ($item = $items->fetch_assoc()):
        $subtotal = $item['quantity'] * $item['price'];
        $total += $subtotal;
    ?>
    <tr>
      <td>
        <?php if (!empty($item['image'])): ?>
          <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
        <?php else: ?>
          <img src="images/no-image.png" alt="No Image">
        <?php endif; ?>
      </td>
      <td><?= htmlspecialchars($item['product_name']) ?></td>
      <td><?= $item['quantity'] ?></td>
      <td><?= number_format($item['price'], 2) ?></td>
      <td><?= number_format($subtotal, 2) ?></td>
    </tr>
    <?php endwhile; ?>
  </table>

  <div class="total">Total: RM <?= number_format($total, 2) ?></div>

  <a href="orders.php" class="btn">← Back to Orders</a>
</div>

</body>
</html>
