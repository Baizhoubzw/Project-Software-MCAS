<?php
include 'db.php';

// Featured products
$result = $conn->query("SELECT * FROM products WHERE id BETWEEN 25 AND 28 ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MCAS - Home</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
*{margin:0;padding:0;box-sizing:border-box;font-family:'Roboto',sans-serif;}
body{background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);color:#fff;overflow-x:hidden;position:relative;}
#particles-js{position:fixed;width:100%;height:100%;top:0;left:0;z-index:-1;}

/* ===== HEADER ===== */
header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:20px 50px;
    background:rgba(20,20,30,0.85);
    backdrop-filter:blur(6px);
    position:sticky;
    top:0;
    z-index:1000;
    box-shadow:0 4px 25px rgba(0,255,255,0.15);
}
header .logo {
    font-size:30px;
    font-weight:700;
    color:#00ffff;
    letter-spacing:1.5px;
    text-shadow:0 0 12px #00ffff, 0 0 30px #0088aa;
}
header nav {
    display:flex;
    gap:25px;
}
header nav a {
    position:relative;
    color:#aeefff;
    font-weight:500;
    text-transform:uppercase;
    letter-spacing:0.5px;
    padding:6px 0;
    transition:color 0.4s, transform 0.3s;
}
header nav a::after {
    content:'';
    position:absolute;
    left:0;
    bottom:-4px;
    width:0%;
    height:2px;
    background:linear-gradient(90deg,#00ffff,#00bfbf);
    border-radius:2px;
    box-shadow:0 0 8px #00ffff;
    transition:width 0.4s;
}
header nav a:hover {
    color:#00ffff;
    transform:translateY(-2px);
}
header nav a:hover::after {
    width:100%;
}

/* ===== HERO SECTION ===== */
.hero {position:relative;height:550px;display:flex;align-items:center;justify-content:center;overflow:hidden;border-radius:20px;margin-top:20px;}
.hero-slide {position:absolute;width:100%;height:100%;opacity:0;transition:opacity 1.2s ease;background-size:cover;background-position:center;border-radius:20px;}
.hero-slide.active {opacity:1;}
.hero-overlay {position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.4);border-radius:20px;}
.hero-content {position:relative;text-align:center;z-index:2;color:#00ffff;animation:fadeInUp 1.5s ease forwards;}
.hero-content h1 {font-size:56px;margin-bottom:15px;background:linear-gradient(90deg,#00ffff,#00bfbf,#00ffff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;animation:gradientMove 3s infinite linear;text-shadow:0 0 20px rgba(0,255,255,0.6);}
.hero-content p {font-size:20px;margin-bottom:25px;text-shadow:0 0 8px rgba(0,255,255,0.4);}
.hero-content a.button {display:inline-block;padding:16px 45px;background:linear-gradient(45deg,#00ffff,#00bfbf);color:#000;font-weight:700;border-radius:12px;box-shadow:0 8px 25px rgba(0,255,255,0.5);position:relative;overflow:hidden;transition:0.4s;}
.hero-content a.button::before {content:"";position:absolute;top:0;left:-75%;width:50%;height:100%;background:rgba(255,255,255,0.3);transform:skewX(-25deg);transition:0.7s;}
.hero-content a.button:hover::before {left:125%;}
.hero-content a.button:hover {transform:scale(1.08);box-shadow:0 12px 30px rgba(0,255,255,0.7);}

/* ===== PRODUCTS ===== */
.container {width:90%;max-width:1300px;margin:70px auto;}
h2.section-title {text-align:center;font-size:36px;color:#00ffff;margin-bottom:50px;text-shadow:0 0 10px rgba(0,255,255,0.5);}
.products {display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:30px;}
.product-card {background:rgba(255,255,255,0.05);backdrop-filter:blur(6px);border-radius:20px;overflow:hidden;position:relative;transition:transform 0.5s,box-shadow 0.5s;}
.product-card:hover {transform:translateY(-15px);box-shadow:0 25px 50px rgba(0,255,255,0.3);}
.product-card img {width:100%;height:180px;object-fit:cover;transition:transform 0.3s;}
.product-card:hover img {transform:scale(1.08);}
.card-content {padding:20px;text-align:center;animation:floatUp 4s ease-in-out infinite alternate;}
.card-content h3 {font-size:22px;color:#00ffff;margin:10px 0 5px;}
.card-content p {font-size:14px;color:#cceeff;height:45px;overflow:hidden;margin-bottom:10px;}
.card-content strong {display:block;font-size:16px;color:#00ffff;margin-bottom:8px;}
.badge {display:inline-block;background:rgba(0,255,255,0.2);color:#00ffff;padding:4px 10px;border-radius:8px;font-size:12px;margin:2px 3px;backdrop-filter:blur(4px);}

/* ===== FOOTER ===== */
footer {text-align:center;padding:30px 0;background:rgba(26,26,26,0.85);backdrop-filter:blur(6px);color:#00ffff;font-weight:500;}

/* ===== ANIMATIONS ===== */
@keyframes fadeInUp {0%{opacity:0;transform:translateY(50px);}100%{opacity:1;transform:translateY(0);}}
@keyframes floatUp {0%{transform:translateY(0);}100%{transform:translateY(-10px);}}
@keyframes gradientMove {0%{background-position:0% 50%;}100%{background-position:100% 50%;}}

/* ===== RESPONSIVE ===== */
@media(max-width:600px){
    header{flex-direction:column;gap:10px;padding:15px;}
    header nav{flex-wrap:wrap;justify-content:center;}
    .hero-content h1{font-size:36px;}
    .hero-content p{font-size:16px;}
}
</style>
</head>
<body>

<div id="particles-js"></div>

<header>
    <div class="logo">MCAS</div>
    <nav>
        <a href="about_us.php">About Us</a>
        <a href="contact_us.php">Contact Us</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
        <a href="admin_login.php">Admin</a>
    </nav>
</header>

<!-- ===== HERO CAROUSEL ===== -->
<section class="hero">
    <div class="hero-slide active" style="background-image:url('images/hero1.jpg');"></div>
    <div class="hero-slide" style="background-image:url('images/hero2.jpg');"></div>
    <div class="hero-slide" style="background-image:url('images/hero3.jpg');"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1>Premium Computer Accessories</h1>
        <p>High-quality products to elevate your gaming and work experience</p>
        <a href="login.php" class="button">Shop Now</a>
    </div>
</section>

<!-- ===== PRODUCTS SECTION ===== -->
<div class="container">
    <h2 class="section-title">Featured Products</h2>
    <div class="products">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                    <div class="card-content">
                        <h3><?= htmlspecialchars($row['name']) ?></h3>
                        <p><?= htmlspecialchars($row['description']) ?></p>
                        <strong>RM <?= number_format($row['price'],2) ?></strong>
                        <div class="badge"><?= htmlspecialchars($row['brand']) ?></div>
                        <div class="badge"><?= htmlspecialchars($row['category']) ?></div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color:#ccc;text-align:center;">No products available.</p>
        <?php endif; ?>
    </div>
</div>

<footer>
    &copy; 2025 Machap Computer Accessories System (MCAS)
</footer>

<!-- Particles.js -->
<script src="https://cdn.jsdelivr.net/npm/particles.js"></script>
<script>
particlesJS("particles-js", {
  "particles": {
    "number": {"value":70},
    "color": {"value":"#00ffff"},
    "shape": {"type":"circle"},
    "opacity": {"value":0.4,"random":true},
    "size": {"value":3,"random":true},
    "line_linked": {"enable":true,"distance":120,"color":"#00ffff","opacity":0.2,"width":1},
    "move": {"enable":true,"speed":1.5,"direction":"none","random":true,"straight":false}
  },
  "interactivity": {"events":{"onhover":{"enable":true,"mode":"grab"}}},
  "retina_detect": true
});
</script>

<!-- Hero Carousel -->
<script>
let slides = document.querySelectorAll('.hero-slide');
let current = 0;
function showSlide(index){
    slides.forEach((s,i)=> s.classList.remove('active'));
    slides[index].classList.add('active');
}
setInterval(()=>{ current = (current + 1) % slides.length; showSlide(current); },5000);
</script>

</body>
</html>
