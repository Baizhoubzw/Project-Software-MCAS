<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Contact Us - MCAS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body {
  margin: 0;
  font-family: 'Roboto', sans-serif;
  background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
  color: #e0f7ff;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 40px;
  background: rgba(26,26,26,0.85);
  backdrop-filter: blur(6px);
  box-shadow: 0 4px 20px rgba(0,0,0,0.5);
}
header .logo {
  font-size: 26px;
  font-weight: 700;
  color: #00ffff;
  letter-spacing: 1px;
}
nav { display: flex; align-items: center; gap: 15px; }
nav a, .dropbtn {
  color: #00ffff;
  text-decoration: none;
  font-weight: 500;
  padding: 8px 10px;
  border-radius: 6px;
  transition: 0.3s;
  font-size: 14px;
}
nav a:hover, .dropbtn:hover { background: rgba(0,255,255,0.2); }
.dropdown { position: relative; }
.dropdown-content {
  display: none;
  position: absolute;
  right: 0;
  top: 35px;
  background: rgba(26,26,26,0.95);
  backdrop-filter: blur(6px);
  min-width: 160px;
  box-shadow: 0 8px 16px rgba(0,0,0,0.4);
  border-radius: 10px;
  overflow: hidden;
  z-index: 1000;
}
.dropdown-content a {
  color: #00ffff;
  padding: 10px 16px;
  display: block;
  text-decoration: none;
  font-size: 14px;
}
.dropdown-content a:hover { background: rgba(0,255,255,0.2); }
.dropdown:hover .dropdown-content { display: block; }

#particles-js { position: fixed; width: 100%; height: 100%; top: 0; left: 0; z-index: -1; }

.container {
  width: 85%;
  max-width: 900px;
  margin: 80px auto;
  background: rgba(255,255,255,0.05);
  border-radius: 15px;
  padding: 40px;
  box-shadow: 0 0 30px rgba(0,255,255,0.2);
  text-align: center;
}
.container h1 {
  color: #00ffff;
  font-size: 36px;
  text-shadow: 0 0 15px #00ffff;
  margin-bottom: 20px;
}
.contact-info {
  margin-top: 20px;
  font-size: 16px;
  color: #cdefff;
  line-height: 1.8;
}
.contact-info i {
  color: #00ffff;
  margin-right: 8px;
}
footer {
  text-align: center;
  padding: 20px;
  background: rgba(26,26,26,0.85);
  color: #00ffff;
  font-weight: 500;
  margin-top: auto;
}
</style>
</head>
<body>

<div id="particles-js"></div>

<header>
  <div class="logo">MCAS</div>
  <nav>
    <a href="index.php"><i class="fas fa-home"></i> Home</a>
    <a href="products.php"><i class="fas fa-box-open"></i> Products</a>
    <div class="dropdown">
      <button class="dropbtn"><i class="fas fa-user"></i> My Account &#9662;</button>
      <div class="dropdown-content">
        <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
        <a href="orders.php"><i class="fas fa-list"></i> Orders</a>
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
  <h1>Contact Us</h1>
  <p>If you have any questions or inquiries, feel free to reach out to us through the following channels:</p>

  <div class="contact-info">
    <p><i class="fas fa-envelope"></i> Email: support@mcas.com</p>
    <p><i class="fas fa-phone"></i> Phone: +60 12-345 6789</p>
    <p><i class="fas fa-map-marker-alt"></i> Address: Machap Baru, Johor, Malaysia</p>
    <p><i class="fas fa-clock"></i> Operating Hours: Mon - Fri, 9:00 AM - 6:00 PM</p>
  </div>
</div>

<footer>
  © 2025 Machap Computer Accessories System (MCAS)
</footer>

<script src="https://cdn.jsdelivr.net/npm/particles.js"></script>
<script>
particlesJS("particles-js", {
  particles: {
    number: { value: 70 },
    color: { value: "#00ffff" },
    shape: { type: "circle" },
    opacity: { value: 0.4, random: true },
    size: { value: 3, random: true },
    line_linked: { enable: true, distance: 120, color: "#00ffff", opacity: 0.2, width: 1 },
    move: { enable: true, speed: 1.5, random: true }
  },
  interactivity: { events: { onhover: { enable: true, mode: "grab" } } },
  retina_detect: true
});
</script>

</body>
</html>
