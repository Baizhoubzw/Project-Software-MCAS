<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$rating = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $message = $_POST['message'] ?? '';
    $rating = intval($_POST['rating'] ?? 0);

    if(!empty($message)){
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, message, rating) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $user_id, $message, $rating);
        $stmt->execute();
        $success = "Thank you! Your feedback has been submitted.";
        $message = '';
        $rating = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Feedback - MCAS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
body{margin:0;padding:0;font-family:'Poppins',sans-serif;background:#f4f6f8;}
header{background:#111;color:#00ffff;padding:15px 32px;font-size:22px;font-weight:700;}
.container{max-width:700px;margin:50px auto;background:#fff;padding:30px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.1);}
h2{color:#00bfbf;margin-bottom:15px;text-align:center;}
.back-btn{display:inline-block;margin:0 auto 20px auto;padding:10px 18px;background:#00bfbf;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;transition:0.3s;}
.back-btn:hover{background:#00ffff;color:#000;}
textarea{width:100%;height:120px;padding:12px;border-radius:8px;border:1px solid #ccc;margin-bottom:15px;resize:none;font-size:14px;}
input[type="number"]{width:80px;padding:8px;margin-bottom:15px;border-radius:6px;border:1px solid #ccc;}
button{width:100%;padding:12px;background:#00bfbf;color:#fff;border:none;border-radius:10px;font-weight:600;cursor:pointer;transition:0.3s;}
button:hover{background:#00ffff;color:#000;}
.success{color:green;margin-bottom:15px;text-align:center;font-weight:600;}
</style>
</head>
<body>
<header>MCAS - Feedback</header>
<div class="container">
<h2>We Value Your Feedback</h2>
<a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
<?php if(!empty($success)) echo "<div class='success'>{$success}</div>"; ?>
<form method="POST">
    <textarea name="message" placeholder="Write your feedback here..." required><?php echo htmlspecialchars($message); ?></textarea>
    <label>Rating (optional 1-5): </label>
    <input type="number" name="rating" min="1" max="5" value="<?php echo htmlspecialchars($rating); ?>"><br>
    <button type="submit"><i class="fas fa-paper-plane"></i> Submit Feedback</button>
</form>
</div>
</body>
</html>
