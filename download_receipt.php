<?php
require('libs/fpdf/fpdf.php');
include 'db.php';

if (!isset($_GET['order_id'])) {
    die("Order ID not provided.");
}

$order_id = intval($_GET['order_id']);

// 取得订单资料
$order_query = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$order_query->bind_param("i", $order_id);
$order_query->execute();
$order_result = $order_query->get_result();
$order = $order_result->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

// 取得订单商品
$item_query = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$item_query->bind_param("i", $order_id);
$item_query->execute();
$item_result = $item_query->get_result();

// === 生成 PDF ===
$pdf = new FPDF();
$pdf->AddPage();

// Title
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 10, 'Payment Receipt', 0, 1, 'C');
$pdf->Ln(5);

// Order info
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, 'Order ID: #' . $order_id, 0, 1);
$pdf->Cell(0, 8, 'Date: ' . date("F j, Y, g:i a", strtotime($order['created_at'])), 0, 1);
$pdf->Cell(0, 8, 'Status: ' . ucfirst($order['status']), 0, 1);
$pdf->Ln(5);

// Table header
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(30, 58, 138); // 深蓝色标题背景
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(90, 10, 'Product', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Qty', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Subtotal (RM)', 1, 1, 'C', true);

// Table rows
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
$total = 0;
while ($item = $item_result->fetch_assoc()) {
    $subtotal = $item['quantity'] * $item['price'];
    $total += $subtotal;
    $pdf->Cell(90, 10, $item['product_name'], 1);
    $pdf->Cell(30, 10, $item['quantity'], 1, 0, 'C');
    $pdf->Cell(50, 10, number_format($subtotal, 2), 1, 1, 'R');
}

// Total
$pdf->SetFont('Arial', 'B', 13);
$pdf->Cell(120, 10, 'Total', 1);
$pdf->Cell(50, 10, 'RM ' . number_format($total, 2), 1, 1, 'R');
$pdf->Ln(10);

$pdf->SetFont('Arial', 'I', 11);
$pdf->Cell(0, 10, 'Thank you for your purchase!', 0, 1, 'C');

$pdf->Output('D', 'Receipt_Order_' . $order_id . '.pdf');
?>
