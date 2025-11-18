<?php
session_start();
include 'db.php';


if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// 删除反馈操作
if(isset($_GET['delete_id'])){
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: admin_user_feedback.php"); // 删除后刷新页面
    exit();
}

// 获取所有用户提交的反馈
$result = $conn->query("SELECT id, user_id, message, rating, created_at
                        FROM feedback
                        ORDER BY created_at DESC");
$feedbacks = [];
if($result->num_rows>0){
    while($row=$result->fetch_assoc()){
        $feedbacks[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin User Feedback - MCAS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
body{margin:0;padding:0;font-family:'Poppins',sans-serif;background:#f4f6f8;}
header{background:#111;color:#00ffff;padding:15px 32px;font-size:22px;font-weight:700;}
.container{max-width:1000px;margin:50px auto;background:#fff;padding:30px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.1);}
h2{color:#00bfbf;margin-bottom:20px;text-align:center;}
.back-btn{display:inline-block;margin-bottom:20px;padding:10px 18px;background:#00bfbf;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;transition:0.3s;}
.back-btn:hover{background:#00ffff;color:#000;}
table{width:100%;border-collapse:collapse;margin-top:15px;}
th,td{padding:12px 15px;text-align:left;border-bottom:1px solid #ccc;}
th{background:#00bfbf;color:#fff;}
tr:hover{background:#f1fdfd;}
.rating{color:#00bfbf;font-weight:600;}
.delete-btn{color:#fff;background:#ff4d4f;padding:6px 12px;border-radius:6px;text-decoration:none;transition:0.3s;}
.delete-btn:hover{background:#ff7875;color:#000;}
</style>
</head>
<body>
<header>MCAS - Admin User Feedback</header>
<div class="container">
<h2>User Feedback</h2>
<a href="admin_dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>

<?php if(empty($feedbacks)){ ?>
    <p>No feedback submitted yet.</p>
<?php } else { ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>User ID</th>
                <th>Message</th>
                <th>Rating</th>
                <th>Submitted At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($feedbacks as $fb){ ?>
            <tr>
                <td><?php echo htmlspecialchars($fb['id']); ?></td>
                <td><?php echo htmlspecialchars($fb['user_id']); ?></td>
                <td><?php echo htmlspecialchars($fb['message']); ?></td>
                <td class="rating"><?php echo htmlspecialchars($fb['rating']); ?></td>
                <td><?php echo htmlspecialchars($fb['created_at']); ?></td>
                <td>
                    <a href="admin_user_feedback.php?delete_id=<?php echo $fb['id']; ?>" 
                       class="delete-btn" onclick="return confirm('Are you sure you want to delete this feedback?');">
                       <i class="fas fa-trash-alt"></i> Delete
                    </a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } ?>
</div>
</body>
</html>
