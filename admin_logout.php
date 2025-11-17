<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Logged Out - MCAS Admin</title>
<style>
body {
  font-family: Arial, sans-serif;
  background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
  color: #fff;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
}
.container {
  background: rgba(255,255,255,0.05);
  padding: 40px 60px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 6px 18px rgba(0,0,0,0.3);
}
h1 {
  color: #00ffff;
  margin-bottom: 15px;
}
p {
  color: #cce;
  margin-bottom: 25px;
}
a.btn {
  display: inline-block;
  padding: 10px 18px;
  margin: 6px;
  background: #00ffff;
  color: #000;
  border-radius: 8px;
  text-decoration: none;
  font-weight: bold;
  transition: 0.3s;
}
a.btn:hover {
  background: #00cccc;
}
</style>
</head>
<body>
  <div class="container">
    <h1>You have successfully logged out</h1>
    <p>Thank you for using the MCAS Admin Panel.</p>
    <a href="admin_login.php" class="btn">Back to Admin Login</a>
    <a href="index.php" class="btn">Go to User Page</a>
  </div>
</body>
</html>
