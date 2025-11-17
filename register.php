<?php
include 'db.php';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $contact = $_POST['contact_number'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO user (username, email, contact_number, password) 
            VALUES ('$username', '$email', '$contact', '$password')";

    if ($conn->query($sql) === TRUE) {
        header("Location: login.php"); // 注册成功直接跳到登录页
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - Machap Computer Accessories System（MCAS）</title>
<style>
body {margin:0; font-family:'Arial',sans-serif; background:linear-gradient(to right,#0f2027,#203a43,#2c5364); color:#fff;}
.container {width:400px; margin:80px auto; background: rgba(0,0,0,0.7); padding:30px; border-radius:10px; box-shadow:0 0 20px #000;}
h2 {text-align:center; margin-bottom:20px; color:#00ffff;}
input[type="text"], input[type="email"], input[type="password"] {width:100%; padding:12px; margin:8px 0; border:none; border-radius:5px;}
input[type="submit"] {width:100%; padding:12px; margin-top:15px; border:none; border-radius:5px; background:#00ffff; color:#000; font-weight:bold; cursor:pointer; transition:0.3s;}
input[type="submit"]:hover {background:#00bfbf;}
a {color:#00ffff; text-decoration:none; display:block; text-align:center; margin-top:15px;}
</style>
</head>
<body>
<div class="container">
    <h2>Register</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="contact_number" placeholder="Contact Number" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" name="register" value="Register">
    </form>
    <a href="login.php">Already have an account? Login</a>
</div>
</body>
</html>
