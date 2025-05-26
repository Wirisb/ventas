<?php
session_start();

// Conexión a la base de datos y carga de TCPDF
require_once('../../admin/inc/config.php');
require_once('../../assets/tcpdf/tcpdf.php');

// Verificamos que el cliente haya iniciado sesión
if (!isset($_SESSION['customer'])) {
    header('location: ../../login.php');
    exit;
}

// Validación: si no hay total definido, detenemos el proceso
if (!isset($_SESSION['final_total'])) {
    die("Error: No se ha definido el total de la compra.");
}

// Datos del cliente y del pedido
$customer_id = $_SESSION['customer']['cust_id'];
$customer_name = $_SESSION['customer']['cust_name'];
$customer_email = $_SESSION['customer']['cust_email'];
$payment_date = date('Y-m-d H:i:s');
$payment_method = 'Efectivo';
$payment_status = 'Pendiente';
$shipping_status = 'Pendiente';
$payment_id = time(); // Se usa como ID de factura único
$paid_amount = $_SESSION['final_total'];

// Insertar en la tabla tbl_payment
$stmt = $pdo->prepare("INSERT INTO tbl_payment (
    customer_id, customer_name, customer_email, payment_date, txnid, paid_amount,
    card_number, card_cvv, card_month, card_year, bank_transaction_info,
    payment_method, payment_status, shipping_status, payment_id
) VALUES (?, ?, ?, ?, '', ?, '', '', '', '', '', ?, ?, ?, ?)");

$stmt->execute([
    $customer_id,
    $customer_name,
    $customer_email,
    $payment_date,
    $paid_amount,
    $payment_method,
    $payment_status,
    $shipping_status,
    $payment_id
]);

// Insertar los productos comprados en tbl_order
foreach ($_SESSION['cart_p_id'] as $key => $value) {
    $product_id = $_SESSION['cart_p_id'][$key];
    $product_name = $_SESSION['cart_p_name'][$key];
    $product_size = $_SESSION['cart_size_name'][$key];
    $product_color = $_SESSION['cart_color_name'][$key];
    $product_qty = $_SESSION['cart_p_qty'][$key];
    $product_price = $_SESSION['cart_p_current_price'][$key];

    $stmt = $pdo->prepare("INSERT INTO tbl_order (
        product_id, product_name, size, color, quantity, unit_price, payment_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $product_id,
        $product_name,
        $product_size,
        $product_color,
        $product_qty,
        $product_price,
        $payment_id
    ]);
}

// Crear la factura PDF con TCPDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

// Encabezado y datos generales
$html = "
<h1>Factura de Compra</h1>
<p><strong>Cliente:</strong> $customer_name</p>
<p><strong>Email:</strong> $customer_email</p>
<p><strong>Fecha:</strong> $payment_date</p>
<p><strong>Método de Pago:</strong> Efectivo</p>
<p><strong>Estado del Pago:</strong> Pendiente</p>
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

// Detalle de los productos
foreach ($_SESSION['cart_p_id'] as $key => $value) {
    $product_name = $_SESSION['cart_p_name'][$key];
    $product_size = $_SESSION['cart_size_name'][$key];
    $product_color = $_SESSION['cart_color_name'][$key];
    $product_qty = $_SESSION['cart_p_qty'][$key];
    $product_price = $_SESSION['cart_p_current_price'][$key];
    $total_item = $product_qty * $product_price;

    $html .= "<tr>
        <td>$product_name</td>
        <td>$product_size</td>
        <td>$product_color</td>
        <td>$product_qty</td>
        <td>\$$product_price</td>
        <td>\$$total_item</td>
    </tr>";
}

$html .= "</table><br><h3>Total pagado: \$$paid_amount</h3>";

// Generar PDF y mostrarlo en el navegador
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output("Factura_$payment_id.pdf", 'I');

// Vaciar carrito
unset($_SESSION['cart_p_id']);
unset($_SESSION['cart_p_name']);
unset($_SESSION['cart_size_name']);
unset($_SESSION['cart_color_name']);
unset($_SESSION['cart_p_qty']);
unset($_SESSION['cart_p_current_price']);
unset($_SESSION['final_total']);
?>
