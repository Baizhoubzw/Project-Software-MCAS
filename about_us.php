<?php
session_start();
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
<title>About Us - MCAS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body {
    margin: 0;
    font-family: 'Roboto', sans-serif;
    background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
    color: #fff;
}

/* ===== NAVBAR ===== */
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 40px;
    background: rgba(26,26,26,0.85);
    backdrop-filter: blur(6px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.5);
}
.logo {
    font-size: 26px;
    font-weight: 700;
    color: #00ffff;
}
nav {
    display: flex;
    align-items: center;
    gap: 15px;
}
nav a, .dropbtn {
    color: #00ffff;
    text-decoration: none;
    font-weight: 500;
    padding: 8px 10px;
    border-radius: 6px;
    transition: 0.3s;
    font-size: 14px;
}
nav a:hover, .dropbtn:hover {
    background: rgba(0,255,255,0.2);
}
.dropdown {
    position: relative;
}
.dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    top: 35px;
    background: rgba(26,26,26,0.95);
    backdrop-filter: blur(6px);
    min-width: 160px;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 8px 16px rgba(0,0,0,0.4);
    z-index: 1000;
}
.dropdown-content a {
    color: #00ffff;
    padding: 10px 16px;
    display: block;
    text-decoration: none;
}
.dropdown-content a:hover {
    background: rgba(0,255,255,0.2);
}
.dropdown:hover .dropdown-content {
    display: block;
}

/* ===== CONTENT ===== */
.container {
    width: 90%;
    max-width: 1000px;
    margin: 80px auto;
    text-align: center;
}
.container h1 {
    color: #00ffff;
    text-shadow: 0 0 10px #00ffff;
    margin-bottom: 20px;
    font-size: 36px;
}
.container p {
    color: #cceeff;
    font-size: 16px;
    line-height: 1.7;
    margin-bottom: 20px;
}

/* FOOTER */
footer {
    text-align: center;
    padding: 25px 0;
    background: rgba(26,26,26,0.85);
    backdrop-filter: blur(6px);
    color: #00ffff;
}
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
    </nav>
</header>

<div class="container">
    <h1>About Machap Computer Accessories System</h1>
    <p>
        Welcome to <strong>Machap Computer Accessories System (MCAS)</strong> — your trusted online platform for computer accessories and maintenance services.
    </p>
    <p>
        We specialize in providing high-quality computer parts, peripherals, and accessories for both personal and professional use. 
        Our mission is to deliver the best products at affordable prices while offering reliable <strong>maintenance support</strong> to ensure your devices run smoothly.
    </p>
    <p>
        MCAS is built with the latest web technologies, ensuring a seamless and secure user experience. 
        Our maintenance service allows customers to easily book repair appointments, track service progress, and receive updates online.
    </p>
</div>

<footer>
    © 2025 Machap Computer Accessories System (MCAS)
</footer>

</body>
</html>
