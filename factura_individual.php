<?php
session_start();
require_once('../../admin/inc/config.php');
require_once('../../assets/tcpdf/tcpdf.php');

// Verificar que llegue el ID
if (!isset($_POST['payment_id'])) {
    die("No se recibió el ID del pago.");
}

$payment_id = $_POST['payment_id'];

// Obtener la información del pago
$stmt = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_id = ?");
$stmt->execute([$payment_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    die("Factura no encontrada.");
}

$customer_name = $payment['customer_name'];
$customer_email = $payment['customer_email'];
$payment_date = $payment['payment_date'];
$paid_amount = $payment['paid_amount'];
$payment_method = $payment['payment_method'];
$payment_status = $payment['payment_status'];

// Crear PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

// Encabezado
$html = "
<h1>Factura de Compra</h1>
<p><strong>Cliente:</strong> $customer_name</p>
<p><strong>Email:</strong> $customer_email</p>
<p><strong>Fecha:</strong> $payment_date</p>
<p><strong>Método de Pago:</strong> $payment_method</p>
<p><strong>Estado del Pago:</strong> $payment_status</p>
<br>
<table border=\"1\" cellpadding=\"5\">
<tr>
    <th>Producto</th>
    <th>Talla</th>
    <th>Color</th>
    <th>Cantidad</th>
    <th>Precio</th>
    <th>Total</th>
</tr>";

// Obtener los productos asociados al pago
$stmt2 = $pdo->prepare("SELECT * FROM tbl_order WHERE payment_id = ?");
$stmt2->execute([$payment_id]);
while ($item = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    $total_item = $item['quantity'] * $item['unit_price'];
    $html .= "<tr>
        <td>{$item['product_name']}</td>
        <td>{$item['size']}</td>
        <td>{$item['color']}</td>
        <td>{$item['quantity']}</td>
        <td>\${$item['unit_price']}</td>
        <td>\$" . number_format($total_item, 2) . "</td>
    </tr>";
}

$html .= "</table><br><h3>Total pagado: \$$paid_amount</h3>";

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output("Factura_$payment_id.pdf", 'I');
