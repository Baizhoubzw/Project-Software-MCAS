<?php
session_start();
include 'db.php';

// 获取 FAQ
$result = $conn->query("SELECT * FROM faq ORDER BY category, id ASC");
$faqs = [];
if($result->num_rows>0){
    while($row=$result->fetch_assoc()){
        $faqs[$row['category']][] = $row;
    }
} else {
    // 如果数据库为空，自动生成示例
    $faqs['General'][] = ['question'=>'What is MCAS?', 'answer'=>'MCAS is Machap Computer Accessories System, your online store for computer accessories.'];
    $faqs['Orders'][] = ['question'=>'How do I track my order?', 'answer'=>'Go to "My Orders" in your account to see the status of your order.'];
    $faqs['Payment'][] = ['question'=>'Which payment methods are available?', 'answer'=>'We accept Credit Card, FPX, Touch n Go, Bank Transfer, and Stripe.'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FAQ - MCAS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
body{margin:0;padding:0;font-family:'Poppins',sans-serif;background:#f4f6f8;}
header{background:#111;color:#00ffff;padding:15px 32px;font-size:22px;font-weight:700;}
.container{max-width:900px;margin:50px auto;padding:30px;background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.1);}
h2{text-align:center;color:#00bfbf;margin-bottom:15px;}
.back-btn{display:inline-block;margin:0 auto 30px auto;padding:10px 18px;background:#00bfbf;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;transition:0.3s;}
.back-btn:hover{background:#00ffff;color:#000;}
.category{margin-bottom:25px;}
.question{background:#00bfbf;color:#fff;padding:12px;margin-bottom:5px;cursor:pointer;border-radius:8px;font-weight:600;transition:0.3s;}
.question:hover{background:#00ffff;color:#000;}
.answer{padding:12px 15px;border-left:3px solid #00bfbf;background:#f9f9f9;display:none;margin-bottom:10px;border-radius:0 8px 8px 8px;}
</style>
</head>
<body>
<header>MCAS - FAQ</header>
<div class="container">
<h2>Frequently Asked Questions</h2>
<a href="dashboard.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
<?php foreach($faqs as $category => $items){ ?>
<div class="category">
<h3 style="color:#00bfbf;margin-bottom:15px;"><?php echo htmlspecialchars($category); ?></h3>
<?php foreach($items as $item){ ?>
<div class="question"><?php echo htmlspecialchars($item['question']); ?></div>
<div class="answer"><?php echo htmlspecialchars($item['answer']); ?></div>
<?php } ?>
</div>
<?php } ?>
</div>
<script>
document.querySelectorAll('.question').forEach(q=>{
    q.addEventListener('click',()=> {
        const a=q.nextElementSibling;
        a.style.display = a.style.display==='block'?'none':'block';
    });
});
</script>
</body>
</html>
