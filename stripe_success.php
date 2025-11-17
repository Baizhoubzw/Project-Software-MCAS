<?php
session_start();
include 'db.php';
require 'vendor/autoload.php';
require('libs/fpdf/fpdf.php'); // 确认路径正确

use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['session_id'])) {
    header("Location: products.php");
    exit();
}

$session_id = $_GET['session_id'];

Stripe::setApiKey('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');

try {
    $checkout_session = StripeSession::retrieve($session_id);
    $payment_status = $checkout_session->payment_status;
    $stripe_amount = $checkout_session->amount_total / 100; // MYR
    $customer_email = $checkout_session->customer_details->email ?? 'N/A';

    if ($payment_status === 'paid') {
        // 更新订单状态 Paid
        $stmt = $conn->prepare("UPDATE orders SET statuss='Paid', stripe_session_id=? WHERE stripe_session_id=? OR (user_id=? AND statuss='Pending') ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("ssi", $session_id, $session_id, $user_id);
        $stmt->execute();

        // 从数据库获取订单信息
        $stmt = $conn->prepare("SELECT * FROM orders WHERE stripe_session_id=?");
        $stmt->bind_param("s", $session_id);
        $stmt->execute();
        $order_result = $stmt->get_result();
        $order = $order_result->fetch_assoc();

        // 计算税和 subtotal
        $total_paid = $order['total_price'];
        $subtotal = $total_paid / 1.06;
        $tax = $total_paid - $subtotal;

        // 生成 PDF
        $pdf = new FPDF('P','mm','A4');
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,10,'Machap Computer Accessories System (MCAS)',0,1,'C');
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(0,7,'Payment Receipt',0,1,'C');
        $pdf->Ln(5);

        $pdf->SetFont('Arial','',12);
        $pdf->Cell(0,6,'Customer Email: '.$customer_email,0,1);
        $pdf->Cell(0,6,'Payment Method: '.$order['payment_method'],0,1);
        $pdf->Ln(3);
        $pdf->Cell(0,6,'Subtotal (Before Tax): RM '.number_format($subtotal,2),0,1);
        $pdf->Cell(0,6,'6% SST: RM '.number_format($tax,2),0,1);
        $pdf->Cell(0,6,'Total Paid: RM '.number_format($total_paid,2),0,1);
        $pdf->Ln(3);
        $pdf->Cell(0,6,'Transaction ID: '.$session_id,0,1);
        $pdf->Cell(0,6,'Date: '.date('Y-m-d H:i:s'),0,1);

        $receipt_file = 'receipts/receipt_'.$order['id'].'.pdf';
        if(!is_dir('receipts')) mkdir('receipts', 0777, true);
        $pdf->Output('F', $receipt_file);

    } else {
        die("Payment not successful.");
    }

} catch (\Exception $e) {
    die("Stripe Error: ".$e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Success</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f8f9fa;font-family:sans-serif}
.card{max-width:600px;margin:50px auto;padding:30px;border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,0.1)}
h2{margin-bottom:20px}
</style>
</head>
<body>
<div class="card text-center">
    <h2 class="text-success">✅ Payment Successful</h2>
    <p>Thank you for your payment, your transaction has been completed successfully.</p>
    <p><strong>Total Paid:</strong> RM <?= number_format($total_paid,2) ?></p>
    <p><strong>Tax (6%):</strong> RM <?= number_format($tax,2) ?></p>
    <p><strong>Transaction ID:</strong> <?= $session_id ?></p>
    <div class="mt-4">
        <a href="<?= $receipt_file ?>" class="btn btn-outline-primary" download>📄 Download Receipt</a>
        <a href="products.php" class="btn btn-success">🏠 Return to Products</a>
    </div>
</div>
</body>
</html>
