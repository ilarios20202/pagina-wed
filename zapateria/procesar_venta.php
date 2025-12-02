<?php

if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} elseif (file_exists(__DIR__ . '/.. /config.php')) { 
    require_once __DIR__ . '/../config.php';
} else {
    header('Content-Type: text/plain; charset=utf-8');
    die("Error crítico: no se encontró config.php. Asegúrate de que existe en la carpeta del proyecto.");
}


if (file_exists(__DIR__ . '/lib/pdf_simple.php')) {
    require_once __DIR__ . '/lib/pdf_simple.php';
} elseif (file_exists(__DIR__ . '/pdf_simple.php')) {
    require_once __DIR__ . '/pdf_simple.php';
} elseif (file_exists(__DIR__ . '/../lib/pdf_simple.php')) {
    require_once __DIR__ . '/../lib/pdf_simple.php';
} else {
    header('Content-Type: text/plain; charset=utf-8');
    die("Error crítico: no se encontró pdf_simple.php en lib/ ni en la raíz. Coloca pdf_simple.php o lib/pdf_simple.php en la carpeta del proyecto.");
}

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_POST['confirmar'])) {
    header('Location: carrito.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    die("El carrito está vacío. No hay nada que procesar.");
}

$cliente = $_SESSION['selected_user'] ?? null;
if (!$cliente || !is_array($cliente) || empty($cliente['id'])) {
    die("Error: no hay cliente seleccionado. Seleccione un usuario desde VENTAS.");
}

$admin_session = $_SESSION['usuario'] ?? null;
$admin = null;
if (!empty($admin_session)) {

    if (is_array($admin_session) && !empty($admin_session['id'])) {
        $admin = $admin_session;
    } else {
        $a = $pdo->prepare("SELECT id, usuario, nombre, apellido FROM admins WHERE usuario = ? LIMIT 1");
        $a->execute([$admin_session]);
        $admin = $a->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

$admin_id = $admin['id'] ?? null;
$vendido_por = $admin['usuario'] ?? 'admin';

$ticket_id = 'TKT' . time();

try {
    $pdo->beginTransaction();

    $total_acumulado = 0;
    foreach ($cart as $item) {
        $p = $item['producto'] ?? null;
        $cantidad = max(1, (int)($item['cantidad'] ?? 1));
        if (!$p) continue;

        $precio_unitario = floatval($p['precio'] ?? 0);
        $subtotal = $precio_unitario * $cantidad;
        $total_acumulado += $subtotal;
        $ins = $pdo->prepare("
            INSERT INTO movimientos
            (ticket_id, usuario_id, usuario_nombre, usuario_apellido, admin_id,
             zapato_id, zapato_modelo, cantidad, precio_unitario, subtotal, total_venta, fecha_movimiento)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW())
        ");

        $ins->execute([
            $ticket_id,
            $cliente['id'],
            $cliente['nombre'] ?? '',
            $cliente['apellido'] ?? '',
            $admin_id,
            $p['id'],
            $p['modelo'] ?? '',
            $cantidad,
            $precio_unitario,
            $subtotal,
            $total_acumulado
        ]);

    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    header('Content-Type: text/plain; charset=utf-8');
    die("Error al registrar movimientos en la BD: " . $e->getMessage());
}

$lines = [];
$lines[] = "      ZAPATERÍA BUCANEROS";
$lines[] = "";
$lines[] = "Ticket: " . $ticket_id;
$lines[] = "Fecha: " . date('Y-m-d H:i:s');
$lines[] = "";
$lines[] = "Cliente: " . trim(($cliente['nombre'] ?? '') . ' ' . ($cliente['apellido'] ?? ''));
if (!empty($cliente['correo'])) $lines[] = "Correo: " . $cliente['correo'];
$lines[] = "Atendido por: " . $vendido_por;
$lines[] = "";
$lines[] = str_repeat("-", 60);
$lines[] = sprintf("| %-10s | %-20s | %4s | %8s |", "Marca", "Producto", "Cant", "Subtotal");
$lines[] = str_repeat("-", 60);

$total_final = 0;
$total_items = 0;

foreach ($cart as $item) {
    $p = $item['producto'] ?? null;
    $cantidad = max(1, (int)($item['cantidad'] ?? 1));
    if (!$p) continue;


    $marca = $p['marca'] ?? $p['marca_nombre'] ?? ($p['marca_id'] ?? '');
    if (is_numeric($marca)) $marca = ''; 

    $modelo = $p['modelo'] ?? ($p['nombre'] ?? '');
    $precio_unitario = floatval($p['precio'] ?? 0);
    $subtotal = $precio_unitario * $cantidad;

    $lines[] = sprintf("| %-10s | %-20s | %4d | %8.2f |", substr($marca,0,10), substr($modelo,0,20), $cantidad, $subtotal);

    $total_final += $subtotal;
    $total_items += $cantidad;
}

$lines[] = str_repeat("-", 60);
$lines[] = "Total de productos: " . $total_items;
$lines[] = "TOTAL PAGADO: $" . number_format($total_final, 2);
$lines[] = "";
$lines[] = "Gracias por su compra :)";

unset($_SESSION['cart']);
unset($_SESSION['selected_user']);

$filename = 'factura_' . date('Ymd_His') . '.pdf';
pdf_simple_output($filename, $lines);

exit;
?>