<?php
require('fpdf/fpdf.php');
include 'db.php';

if (!isset($_GET['order_id'])) {
    die("Order ID not provided.");
}
$order_id = intval($_GET['order_id']);

$order_query = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$order_query->bind_param("i", $order_id);
$order_query->execute();
$order = $order_query->get_result()->fetch_assoc();

$item_query = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$item_query->bind_param("i", $order_id);
$item_query->execute();
$items = $item_query->get_result();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Payment Receipt',0,1,'C');
$pdf->Ln(5);

$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,"Order ID: #$order_id",0,1);
$pdf->Cell(0,8,"Date: ".$order['created_at'],0,1);
$pdf->Cell(0,8,"Status: ".$order['status'],0,1);
$pdf->Ln(10);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(90,10,'Product',1);
$pdf->Cell(30,10,'Qty',1);
$pdf->Cell(40,10,'Price (RM)',1);
$pdf->Cell(30,10,'Total',1);
$pdf->Ln();

$pdf->SetFont('Arial','',12);
$total = 0;
while ($item = $items->fetch_assoc()) {
    $subtotal = $item['quantity'] * $item['price'];
    $total += $subtotal;
    $pdf->Cell(90,10,$item['product_name'],1);
    $pdf->Cell(30,10,$item['quantity'],1);
    $pdf->Cell(40,10,number_format($item['price'],2),1);
    $pdf->Cell(30,10,number_format($subtotal,2),1);
    $pdf->Ln();
}

$pdf->SetFont('Arial','B',12);
$pdf->Cell(160,10,'Total',1);
$pdf->Cell(30,10,'RM '.number_format($total,2),1);
$pdf->Ln(15);

$pdf->SetFont('Arial','I',10);
$pdf->Cell(0,10,'Thank you for your purchase!',0,1,'C');
$pdf->Output("D","receipt_order_$order_id.pdf");
?>
