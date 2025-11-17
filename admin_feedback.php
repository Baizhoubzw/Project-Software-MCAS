<?php
session_start();
include 'db.php';

// 🧱 仅管理员可进
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$username = $_SESSION['admin_username'] ?? 'Admin';

// 🧩 获取筛选值
$filter_date = $_GET['date'] ?? '';
$filter_product = $_GET['product'] ?? '';

// 🧠 获取所有产品列表
$products_list = $conn->query("SELECT id, name FROM products ORDER BY name ASC");

// 🔥 删除评论
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM comments WHERE id=$delete_id OR parent_id=$delete_id");
    header("Location: admin_feedback.php");
    exit();
}

// 💬 回复评论
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_text'])) {
    $parent_id = intval($_POST['parent_id']);
    $reply = trim($_POST['reply_text']);
    if ($reply !== '') {
        $stmt = $conn->prepare("INSERT INTO comments (product_id, user_id, comment, parent_id, created_at) VALUES (?, ?, ?, ?, NOW())");
        $admin_id = 0; // admin回复
        $stmt->bind_param("iisi", $_POST['product_id'], $admin_id, $reply, $parent_id);
        $stmt->execute();
    }
    header("Location: admin_feedback.php");
    exit();
}

// 🧮 查询评论
$query = "
    SELECT c.id, c.product_id, c.user_id, c.comment, c.likes, c.created_at, c.parent_id,
           p.name AS product_name, u.username AS user_name
    FROM comments c
    LEFT JOIN products p ON c.product_id = p.id
    LEFT JOIN user u ON c.user_id = u.id
    WHERE 1
";
if (!empty($filter_date)) {
    $query .= " AND DATE(c.created_at) = '$filter_date'";
}
if (!empty($filter_product)) {
    $query .= " AND c.product_id = " . intval($filter_product);
}
$query .= " ORDER BY c.created_at DESC";
$comments = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Feedback Management - MCAS Admin</title>
<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
    color: #fff;
}
header {
    background: #111;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
header .logo {
    color: #00ffff;
    font-weight: bold;
    font-size: 20px;
}
nav a {
    color: #00ffff;
    text-decoration: none;
    margin: 0 10px;
    font-weight: bold;
}
nav a:hover { color: #80ffff; }
.dropdown {
    position: relative;
    display: inline-block;
}
.dropdown-content {
    display: none;
    position: absolute;
    background-color: #111;
    min-width: 160px;
    box-shadow: 0px 8px 16px rgba(0,0,0,0.4);
    z-index: 1;
}
.dropdown-content a {
    color: #00ffff;
    padding: 10px 14px;
    text-decoration: none;
    display: block;
}
.dropdown:hover .dropdown-content { display: block; }

.container {
    width: 90%;
    margin: 30px auto;
}
h1 {
    text-align: center;
    color: #00ffff;
}
.filter {
    text-align: center;
    margin-bottom: 20px;
}
.filter select, .filter input[type="date"] {
    padding: 6px;
    border-radius: 6px;
    border: none;
    margin-left: 8px;
}
.filter button {
    background: #00ffff;
    border: none;
    color: #000;
    padding: 7px 12px;
    margin-left: 8px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
}
.filter button:hover {
    background: #80ffff;
}
.table {
    background: rgba(0,0,0,0.4);
    border-radius: 10px;
    overflow: hidden;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 10px;
    border-bottom: 1px solid rgba(0,255,255,0.1);
    text-align: left;
}
th {
    background: rgba(0,0,0,0.6);
    color: #00ffff;
}
.reply-box {
    background: rgba(255,255,255,0.05);
    padding: 10px;
    margin-top: 6px;
    border-radius: 8px;
}
.reply-box textarea {
    width: 100%;
    padding: 6px;
    border-radius: 6px;
    border: none;
}
.reply-box button {
    background: #00ffff;
    border: none;
    color: #000;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: bold;
    margin-top: 6px;
}
.reply-box button:hover { background: #80ffff; }
.footer {
    text-align: center;
    padding: 20px;
    background: #111;
    color: #00ffff;
    margin-top: 30px;
    border-radius: 10px;
}
.btn-del {
    color: #ff8a8a;
    font-weight: bold;
    text-decoration: none;
}
.btn-del:hover { color: #ffb3b3; }
</style>
</head>
<body>

<header>
  <div class="logo">MCAS Admin Panel</div>
  <nav>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_maintenance.php">Maintenance</a>
    <a href="admin_products.php">Products</a>
    <div class="dropdown">
      <a href="#">More ▾</a>
      <div class="dropdown-content">
        <a href="admin_add_product.php">Add Product</a>
        <a href="admin_feedback.php">Product Feedback</a>
        <a href="admin_logout.php" style="color:#ff8a8a;">Logout</a>
      </div>
    </div>
  </nav>
</header>

<div class="container">
  <h1>Product Feedback Management</h1>

  <div class="filter">
    <form method="get">
      <label>Date:</label>
      <input type="date" name="date" value="<?= htmlspecialchars($filter_date) ?>">
      <label>Product:</label>
      <select name="product">
        <option value="">All</option>
        <?php while ($p = $products_list->fetch_assoc()): ?>
          <option value="<?= $p['id'] ?>" <?= ($filter_product == $p['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($p['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
      <button type="submit">Filter</button>
      <a href="admin_feedback.php"><button type="button">Clear</button></a>
    </form>
  </div>

  <div class="table">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Product</th>
          <th>User</th>
          <th>Comment</th>
          <th>Likes</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($comments->num_rows == 0): ?>
        <tr><td colspan="7" style="text-align:center;color:#ccc;">No comments found.</td></tr>
      <?php else: ?>
        <?php while($c = $comments->fetch_assoc()): ?>
          <?php if ($c['parent_id'] == 0): ?>
          <tr>
            <td><?= $c['id'] ?></td>
            <td><?= htmlspecialchars($c['product_name']) ?></td>
            <td><?= htmlspecialchars($c['user_name']) ?></td>
            <td><?= htmlspecialchars($c['comment']) ?></td>
            <td><?= $c['likes'] ?></td>
            <td><?= $c['created_at'] ?></td>
            <td>
              <a href="?delete=<?= $c['id'] ?>" class="btn-del" onclick="return confirm('Delete this comment?')">Delete</a>
            </td>
          </tr>
          <tr>
            <td colspan="7">
              <div class="reply-box">
                <form method="post">
                  <input type="hidden" name="product_id" value="<?= $c['product_id'] ?>">
                  <input type="hidden" name="parent_id" value="<?= $c['id'] ?>">
                  <textarea name="reply_text" placeholder="Write a reply..." rows="2" required></textarea>
                  <button type="submit">Reply</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endif; ?>
        <?php endwhile; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="footer">
    © 2025 Machap Computer Accessories System (MCAS) — Product Feedback Management
  </div>
</div>

</body>
</html>
