<?php
session_start();
include 'db.php';

// 🧱 Protect admin page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$messages = [];

// ⚙️ Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);

    // 更新状态
    if ($_POST['action'] === 'update_status') {
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE maintenance_requests SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
        $messages[] = "✅ Request #$id status updated to $status.";
    }

    // 修改 issue / description / priority
    if ($_POST['action'] === 'edit') {
        $issue = $_POST['issue_description'];
        $priority = $_POST['priority'];
        $stmt = $conn->prepare("UPDATE maintenance_requests SET issue_description = ?, priority = ? WHERE id = ?");
        $stmt->bind_param('ssi', $issue, $priority, $id);
        $stmt->execute();
        $messages[] = "✏️ Request #$id details updated.";
    }

    // 删除记录
    if ($_POST['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM maintenance_requests WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $messages[] = "🗑 Request #$id deleted.";
    }
}

// 🔍 搜索 & 筛选
$filter_status = $_GET['status'] ?? 'all';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM maintenance_requests";
$where = [];
$params = [];
$types = '';

if ($filter_status !== 'all') {
    $where[] = "status = ?";
    $params[] = $filter_status;
    $types .= 's';
}
if ($search !== '') {
    $where[] = "(product_name LIKE ? OR issue_description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}
if (!empty($where)) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Maintenance Management</title>
<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    color: #fff;
    background: linear-gradient(to right,#0f2027,#203a43,#2c5364);
}
header {
    background: #111;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.5);
}
header .logo {
    color: #00ffff;
    font-size: 22px;
    font-weight: bold;
}
header nav a {
    color: #00ffff;
    margin-left: 15px;
    text-decoration: none;
}
header nav a:hover {
    color: #80ffff;
}
.container {
    width: 95%;
    margin: 25px auto;
}
h1 {
    color: #00ffff;
}
.panel {
    background: rgba(0,0,0,0.4);
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
}
.msg {
    background: #042;
    padding: 10px;
    border-radius: 6px;
    color: #9f9;
    margin-bottom: 12px;
}
.input, select, button {
    padding: 8px;
    border-radius: 6px;
    border: none;
}
.input {
    background: #1a1a1a;
    color: #00ffff;
}
.select {
    background: #00ffff;
    color: #000;
}
.btn {
    background: #00ffff;
    color: #000;
    font-weight: bold;
    border-radius: 6px;
    padding: 8px 12px;
    cursor: pointer;
}
.btn:hover {
    background: #80ffff;
}
.table {
    background: rgba(255,255,255,0.03);
    border-radius: 10px;
    padding: 10px;
    overflow-x: auto;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    padding: 10px;
    border-bottom: 1px solid rgba(0,255,255,0.06);
}
th {
    background: rgba(0,0,0,0.6);
    color: #00ffff;
}
.actions form {
    display: inline;
}
td input[type=text], td select {
    background: #1a1a1a;
    border: 1px solid #00ffff;
    color: #00ffff;
    border-radius: 5px;
    padding: 5px;
    width: 150px;
}
</style>
</head>
<body>
<header>
  <div class="logo">MCAS Admin Panel</div>
  <nav>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_logout.php" style="color:#ff8a8a">Logout</a>
  </nav>
</header>

<div class="container">
  <h1>Maintenance Requests</h1>

  <?php foreach($messages as $m) echo "<div class='msg'>".htmlspecialchars($m)."</div>"; ?>

  <!-- 🔍 搜索 & 过滤 -->
  <div class="panel">
    <form method="get" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <div style="position:relative;">
        <input class="input" type="text" name="search" placeholder="Search product or issue..." 
               value="<?= htmlspecialchars($search) ?>" style="padding-left:35px;">
        <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#00ffff;font-weight:bold;">🔍</span>
      </div>
      <select name="status" class="select">
        <option value="all" <?= $filter_status=='all'?'selected':'' ?>>All Status</option>
        <option value="Pending" <?= $filter_status=='Pending'?'selected':'' ?>>Pending</option>
        <option value="In Progress" <?= $filter_status=='In Progress'?'selected':'' ?>>In Progress</option>
        <option value="Completed" <?= $filter_status=='Completed'?'selected':'' ?>>Completed</option>
      </select>
      <button class="btn" type="submit">Filter</button>
      <a class="btn" href="admin_maintenance.php">Reset</a>
    </form>
  </div>

  <!-- 📋 Maintenance Table -->
  <div class="table">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>User</th>
          <th>Product</th>
          <th>Issue Description</th>
          <th>Priority</th>
          <th>Status</th>
          <th>Attachment</th>
          <th>Created At</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <form method="post">
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['user_id']) ?></td>
            <td><?= htmlspecialchars($row['product_name']) ?></td>
            <td><input type="text" name="issue_description" value="<?= htmlspecialchars($row['issue_description']) ?>"></td>
            <td>
              <select name="priority">
                <option <?= $row['priority']=='Low'?'selected':'' ?>>Low</option>
                <option <?= $row['priority']=='Medium'?'selected':'' ?>>Medium</option>
                <option <?= $row['priority']=='High'?'selected':'' ?>>High</option>
              </select>
            </td>
            <td>
              <select name="status">
                <option <?= $row['status']=='Pending'?'selected':'' ?>>Pending</option>
                <option <?= $row['status']=='In Progress'?'selected':'' ?>>In Progress</option>
                <option <?= $row['status']=='Completed'?'selected':'' ?>>Completed</option>
              </select>
            </td>
            <td>
              <?php if ($row['attachment']): ?>
                <a href="<?= htmlspecialchars($row['attachment']) ?>" target="_blank" style="color:#00ffff;">View</a>
              <?php else: ?>-
              <?php endif; ?>
            </td>
            <td><?= $row['created_at'] ?></td>
            <td class="actions">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button class="btn" type="submit" name="action" value="edit">Save</button>
              <button class="btn" type="submit" name="action" value="update_status">Update Status</button>
              <button class="btn" type="submit" name="action" value="delete" style="background:#e55;color:#fff;" onclick="return confirm('Delete this record?');">Delete</button>
            </td>
          </form>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
