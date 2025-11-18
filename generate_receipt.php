<?php
session_start();
require('fpdf186/fpdf.php'); // Make sure you have the FPDF library folder in your project
include 'db.php';

if (!isset($_GET['id'])) {
    die("❌ Invalid request.");
}

$order_id = intval($_GET['id']);

// Get order info
$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("❌ Order not found.");
}

// Get order items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id=?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();

// Create PDF
class PDF extends FPDF {
    function Header() {
        // Logo
        $this->SetFont('Arial','B',16);
        $this->Cell(0,10,'FACILITY BOOKING SYSTEM - RECEIPT',0,1,'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Thank you for your purchase! Generated on '.date("Y-m-d H:i:s"),0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

// --- Order Info ---
$pdf->Cell(0,10,"Order ID: ".$order['id'],0,1);
$pdf->Cell(0,10,"Customer ID: ".$order['user_id'],0,1);
$pdf->Cell(0,10,"Bank / Payment Method: ".$order['bank'],0,1);
$pdf->Cell(0,10,"Status: ".$order['statuss'],0,1);
$pdf->Cell(0,10,"Date: ".$order['created_at'],0,1);
$pdf->Ln(10);

// --- Table Header ---
$pdf->SetFont('Arial','B',12);
$pdf->Cell(90,10,'Product Name',1);
$pdf->Cell(30,10,'Price (RM)',1,0,'C');
$pdf->Cell(30,10,'Qty',1,0,'C');
$pdf->Cell(40,10,'Total (RM)',1,1,'C');
$pdf->SetFont('Arial','',12);

$total_amount = 0;
while ($item = $items->fetch_assoc()) {
    $line_total = $item['price'] * $item['quantity'];
    $total_amount += $line_total;

    $pdf->Cell(90,10,$item['product_name'],1);
    $pdf->Cell(30,10,number_format($item['price'],2),1,0,'C');
    $pdf->Cell(30,10,$item['quantity'],1,0,'C');
    $pdf->Cell(40,10,number_format($line_total,2),1,1,'C');
}

$pdf->Ln(8);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(150,10,'Subtotal',1);
$pdf->Cell(40,10,'RM '.number_format($total_amount,2),1,1,'C');

// --- Tax (6%) ---
$tax = $total_amount * 0.06;
$pdf->Cell(150,10,'Tax (6%)',1);
$pdf->Cell(40,10,'RM '.number_format($tax,2),1,1,'C');

// --- Grand Total ---
$grand_total = $total_amount + $tax;
$pdf->SetFont('Arial','B',14);
$pdf->Cell(150,12,'Grand Total',1);
$pdf->Cell(40,12,'RM '.number_format($grand_total,2),1,1,'C');

$pdf->Ln(10);
$pdf->SetFont('Arial','I',11);
$pdf->MultiCell(0,8,"Delivery Address:\nThis is a digital receipt. Your order will be processed and shipped to the registered address on your profile.\n\nIf you wish to cancel, please visit 'My Orders' within 24 hours.");

// Output (Force Download)
$pdf->Output('D', 'Receipt_Order_'.$order_id.'.pdf');
exit();
?>
