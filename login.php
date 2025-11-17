<?php
include 'db.php';
session_start();

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM user WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: dashboard.php"); 
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - MCAS</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
body {margin:0; font-family:'Roboto',sans-serif; background:linear-gradient(135deg,#0f2027,#203a43,#2c5364); color:#fff; overflow:hidden; position:relative;}
#particles-js{position:fixed;width:100%;height:100%;top:0;left:0;z-index:-1;}

/* 登录容器 */
.container {
    width:400px; max-width:90%;
    margin:100px auto; 
    background: rgba(0,0,0,0.6); 
    backdrop-filter: blur(10px); 
    padding:40px; 
    border-radius:15px; 
    box-shadow:0 8px 30px rgba(0,255,255,0.3); 
    position:relative;
    z-index:1;
    animation: floatUp 2s ease;
}

/* 标题 */
h2 {
    text-align:center; 
    margin-bottom:30px; 
    font-size:28px;
    background: linear-gradient(90deg,#00ffff,#00bfbf,#00ffff); 
    -webkit-background-clip: text; 
    -webkit-text-fill-color: transparent; 
    text-shadow:0 0 12px rgba(0,255,255,0.5);
}

/* 输入框 */
input[type="email"], input[type="password"] {
    width:100%; 
    padding:14px; 
    margin:10px 0; 
    border:none; 
    border-radius:8px;
    background: rgba(255,255,255,0.05);
    color:#fff;
    font-size:16px;
}
input::placeholder { color:#ccc; }

/* 登录按钮 */
input[type="submit"] {
    width:100%; 
    padding:14px; 
    margin-top:20px; 
    border:none; 
    border-radius:10px; 
    background: linear-gradient(45deg,#00ffff,#00bfbf); 
    color:#000; 
    font-weight:700; 
    cursor:pointer; 
    font-size:16px;
    box-shadow:0 6px 20px rgba(0,255,255,0.4);
    transition:0.4s;
    position:relative;
    overflow:hidden;
}
input[type="submit"]::before {
    content:""; position:absolute; top:0; left:-80%; width:50%; height:100%; background: rgba(255,255,255,0.3); transform:skewX(-25deg); transition:0.7s;
}
input[type="submit"]:hover::before { left:130%; }
input[type="submit"]:hover { transform:scale(1.05); box-shadow:0 10px 30px rgba(0,255,255,0.6); }

/* 链接 */
a {color:#00ffff; text-decoration:none; display:block; text-align:center; margin-top:20px; transition:0.3s;}
a:hover {color:#00bfbf; text-decoration:underline;}

/* 错误提示 */
.error {color:#ff6b6b; text-align:center; margin-bottom:15px; text-shadow:0 0 8px rgba(255,0,0,0.5);}

/* 动画 */
@keyframes floatUp {0%{transform:translateY(20px); opacity:0;} 100%{transform:translateY(0); opacity:1;}}

/* 响应式 */
@media(max-width:480px){ .container{margin:60px 20px; padding:30px;} h2{font-size:24px;} input[type="submit"]{padding:12px;} }
</style>
</head>
<body>

<div id="particles-js"></div>

<div class="container">
    <h2>Login</h2>
    <?php if(isset($error)){ echo "<div class='error'>$error</div>"; } ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" name="login" value="Login">
    </form>
    <a href="register.php">Don't have an account? Register</a>
</div>

<!-- Particles.js -->
<script src="https://cdn.jsdelivr.net/npm/particles.js"></script>
<script>
particlesJS("particles-js", {
  "particles": {
    "number":{"value":80},
    "color":{"value":"#00ffff"},
    "shape":{"type":"circle"},
    "opacity":{"value":0.4,"random":true},
    "size":{"value":3,"random":true},
    "line_linked":{"enable":true,"distance":120,"color":"#00ffff","opacity":0.15,"width":1},
    "move":{"enable":true,"speed":1.5,"direction":"none","random":true,"straight":false}
  },
  "interactivity":{"events":{"onhover":{"enable":true,"mode":"grab"}}},
  "retina_detect": true
});
</script>

</body>
</html>
