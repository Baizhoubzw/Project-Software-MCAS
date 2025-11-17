<?php
session_start();
include 'db.php';

// Ensure user logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info (username + profile pic)
$user_query = $conn->prepare("SELECT username, profile_pic FROM user WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();

$username = $user['username'];
$profile_image = !empty($user['profile_pic']) ? $user['profile_pic'] : 'default-avatar.png';

// Fetch maintenance requests
$requests = $conn->query("SELECT * FROM maintenance_requests WHERE user_id = $user_id ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Maintenance - MCAS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

* { margin:0; padding:0; box-sizing:border-box; font-family:'Roboto',sans-serif; }
body {
  background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);
  color:#fff;
  min-height:100vh;
  display:flex;
  flex-direction:column;
  overflow-x:hidden;
}

/* Header */
header {
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:15px 40px;
  background:rgba(26,26,26,0.85);
  backdrop-filter: blur(6px);
  box-shadow:0 4px 20px rgba(0,0,0,0.5);
  position:relative;
  z-index:1000; /* 让导航永远在最上层 */
}
header .logo {
  font-size:26px;
  font-weight:700;
  color:#00ffff;
  letter-spacing:1px;
}
nav {
  display:flex;
  align-items:center;
  gap:15px;
}
nav a, .dropbtn {
  color:#00ffff;
  text-decoration:none;
  font-weight:500;
  padding:8px 10px;
  border-radius:6px;
  transition:0.3s;
  font-size:14px;
}
nav a:hover, .dropbtn:hover { background:rgba(0,255,255,0.2); }
.dropdown { position:relative; z-index:2000; }
.dropdown-content {
  display:none;
  position:absolute;
  right:0;
  top:35px;
  background:rgba(26,26,26,0.95);
  backdrop-filter:blur(6px);
  min-width:160px;
  box-shadow:0 8px 16px rgba(0,0,0,0.4);
  border-radius:10px;
  overflow:hidden;
  z-index:3000;
}
.dropdown-content a {
  color:#00ffff;
  padding:10px 16px;
  display:block;
  text-decoration:none;
  font-size:14px;
}
.dropdown-content a:hover { background:rgba(0,255,255,0.2); }
.dropdown:hover .dropdown-content { display:block; }
.profile-pic {
  width:38px;
  height:38px;
  border-radius:50%;
  border:2px solid #00ffff;
  object-fit:cover;
  box-shadow:0 0 10px #00ffff;
}

/* Container */
.container {
  width:90%;
  max-width:1200px;
  margin:40px auto;
  flex:1;
  background:rgba(255,255,255,0.05);
  border-radius:20px;
  padding:30px;
  box-shadow:0 0 25px rgba(0,255,255,0.2);
  border:1px solid rgba(0,255,255,0.2);
  position:relative;
  overflow:visible; /* ✅ 修复被挡住的问题 */
  z-index:1; /* ✅ 确保卡片在导航之下 */
}
.container h2 {
  color:#00ffff;
  text-align:center;
  margin-bottom:25px;
  text-shadow:0 0 10px rgba(0,255,255,0.6);
}

/* Form */
form {
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
  gap:15px;
  margin-bottom:30px;
  position:relative;
  z-index:2;
}
form input, form select, form textarea {
  width:100%;
  padding:10px;
  border:none;
  border-radius:8px;
  background:rgba(255,255,255,0.1);
  color:#00ffff;
  font-size:14px;
}
form input:focus, form select:focus, form textarea:focus {
  outline:none;
  box-shadow:0 0 10px rgba(0,255,255,0.5);
}
form textarea {
  grid-column:span 2;
  resize:none;
}
form button {
  grid-column:span 2;
  background:linear-gradient(90deg,#00ffff,#00bfbf);
  color:#000;
  border:none;
  padding:10px 0;
  border-radius:8px;
  font-weight:bold;
  cursor:pointer;
  transition:0.3s;
}
form button:hover {
  transform:scale(1.05);
  box-shadow:0 0 20px rgba(0,255,255,0.6);
}

/* Table */
.table-section { margin-top:40px; }
table {
  width:100%;
  border-collapse:collapse;
  border-radius:12px;
  overflow:hidden;
}
th, td {
  padding:12px 10px;
  text-align:left;
}
th {
  background:rgba(0,255,255,0.1);
  color:#00ffff;
}
tr:nth-child(even) { background:rgba(255,255,255,0.03); }
.status-pending { color:#ffcc00; font-weight:bold; }
.status-progress { color:#00bfff; font-weight:bold; }
.status-completed { color:#00ff99; font-weight:bold; }
.view-link { color:#00ffff; text-decoration:underline; }
.view-link:hover { color:#fff; text-shadow:0 0 8px #00ffff; }

.delete-btn {
  background:linear-gradient(90deg,#ff1744,#ff4081);
  color:white;
  padding:6px 10px;
  border:none;
  border-radius:6px;
  cursor:pointer;
  transition:0.3s;
}
.delete-btn:hover {
  box-shadow:0 0 15px rgba(255,0,100,0.6);
}
footer {
  text-align:center;
  padding:25px 0;
  background:rgba(26,26,26,0.85);
  color:#00ffff;
}
</style>
</head>
<body>

<header>
  <div class="logo">MCAS</div>
  <nav>
    <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="products.php"><i class="fas fa-box-open"></i> Products</a>
    <a href="orders.php"><i class="fas fa-list"></i> Orders</a>

    <div class="dropdown">
      <button class="dropbtn"><i class="fas fa-user"></i> More ▼</button>
      <div class="dropdown-content">
        <a href="profile.php"><i class="fas fa-id-card"></i> Profile</a>
        <a href="maintenance.php"><i class="fas fa-tools"></i> Maintenance</a>
        <a href="about_us.php">About Us</a>
        <a href="contact_us.php">Contact Us</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>

    <img src="<?= htmlspecialchars($profile_image) ?>" alt="User" class="profile-pic">
  </nav>
</header>

<div class="container">
  <h2>Submit a Maintenance Request</h2>
  <form action="submit_maintenance.php" method="POST" enctype="multipart/form-data">
    <input type="text" name="product_name" placeholder="Product Name" required>
    <select name="priority">
      <option value="Normal">Normal</option>
      <option value="High">High</option>
    </select>
    <textarea name="issue_description" placeholder="Describe the issue..." rows="4" required></textarea>
    <input type="file" name="attachment" accept=".jpg,.png,.pdf,.jpeg">
    <button type="submit">Submit Request</button>
  </form>

  <div class="table-section">
    <h2>Your Maintenance Requests</h2>
    <table>
      <tr>
        <th>ID</th>
        <th>Product</th>
        <th>Description</th>
        <th>Priority</th>
        <th>Status</th>
        <th>Attachment</th>
        <th>Created At</th>
        <th>Action</th>
      </tr>
      <?php if ($requests->num_rows > 0): ?>
        <?php while ($r = $requests->fetch_assoc()): ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['product_name']) ?></td>
            <td><?= htmlspecialchars($r['issue_description']) ?></td>
            <td><?= htmlspecialchars($r['priority']) ?></td>
            <td class="<?=
              $r['status']=='Pending' ? 'status-pending' :
              ($r['status']=='In Progress' ? 'status-progress' : 'status-completed')
            ?>"><?= htmlspecialchars($r['status']) ?></td>
            <td>
              <?php if ($r['attachment']): ?>
                <a href="<?= htmlspecialchars($r['attachment']) ?>" class="view-link" target="_blank">View</a>
              <?php else: ?> - <?php endif; ?>
            </td>
            <td><?= $r['created_at'] ?></td>
            <td>
              <form action="delete_request.php" method="POST" onsubmit="return confirm('Are you sure to delete this request?');">
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <button type="submit" class="delete-btn">Delete</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="8" style="text-align:center;">No maintenance requests yet.</td></tr>
      <?php endif; ?>
    </table>
  </div>
</div>

<footer>
  &copy; 2025 Machap Computer Accessories System (MCAS)
</footer>

</body>
</html>
