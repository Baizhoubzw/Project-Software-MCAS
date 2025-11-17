<?php
session_start();
include 'db.php';

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// 推荐产品
$result = $conn->query("SELECT * FROM products LIMIT 4");
$featured_products = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $featured_products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard - MCAS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

*{margin:0;padding:0;box-sizing:border-box;font-family:'Roboto',sans-serif;}
body{background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);color:#fff;min-height:100vh;display:flex;flex-direction:column;}

/* Header */
header {display:flex;justify-content:space-between;align-items:center;padding:15px 40px;background:rgba(26,26,26,0.85);backdrop-filter: blur(6px);box-shadow:0 4px 20px rgba(0,0,0,0.5);}
header .logo {font-size:26px;font-weight:700;color:#00ffff;letter-spacing:1px;}
nav {display:flex;align-items:center;gap:15px;}
nav a, .dropbtn {color:#00ffff;text-decoration:none;font-weight:500;padding:8px 10px;border-radius:6px;transition:0.3s;font-size:14px;}
nav a:hover, .dropbtn:hover {background:rgba(0,255,255,0.2);}
.dropdown {position:relative;}
.dropdown-content {display:none;position:absolute;right:0;top:35px;background:rgba(26,26,26,0.95);backdrop-filter:blur(6px);min-width:160px;box-shadow:0 8px 16px rgba(0,0,0,0.4);border-radius:10px;overflow:hidden;z-index:1000;}
.dropdown-content a {color:#00ffff;padding:10px 16px;display:block;text-decoration:none;font-size:14px;}
.dropdown-content a:hover {background:rgba(0,255,255,0.2);}
.dropdown:hover .dropdown-content {display:block;}

/* Announcement bar */
.announcement{
  background: rgba(0,255,255,0.1);
  color:#00ffff;
  text-align:center;
  padding:8px 0;
  border-radius:8px;
  margin:10px auto;
  font-size:14px;
  width:90%;
  max-width:1200px;
  box-shadow: 0 2px 5px rgba(0,255,255,0.2);
}

/* Particles Background */
#particles-js{position:fixed;width:100%;height:100%;top:0;left:0;z-index:-1;}

/* Container */
.container {width:90%; max-width:1200px; margin:20px auto 60px auto; flex:1;}
.welcome {text-align:center;font-size:26px;color:#00ffff;margin-bottom:20px; text-shadow:0 0 10px rgba(0,255,255,0.5);}

/* Quick Links Grid */
.quick-links {display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:20px; margin-bottom:40px; justify-items:center;}
.quick-links a {
    width:100%; 
    text-align:center; 
    padding:14px 0; 
    background: rgba(0,255,255,0.1); 
    border-radius:12px; 
    color:#00ffff; 
    font-weight:700; 
    box-shadow: 0 0 5px rgba(0,255,255,0.2), inset 0 0 5px rgba(0,255,255,0.1); 
    font-size:14px; 
    transition: 0.3s ease-in-out;
    border: 1px solid rgba(0,255,255,0.2);
}
.quick-links a i {margin-right:6px;}
.quick-links a:hover {
    transform:scale(1.05);
    box-shadow: 0 0 15px rgba(0,255,255,0.6), inset 0 0 10px rgba(0,255,255,0.3);
    background: rgba(0,255,255,0.15);
}

/* Featured Products */
.section-title {text-align:center; font-size:32px; color:#00ffff; margin-bottom:40px; text-shadow:0 0 10px rgba(0,255,255,0.5);}
.featured-products {
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));
    gap:25px;
    justify-items:center;
}
.product-card {
    display:flex;
    flex-direction:column;
    justify-content:space-between;
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(6px);
    border-radius:20px;
    width:220px;
    transition: transform 0.3s, box-shadow 0.3s;
}
.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0,255,255,0.3);
}
.product-card img {
    width:100%;
    height:180px;
    object-fit:cover;
    border-top-left-radius:20px;
    border-top-right-radius:20px;
}
.card-content {
    padding:15px;
    display:flex;
    flex-direction:column;
    flex-grow:1;
}
.card-content h3 { font-size:18px; color:#00ffff; margin:8px 0 5px; text-align:center; }
.card-content p { font-size:13px; color:#cceeff; text-align:center; height:40px; overflow:hidden; margin-bottom:10px; }
.card-content strong { display:block; font-size:16px; color:#00ffff; margin-bottom:8px; text-align:center; }
.card-content .badge { display:inline-block; background:rgba(0,255,255,0.2); color:#00ffff; padding:3px 8px; border-radius:6px; font-size:12px; margin:2px; text-align:center; }
.card-content button {
    margin-top:auto; 
    padding:10px 0; 
    background: linear-gradient(90deg,#00ffff,#00bfbf);
    color:#000; 
    border:none; 
    border-radius:8px; 
    cursor:pointer; 
    font-weight:bold; 
    transition:0.3s ease-in-out;
    box-shadow: 0 0 5px rgba(0,255,255,0.3), inset 0 0 3px rgba(0,255,255,0.2);
}
.card-content button:hover {
    transform:scale(1.05);
    box-shadow: 0 0 20px rgba(0,255,255,0.6), inset 0 0 8px rgba(0,255,255,0.3);
    background: linear-gradient(90deg,#00bfbf,#00ffff);
}

/* Footer */
footer {text-align:center; padding:25px 0; background:rgba(26,26,26,0.85); backdrop-filter:blur(6px); color:#00ffff; font-weight:500; font-size:14px;}
footer .social-icons{margin-top:10px;}
footer .social-icons a{color:#00ffff;margin:0 8px;font-size:16px;transition:0.3s;}
footer .social-icons a:hover{color:#00bfbf;}

/* Dashboard Footer Links */
.dashboard-footer-links{
  display:flex;
  justify-content:center;
  gap:20px;
  margin-top:15px;
  flex-wrap:wrap;
}
.dashboard-footer-links a{
  color:#00ffff;
  text-decoration:none;
  font-weight:500;
  padding:6px 12px;
  border-radius:6px;
  transition:0.3s;
  border: 1px solid rgba(0,255,255,0.2);
}
.dashboard-footer-links a:hover{
  background: rgba(0,255,255,0.2);
  transform:scale(1.05);
}

/* Responsive */
@media(max-width:768px){
    .quick-links{grid-template-columns:repeat(auto-fit,minmax(120px,1fr)); margin-bottom:30px;}
    .welcome{font-size:22px;}
    nav {flex-wrap:wrap; gap:8px;}
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

<!-- Announcement -->
<div class="announcement">
  🛠 Scheduled maintenance on 10th Nov 2025 from 1AM to 3AM. New products coming soon!
</div>

<div class="container">
    <div class="welcome">Welcome, <?php echo htmlspecialchars($username); ?>!</div>

    <!-- Quick Links -->
    <div class="quick-links">
        <a href="products.php"><i class="fas fa-shopping-bag"></i> Shop Now</a>
        <a href="cart.php"><i class="fas fa-shopping-cart"></i> View Cart</a>
        <a href="orders.php"><i class="fas fa-list"></i> My Orders</a>
        <a href="profile.php"><i class="fas fa-id-card"></i> My Profile</a>
        <a href="maintenance.php"><i class="fas fa-tools"></i> Maintenance</a>
    </div>

    <!-- Featured Products -->
    <div class="section-title">Featured Products</div>
    <div class="featured-products">
        <?php if(empty($featured_products)){ echo "<p style='width:100%; text-align:center;'>No products available.</p>"; } ?>
        <?php foreach($featured_products as $product){ ?>
            <div class="product-card">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <div class="card-content">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                    <strong>RM <?php echo number_format($product['price'],2); ?></strong>
                    <div class="badge"><?php echo htmlspecialchars($product['brand']); ?></div>
                    <div class="badge"><?php echo htmlspecialchars($product['category']); ?></div>
                    <button onclick="window.location='products.php'">View Product</button>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<!-- Footer -->
<footer>
    &copy; 2025 Machap Computer Accessories System (MCAS) | 123 Machap Street, Melaka | support@mcas.com
    <div class="social-icons">
        <a href="#"><i class="fab fa-facebook"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
    </div>
    <div class="dashboard-footer-links">
        <a href="contact_us.php">Contact Support</a>
        <a href="faq.php">FAQs</a>
        <a href="feedback.php">Feedback</a>
    </div>
</footer>

<!-- Particles.js -->
<script src="https://cdn.jsdelivr.net/npm/particles.js"></script>
<script>
particlesJS("particles-js", {
  "particles": {
    "number": {"value":70},
    "color":{"value":"#00ffff"},
    "shape":{"type":"circle"},
    "opacity":{"value":0.4,"random":true},
    "size":{"value":3,"random":true},
    "line_linked":{"enable":true,"distance":120,"color":"#00ffff","opacity":0.2,"width":1},
    "move":{"enable":true,"speed":1.5,"direction":"none","random":true,"straight":false}
  },
  "interactivity":{"events":{"onhover":{"enable":true,"mode":"grab"}}},
  "retina_detect": true
});
</script>

</body>
</html>
