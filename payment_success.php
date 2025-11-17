<?php
// payment_success.php
session_start();
include 'db.php';
require_once 'vendor/autoload.php'; // Stripe SDK

use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = null;
$generated_pdf = null;

// === 判斷來源 ===
if (isset($_GET['order_id'])) {
    // Fake / Bank Payment
    $order_id = intval($_GET['order_id']);

    // 抓訂單
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    if (!$order) { echo "Order not found"; exit(); }

} elseif (isset($_GET['session_id'])) {
    // Stripe Payment
    $session_id = $_GET['session_id'];

    // 設定 Stripe Secret Key
   
Stripe::setApiKey('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');

    try {
        $session = StripeSession::retrieve($session_id);

        if ($session->payment_status === 'paid') {
            // 付款成功，檢查是否已有 order
            $stmt = $conn->prepare("SELECT * FROM orders WHERE stripe_session_id = ? AND user_id = ?");
            $stmt->bind_param("si", $session_id, $user_id);
            $stmt->execute();
            $order = $stmt->get_result()->fetch_assoc();

            if (!$order) {
                // 建立訂單
                $total_price = $session->amount_total / 100; // Stripe 金額是分
                $payment_method = 'Stripe';
                $transaction_id = $session->payment_intent;
                $created_at = date('Y-m-d H:i:s');

                $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, statuss, payment_method, transaction_id, stripe_session_id, created_at) VALUES (?,?,?,?,?,?,?)");
                $status = 'Paid';
                $stmt->bind_param("idsssss", $user_id, $total_price, $status, $payment_method, $transaction_id, $session_id, $created_at);
                $stmt->execute();
                $order_id = $stmt->insert_id;

                // 建立 order_items
                $line_items = $session->display_items ?? []; // Stripe v18 可能不同
                foreach ($line_items as $li) {
                    $name = $li->custom->name ?? 'Item';
                    $price = ($li->amount ?? 0)/100;
                    $quantity = $li->quantity ?? 1;

                    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity) VALUES (?,?,?,?)");
                    $stmt->bind_param("isdi", $order_id, $name, $price, $quantity);
                    $stmt->execute();
                }

                // 重新抓取訂單
                $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $order_id, $user_id);
                $stmt->execute();
                $order = $stmt->get_result()->fetch_assoc();
            } else {
                $order_id = $order['id'];
            }

        } else {
            echo "Stripe payment not completed."; exit();
        }

    } catch (\Exception $e) {
        echo "Stripe Error: " . $e->getMessage();
        exit();
    }
} else {
    header("Location: products.php");
    exit();
}

// === 抓 order_items ===
$itq = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itq->bind_param("i", $order_id);
$itq->execute();
$items = $itq->get_result()->fetch_all(MYSQLI_ASSOC);

