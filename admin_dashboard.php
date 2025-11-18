<?php
session_start();
include 'db.php';

// Protect admin access
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$username = $_SESSION['admin_username'] ?? 'Admin';

// Dashboard stats
$stats = [
    'requests_total' => 0,
    'requests_pending' => 0,
    'requests_inprogress' => 0,
    'requests_completed' => 0,
    'users_total' => 0,
    'products_total' => 0
];

// Maintenance stats
$result = $conn->query("
    SELECT 
        COUNT(*) AS total,
        SUM(status='Pending') AS pending,
        SUM(status='In Progress') AS inprogress,
        SUM(status='Completed') AS completed
    FROM maintenance_requests
");
if ($r = $result->fetch_assoc()) {
    $stats['requests_total'] = $r['total'];
    $stats['requests_pending'] = $r['pending'];
    $stats['requests_inprogress'] = $r['inprogress'];
    $stats['requests_completed'] = $r['completed'];
}

// Users
if ($conn->query("SHOW TABLES LIKE 'users'")->num_rows > 0) {
    $res = $conn->query("SELECT COUNT(*) AS c FROM users");
    if ($r = $res->fetch_assoc()) $stats['users_total'] = $r['c'];
}

// Products
if ($conn->query("SHOW TABLES LIKE 'products'")->num_rows > 0) {
    $res = $conn->query("SELECT COUNT(*) AS c FROM products");
    if ($r = $res->fetch_assoc()) $stats['products_total'] = $r['c'];
}

// Recent requests
$recent_requests = [];
$res = $conn->query("SELECT id, user_id, product_name, issue_description, priority, status, created_at 
                     FROM maintenance_requests ORDER BY created_at DESC LIMIT 6");
while ($row = $res->fetch_assoc()) $recent_requests[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - MCAS</title>
<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: linear-gradient(to right,#0f2027,#203a43,#2c5364);
    color: #fff;
}
header {
    background: #111;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.5);
    position: sticky;
    top: 0;
    z-index: 1000;
}
header .logo {
    color: #00ffff;
    font-weight: bold;
    font-size: 20px;
}
nav {
    display: flex;
    align-items: center;
    gap: 15px;
}
nav a {
    color: #00ffff;
    text-decoration: none;
    font-weight: bold;
    padding: 6px 10px;
    border-radius: 6px;
    transition: 0.3s;
}
nav a:hover {
    background: rgba(0,255,255,0.2);
}
.dropdown {
    position: relative;
}
.dropbtn {
    background: none;
    border: none;
    color: #00ffff;
    font-weight: bold;
    cursor: pointer;
    padding: 6px 10px;
    border-radius: 6px;
    transition: 0.3s;
}
.dropbtn:hover {
    background: rgba(0,255,255,0.2);
}
.dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    top: 35px;
    background: #1a1a1a;
    border-radius: 8px;
    overflow: hidden;
    min-width: 160px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.6);
    z-index: 2000;
}
.dropdown-content a {
    display: block;
    padding: 10px;
    text-decoration: none;
    color: #00ffff;
    font-size: 14px;
}
.dropdown-content a:hover {
    background: rgba(0,255,255,0.2);
}
.dropdown:hover .dropdown-content {
    display: block;
}

/* Dashboard content */
.container {
    width: 90%;
    margin: 30px auto;
}
h1 {
    text-align: center;
    color: #00ffff;
    margin-bottom: 10px;
}
.subtitle {
    text-align: center;
    color: #ccc;
    margin-bottom: 25px;
}
.stats {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
}
.card {
    flex: 1;
    min-width: 200px;
    background: rgba(0,0,0,0.4);
    padding: 18px;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.25);
    text-align: center;
}
.card h3 { color: #00ffff; margin-bottom: 8px; }
.card .num { font-size: 28px; font-weight: bold; }
.card .small { font-size: 13px; color: #ccc; }

.section-title {
    color: #00ffff;
    margin: 30px 0 12px;
    font-size: 20px;
}
.table {
    background: rgba(255,255,255,0.03);
    border-radius: 10px;
    overflow: hidden;
}
.table table {
    width: 100%;
    border-collapse: collapse;
}
.table th, .table td {
    padding: 10px;
    border-bottom: 1px solid rgba(0,255,255,0.06);
    text-align: left;
}
.table th {
    background: rgba(0,0,0,0.6);
    color: #00ffff;
}
.table td a.btn {
    display: inline-block;
    padding: 5px 10px;
    background: #00ffff;
    color: #000;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
}
.table td a.btn:hover { background: #80ffff; }

.footer {
    text-align: center;
    color: #00ffff;
    padding: 15px;
    margin-top: 35px;
    background: #111;
    border-radius: 8px;
}
</style>
</head>
<body>

<header>
  <div class="logo">MCAS Admin Panel</div>
  <nav>
    <a href="index.php">Home</a>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_products.php">Products</a>
    <a href="admin_maintenance.php">Maintenance</a>

    <div class="dropdown">
        <button class="dropbtn">More ▼</button>
        <div class="dropdown-content">
            <a href="admin_add_product.php">Add Product</a>         
            <a href="admin_feedback.php">Product Feedback</a>
            <a href="admin_user_feedback.php">Feedback</a>
        </div>
    </div>

    <a href="admin_logout.php" style="color:#ff8a8a;">Logout</a>
  </nav>
</header>

<div class="container">
  <h1>Welcome, <?= htmlspecialchars($username) ?> 👋</h1>
  <div class="subtitle">System Overview and Recent Activities</div>

  <div class="stats">
    <div class="card">
      <h3>Total Requests</h3>
      <div class="num"><?= $stats['requests_total'] ?></div>
      <div class="small">Pending: <?= $stats['requests_pending'] ?> | In Progress: <?= $stats['requests_inprogress'] ?> | Completed: <?= $stats['requests_completed'] ?></div>
    </div>
    <div class="card">
      <h3>Registered Users</h3>
      <div class="num"><?= $stats['users_total'] ?></div>
      <div class="small">All active accounts</div>
    </div>
    <div class="card">
      <h3>Products</h3>
      <div class="num"><?= $stats['products_total'] ?></div>
      <div class="small">In system database</div>
    </div>
  </div>

  <div class="section-title">Recent Maintenance Requests</div>
  <div class="table">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>User ID</th>
          <th>Product</th>
          <th>Issue</th>
          <th>Priority</th>
          <th>Status</th>
          <th>Created At</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($recent_requests) === 0): ?>
          <tr><td colspan="8" style="text-align:center;color:#ccc;">No recent maintenance requests.</td></tr>
        <?php else: ?>
          <?php foreach ($recent_requests as $r): ?>
            <tr>
              <td><?= $r['id'] ?></td>
              <td><?= htmlspecialchars($r['user_id']) ?></td>
              <td><?= htmlspecialchars($r['product_name']) ?></td>
              <td><?= htmlspecialchars(strlen($r['issue_description']) > 25 ? substr($r['issue_description'],0,25).'...' : $r['issue_description']) ?></td>
              <td><?= htmlspecialchars($r['priority']) ?></td>
              <td><?= htmlspecialchars($r['status']) ?></td>
              <td><?= htmlspecialchars($r['created_at']) ?></td>
              <td><a class="btn" href="admin_maintenance.php?view=<?= $r['id'] ?>">Manage</a></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="footer">
    © 2025 Machap Computer Accessories System (MCAS) — Admin Dashboard
  </div>
</div>

</body>
</html>
