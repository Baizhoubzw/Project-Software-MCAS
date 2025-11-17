<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 获取用户订单
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Orders - MCAS</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;600&display=swap');

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Roboto', sans-serif;
    }

    body {
      background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
      color: #e6faff;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* HEADER */
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: rgba(26, 26, 26, 0.9);
      padding: 15px 40px;
      box-shadow: 0 4px 20px rgba(0, 255, 255, 0.2);
      position: sticky;
      top: 0;
      z-index: 100;
      backdrop-filter: blur(6px);
    }

    .logo {
      font-size: 26px;
      font-weight: 700;
      color: #00ffff;
      letter-spacing: 1px;
      text-shadow: 0 0 10px #00ffff;
    }

    nav {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    nav a, .dropdown-btn {
      color: #00ffff;
      text-decoration: none;
      font-weight: 500;
      transition: 0.3s;
      border-radius: 6px;
      padding: 8px 10px;
    }

    nav a:hover, .dropdown-btn:hover {
      background: rgba(0, 255, 255, 0.2);
    }

    /* Dropdown */
    .dropdown {
      position: relative;
    }

    .dropdown-content {
      display: none;
      position: absolute;
      top: 35px;
      right: 0;
      background: rgba(26, 26, 26, 0.95);
      backdrop-filter: blur(8px);
      border-radius: 10px;
      min-width: 160px;
      box-shadow: 0 8px 16px rgba(0, 255, 255, 0.2);
      z-index: 999;
      overflow: hidden;
    }

    .dropdown-content a {
      color: #00ffff;
      padding: 10px 15px;
      text-decoration: none;
      display: block;
      transition: 0.3s;
    }

    .dropdown-content a:hover {
      background: rgba(0, 255, 255, 0.2);
    }

    .dropdown:hover .dropdown-content {
      display: block;
    }

    /* CONTAINER */
    .container {
      width: 90%;
      max-width: 1100px;
      margin: 40px auto;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 0 25px rgba(0, 255, 255, 0.2);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(0, 255, 255, 0.2);
      flex: 1;
    }

    h2 {
      text-align: center;
      color: #00ffff;
      text-shadow: 0 0 10px rgba(0, 255, 255, 0.6);
      margin-bottom: 25px;
    }

    /* TABLE */
    table {
      width: 100%;
      border-collapse: collapse;
      border-radius: 10px;
      overflow: hidden;
    }

    th, td {
      padding: 14px 10px;
      text-align: center;
      border-bottom: 1px solid rgba(0, 255, 255, 0.2);
    }

    th {
      background: rgba(0, 255, 255, 0.1);
      color: #00ffff;
      text-transform: uppercase;
      font-size: 14px;
    }

    tr:nth-child(even) {
      background: rgba(255, 255, 255, 0.03);
    }

    tr:hover td {
      background: rgba(0, 255, 255, 0.08);
      transition: 0.3s;
    }

    .status {
      font-weight: 600;
      padding: 5px 10px;
      border-radius: 6px;
      text-transform: capitalize;
    }

    .status.completed {
      background: rgba(0, 255, 128, 0.2);
      color: #00ff99;
    }

    .status.pending {
      background: rgba(255, 255, 0, 0.2);
      color: #ffcc00;
    }

    .status.cancelled {
      background: rgba(255, 0, 64, 0.2);
      color: #ff4081;
    }

    .btn {
      background: linear-gradient(90deg, #00ffff, #00bfbf);
      color: #000;
      border: none;
      padding: 7px 15px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
      text-decoration: none;
    }

    .btn:hover {
      transform: scale(1.05);
      box-shadow: 0 0 15px rgba(0, 255, 255, 0.5);
    }

    .no-order {
      text-align: center;
      padding: 40px 0;
      font-size: 16px;
      color: #b0c7cc;
    }

    footer {
      text-align: center;
      padding: 25px 0;
      color: #00ffff;
      background: rgba(26,26,26,0.85);
    }
  </style>
</head>
<body>

<header>
  <div class="logo">MCAS</div>
  <nav>
    <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="products.php"><i class="fas fa-box-open"></i> Products</a>
    <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
    <a href="orders.php" class="active"><i class="fas fa-list"></i> Orders</a>

    <div class="dropdown">
      <button class="dropdown-btn"><i class="fas fa-user"></i> More ▼</button>
      <div class="dropdown-content">
        <a href="profile.php"><i class="fas fa-id-card"></i> Profile</a>
        <a href="maintenance.php"><i class="fas fa-tools"></i> Maintenance</a>
        <a href="about_us.php">About Us</a>
        <a href="contact_us.php">Contact Us</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>
  </nav>
</header>

<div class="container">
  <h2>My Orders</h2>

  <?php if ($orders->num_rows > 0): ?>
    <table>
      <tr>
        <th>Order ID</th>
        <th>Total Price (RM)</th>
        <th>Status</th>
        <th>Order Date</th>
        <th>Action</th>
      </tr>
      <?php while ($order = $orders->fetch_assoc()): ?>
        <tr>
          <td>#<?= $order['id'] ?></td>
          <td><?= number_format($order['total_price'], 2) ?></td>
          <td>
            <?php
              $status = strtolower($order['statuss']);
              echo "<span class='status $status'>" . ucfirst($status) . "</span>";
            ?>
          </td>
          <td><?= date("Y-m-d H:i", strtotime($order['created_at'])) ?></td>
          <td>
            <a class="btn" href="order_details.php?id=<?= $order['id'] ?>">View Details</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <p class="no-order">❌ You have no orders yet.</p>
  <?php endif; ?>
</div>

<footer>
  &copy; 2025 Machap Computer Accessories System (MCAS)
</footer>

</body>
</html>
