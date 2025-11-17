<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$success_msg = '';
$error_msg = '';

// 获取用户信息
$stmt = $conn->prepare("SELECT * FROM user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// ===== 更新资料 =====
if (isset($_POST['update_profile'])) {
    $new_username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $contact = $conn->real_escape_string($_POST['contact_number']);

    $update = $conn->prepare("UPDATE user SET username=?, email=?, contact_number=? WHERE id=?");
    $update->bind_param("sssi", $new_username, $email, $contact, $user_id);
    if ($update->execute()) {
        $_SESSION['username'] = $new_username;
        $success_msg = "Profile updated successfully!";
    } else {
        $error_msg = "Error updating profile!";
    }
}

// ===== 修改密码 =====
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $check = $conn->prepare("SELECT password FROM user WHERE id=?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $stored = $check->get_result()->fetch_assoc();

    if (!password_verify($current, $stored['password'])) {
        $error_msg = "Current password incorrect!";
    } elseif ($new !== $confirm) {
        $error_msg = "New password and confirm password do not match!";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $conn->query("UPDATE user SET password='$hashed' WHERE id=$user_id");
        $success_msg = "Password changed successfully!";
    }
}

// ===== 上传头像 =====
if (isset($_POST['upload_pic']) && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    $dir = "uploads/profile_pics/";
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $file_name = "user_" . $user_id . "_" . basename($file["name"]);
    $target = $dir . $file_name;
    $type = strtolower(pathinfo($target, PATHINFO_EXTENSION));

    if (in_array($type, ['jpg', 'jpeg', 'png'])) {
        if (move_uploaded_file($file["tmp_name"], $target)) {
            $conn->query("UPDATE user SET profile_pic='$target' WHERE id=$user_id");
            $success_msg = "Profile picture updated!";
            header("Location: profile.php");
            exit();
        } else {
            $error_msg = "Failed to upload image.";
        }
    } else {
        $error_msg = "Only JPG or PNG allowed.";
    }
}

$user = $conn->query("SELECT * FROM user WHERE id=$user_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile - MCAS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

*{margin:0;padding:0;box-sizing:border-box;font-family:'Roboto',sans-serif;}
body{background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);color:#fff;min-height:100vh;}

/* Header */
header {display:flex;justify-content:space-between;align-items:center;padding:15px 40px;background:rgba(26,26,26,0.85);backdrop-filter:blur(6px);box-shadow:0 4px 20px rgba(0,0,0,0.5);}
header .logo {font-size:26px;font-weight:700;color:#00ffff;}
nav {display:flex;align-items:center;gap:15px;}
nav a, .dropbtn {color:#00ffff;text-decoration:none;font-weight:500;padding:8px 10px;border-radius:6px;transition:0.3s;font-size:14px;}
nav a:hover, .dropbtn:hover {background:rgba(0,255,255,0.2);}
.dropdown {position:relative;}
.dropdown-content {display:none;position:absolute;right:0;top:35px;background:rgba(26,26,26,0.95);backdrop-filter:blur(6px);min-width:160px;box-shadow:0 8px 16px rgba(0,0,0,0.4);border-radius:10px;overflow:hidden;z-index:1000;}
.dropdown-content a {color:#00ffff;padding:10px 16px;display:block;text-decoration:none;font-size:14px;}
.dropdown-content a:hover {background:rgba(0,255,255,0.2);}
.dropdown:hover .dropdown-content {display:block;}
.profile-avatar {width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #00ffff;cursor:pointer;}

/* Container */
.container {width:90%;max-width:700px;margin:50px auto;background:rgba(255,255,255,0.05);padding:30px;border-radius:16px;box-shadow:0 0 25px rgba(0,255,255,0.1);}
h2{text-align:center;color:#00ffff;margin-bottom:25px;text-shadow:0 0 8px rgba(0,255,255,0.4);}
form label{display:block;margin-top:15px;font-weight:600;color:#00ffff;}
form input{width:100%;padding:10px;margin-top:5px;border-radius:8px;border:none;background:rgba(255,255,255,0.1);color:#fff;}
form button{margin-top:20px;background:linear-gradient(90deg,#00ffff,#00bfbf);color:#000;border:none;padding:10px 18px;border-radius:8px;cursor:pointer;font-weight:bold;transition:0.3s;}
form button:hover{transform:scale(1.05);box-shadow:0 0 20px rgba(0,255,255,0.6);}
.message{text-align:center;margin-bottom:15px;font-weight:bold;}
.success{color:#00ff66;}
.error{color:#ff4444;}
footer{text-align:center;padding:25px 0;background:rgba(26,26,26,0.85);backdrop-filter:blur(6px);color:#00ffff;margin-top:40px;}
</style>
</head>
<body>

<header>
    <div class="logo">MCAS</div>
    <nav>
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <a href="products.php"><i class="fas fa-box-open"></i> Products</a>
        <div class="dropdown">
            <button class="dropbtn"><i class="fas fa-user"></i> My Account &#9662;</button>
            <div class="dropdown-content">
                <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                <a href="orders.php"><i class="fas fa-list"></i> My Orders</a>
                <a href="profile.php"><i class="fas fa-id-card"></i> Profile</a>
                <a href="maintenance.php"><i class="fas fa-tools"></i> Maintenance</a>
                <a href="about_us.php">About us</a>
                <a href="contact_us.php">Contact us</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" class="profile-avatar">
    </nav>
</header>

<div class="container">
    <h2>My Profile</h2>
    <?php if ($success_msg) echo "<div class='message success'>$success_msg</div>"; ?>
    <?php if ($error_msg) echo "<div class='message error'>$error_msg</div>"; ?>

    <div style="text-align:center;margin-bottom:20px;">
        <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" class="profile-avatar" style="width:100px;height:100px;border:3px solid #00ffff;">
    </div>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label>Contact Number</label>
        <input type="text" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>" required>

        <button type="submit" name="update_profile">Update Profile</button>
    </form>

    <form method="POST">
        <label>Current Password</label>
        <input type="password" name="current_password" required>

        <label>New Password</label>
        <input type="password" name="new_password" required>

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit" name="change_password">Change Password</button>
    </form>

    <form method="POST" enctype="multipart/form-data">
        <label>Change Profile Picture</label>
        <input type="file" name="profile_pic" accept="image/*" required>
        <button type="submit" name="upload_pic">Upload</button>
    </form>
</div>

<footer>
    &copy; 2025 Machap Computer Accessories System (MCAS)
</footer>

</body>
</html>
