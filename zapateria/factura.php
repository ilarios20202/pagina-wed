<?php
require 'config.php';
require 'lib/pdf_simple.php';

if (!isset($_SESSION['usuario'])) { 
    header("Location: index.php"); 
    exit; 
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    die("El carrito está vacío, no se puede generar factura.");
}

if (!isset($_SESSION['selected_user'])) {
    die("No hay usuario seleccionado.");
}

$cart = $_SESSION['cart'];
$user = $_SESSION['selected_user'];



$lineas = [];
$lineas[] = "=== FACTURA DE COMPRA ===";
$lineas[] = " ";
$lineas[] = "Cliente: " . $user['nombre'] . " " . $user['apellido'];
$lineas[] = "Correo: " . $user['correo'];
$lineas[] = "-------------------------------------------";

$total = 0;

foreach ($cart as $item) {

    $p = $item['producto'];
    $cantidad = $item['cantidad'] ?? 1;
    $subtotal = $p['precio'] * $cantidad;
    $total += $subtotal;

    $lineas[] = "Producto: " . $p['marca'] . " " . $p['modelo'];
    $lineas[] = "Talla: " . $p['talla'] . " - Color: " . $p['color'];
    $lineas[] = "Precio: $" . number_format($p['precio'], 2);
    $lineas[] = "Cantidad: " . $cantidad;
    $lineas[] = "Subtotal: $" . number_format($subtotal, 2);
    $lineas[] = "-------------------------------------------";
}

$lineas[] = "TOTAL A PAGAR: $" . number_format($total, 2);
$lineas[] = " ";
$lineas[] = "Gracias por su compra.";

$filename = "factura_" . date("Ymd_His") . ".pdf";


pdf_simple_output($filename, $lineas);


unset($_SESSION['cart']);
unset($_SESSION['selected_user']);

exit;

?>