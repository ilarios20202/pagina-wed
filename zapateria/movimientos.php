<?php
require 'config.php';
require 'lib/pdf_simple.php';
if(!isset($_SESSION['usuario'])) { header('Location: index.php'); exit; }

$stmt = $pdo->query("SELECT ticket_id, MIN(fecha_movimiento) AS fecha, MIN(usuario_nombre) AS usuario_nombre, MIN(usuario_apellido) AS usuario_apellido, SUM(cantidad) AS total_items, MAX(total_venta) AS total_venta
                     FROM movimientos
                     GROUP BY ticket_id
                     ORDER BY MAX(id) DESC");
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (isset($_GET['download']) && (isset($_GET['ticket']) || isset($_GET['id']))) {

    $ticket = null;
    if (!empty($_GET['ticket'])) {
        $ticket = $_GET['ticket'];
    } else {
        $id = (int)$_GET['id'];
        $s = $pdo->prepare("SELECT ticket_id FROM movimientos WHERE id = ? LIMIT 1");
        $s->execute([$id]);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        if ($row) $ticket = $row['ticket_id'];
    }

    if (!$ticket) {
        die("Ticket no encontrado.");
    }

    $q = $pdo->prepare("SELECT zapato_modelo, cantidad, precio_unitario, subtotal, total_venta, fecha_movimiento, usuario_nombre, usuario_apellido
                        FROM movimientos WHERE ticket_id = ? ORDER BY id ASC");
    $q->execute([$ticket]);
    $items = $q->fetchAll(PDO::FETCH_ASSOC);

    if (!$items) {
        die("No se encontraron items para ese ticket.");
    }

    
    $cliente = trim(($items[0]['usuario_nombre'] ?? '') . ' ' . ($items[0]['usuario_apellido'] ?? ''));
    $fecha = $items[0]['fecha_movimiento'] ?? '';
    $total_final = $items[count($items)-1]['total_venta'] ?? 0;

    
    $lines = [];
    $lines[] = "      ZAPATERÍA BUCANEROS";
    $lines[] = "";
    $lines[] = "Ticket: " . $ticket;
    $lines[] = "Fecha: " . $fecha;
    $lines[] = "";
    $lines[] = "Cliente:";
    $lines[] = $cliente;
    $lines[] = "";
    $lines[] = "Atendido por:";
    $lines[] = $_SESSION['usuario']; 
    $lines[] = "";
    $lines[] = str_repeat("-", 60);
    $lines[] = sprintf("| %-10s | %-20s | %4s | %8s | %9s |", "Marca", "Producto", "Cant", "P. Unit", "Subtotal");
    $lines[] = str_repeat("-", 60);

    foreach ($items as $it) {
        $modelo = $it['zapato_modelo'] ?? '';
        $cantidad = $it['cantidad'] ?? 1;
        $pu = number_format($it['precio_unitario'] ?? 0, 2);
        $sub = number_format($it['subtotal'] ?? 0, 2);
ca
        $marca = ""; 

        $lines[] = sprintf("| %-10s | %-20s | %4s | %8s | %9s |", $marca, substr($modelo,0,20), $cantidad, $pu, $sub);
    }

    $lines[] = str_repeat("-", 60);
    $lines[] = "Total de productos: " . array_reduce($items, function($carry,$i){return $carry + ($i['cantidad'] ?? 0);}, 0);
    $lines[] = "TOTAL PAGADO: $" . number_format($total_final, 2);
    $lines[] = "";
    $lines[] = "Gracias por su compra :)";

    pdf_simple_output('factura_'.$ticket.'.pdf', $lines);
}

require 'header.php';
?>
<h3>Historial de Movimientos (Tickets)</h3>

<table class="table table-bordered">
    <thead>
        <tr><th>Fecha</th><th>Ticket</th><th>Cliente</th><th>Items</th><th>Total</th><th>Acciones</th></tr>
    </thead>
    <tbody>
    <?php foreach($tickets as $t): ?>
        <tr>
            <td><?= s($t['fecha']) ?></td>
            <td><?= s($t['ticket_id']) ?></td>
            <td><?= s(trim(($t['usuario_nombre'] ?? '') . ' ' . ($t['usuario_apellido'] ?? ''))) ?></td>
            <td><?= s($t['total_items']) ?></td>
            <td><?= number_format($t['total_venta'] ?? 0, 2) ?></td>
            <td><a class="btn btn-sm btn-primary" href="movimientos.php?download=1&amp;ticket=<?= urlencode($t['ticket_id']) ?>">Descargar</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require 'footer.php'; ?>