// === 生成 PDF ===
$fpdf_path = __DIR__ . '/libs/fpdf/fpdf.php';
if (file_exists($fpdf_path)) {
    require_once $fpdf_path;

    $pdf = new FPDF('P','mm','A4');
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,'MCAS - Payment Receipt',0,1,'C');
    $pdf->Ln(4);

    $pdf->SetFont('Arial','',11);
    $pdf->Cell(40,7,'Order ID:',0,0);
    $pdf->Cell(0,7,'#' . $order['id'],0,1);
    $pdf->Cell(40,7,'Date:',0,0);
    $pdf->Cell(0,7,date('Y-m-d H:i', strtotime($order['created_at'])),0,1);
    $pdf->Cell(40,7,'Payment Method:',0,0);
    $pdf->Cell(0,7,($order['payment_method'] ?? '-'),0,1);
    $pdf->Cell(40,7,'Transaction ID:',0,0);
    $pdf->Cell(0,7,($order['transaction_id'] ?? '-'),0,1);
    $pdf->Ln(6);

    // Table header
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(90,8,'Item',1,0);
    $pdf->Cell(30,8,'Unit Price',1,0,'R');
    $pdf->Cell(20,8,'Qty',1,0,'R');
    $pdf->Cell(40,8,'Subtotal (RM)',1,1,'R');

    $pdf->SetFont('Arial','',11);
    $sum = 0;
    foreach ($items as $it) {
        $sub = $it['price'] * $it['quantity'];
        $sum += $sub;
        $pdf->Cell(90,7,mb_strimwidth($it['product_name'],0,60,'...'),1,0);
        $pdf->Cell(30,7,number_format($it['price'],2),1,0,'R');
        $pdf->Cell(20,7,$it['quantity'],1,0,'R');
        $pdf->Cell(40,7,number_format($sub,2),1,1,'R');
    }

    // totals
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(140,8,'Subtotal',1,0,'R');
    $pdf->Cell(40,8,number_format($sum,2),1,1,'R');

    $tax = $sum * 0.06;
    $pdf->SetFont('Arial','',11);
    $pdf->Cell(140,8,'Tax (6% SST)',1,0,'R');
    $pdf->Cell(40,8,number_format($tax,2),1,1,'R');

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(140,10,'Total Paid',1,0,'R');
    $pdf->Cell(40,10,number_format($order['total_price'],2),1,1,'R');

    // Footer note
    $pdf->Ln(8);
    $pdf->SetFont('Arial','I',9);
    $pdf->MultiCell(0,5,'This is a receipt for MCAS. Generated by system.',0,'L');

    // 保存 PDF
    $receipts_dir = __DIR__ . '/receipts';
    if (!is_dir($receipts_dir)) mkdir($receipts_dir, 0755, true);
    $filename = 'receipt_order_' . $order_id . '_' . date('Ymd_His') . '.pdf';
    $filepath = $receipts_dir . '/' . $filename;
    $pdf->Output('F', $filepath);

    $generated_pdf = 'receipts/' . $filename;
} else {
    $error = "FPDF library not found.";
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Receipt - MCAS</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
body{background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#111;padding:24px}
.header{background:#111;color:#00ffff;padding:14px 20px;border-radius:8px;margin-bottom:18px}
.container{max-width:980px;margin:0 auto;background:#fff;padding:22px;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.06)}
.table{width:100%;border-collapse:collapse;margin-top:14px}
.table th,.table td{padding:10px;border-bottom:1px solid #eee;text-align:left}
.right{text-align:right}
.actions{margin-top:18px;display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.btn{background:#00bfbf;color:#042029;padding:10px 14px;border-radius:8px;border:0;cursor:pointer;text-decoration:none}
.btn-secondary{background:#eee;color:#333;padding:10px 14px;border-radius:8px;border:0;text-decoration:none}
.btn-home{background:#00bf7f;color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none}
.btn-download{background:#007bff;color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none}
.note{color:#666;margin-top:8px}
</style>
</head>
<body>
<div class="header"><strong>MCAS</strong> — Payment Receipt</div>
<div class="container">
  <h2 style="margin:0">Payment Successful ✅</h2>
  <div class="note">Order #<?= htmlspecialchars($order['id']) ?> • <?= htmlspecialchars($order['created_at']) ?></div>
  <hr>

  <table class="table">
    <thead><tr><th>Item</th><th class="right">Unit</th><th class="right">Qty</th><th class="right">Subtotal</th></tr></thead>
    <tbody>
      <?php $sum = 0; foreach($items as $it): $sub = $it['price'] * $it['quantity']; $sum += $sub; ?>
      <tr>
        <td><?= htmlspecialchars($it['product_name']) ?></td>
        <td class="right">RM <?= number_format($it['price'],2) ?></td>
        <td class="right"><?= intval($it['quantity']) ?></td>
        <td class="right">RM <?= number_format($sub,2) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr><td colspan="3" class="right"><strong>Subtotal</strong></td><td class="right">RM <?= number_format($sum,2) ?></td></tr>
      <tr><td colspan="3" class="right">Tax (6%)</td><td class="right">RM <?= number_format($sum*0.06,2) ?></td></tr>
      <tr><td colspan="3" class="right"><strong>Total Paid</strong></td><td class="right"><strong>RM <?= number_format($order['total_price'],2) ?></strong></td></tr>
    </tfoot>
  </table>

  <div class="actions">
    <?php if (!empty($generated_pdf)): ?>
      <a href="<?= htmlspecialchars($generated_pdf) ?>" class="btn-download"><i class="fa fa-download"></i> Download Receipt</a>
    <?php endif; ?>
    <a href="javascript:window.print()" class="btn-secondary"><i class="fa fa-print"></i> Print Receipt</a>
    <a href="dashboard.php" class="btn-home"><i class="fa fa-home"></i> Back to Dashboard</a>
  </div>

  <div class="note">The receipt PDF will automatically download. If not, click the “Download Receipt” button above.</div>
</div>

<script>
<?php if (!empty($generated_pdf)): ?>
window.addEventListener('load', () => {
    try {
        const a = document.createElement('a');
        a.href = '<?= htmlspecialchars($generated_pdf) ?>';
        a.download = '';
        document.body.appendChild(a);
        a.click();
        a.remove();
    } catch (e) {
        console.warn("Auto download failed, user can click manually.");
    }
});
<?php endif; ?>

document.addEventListener('keydown', e => {
    if (e.key === "Enter") window.location.href = "dashboard.php";
});
</script>
</body>
</html>
