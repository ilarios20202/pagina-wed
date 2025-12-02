<?php
require 'config.php';
if (!isset($_SESSION['usuario'])) { header('Location: index.php'); exit; }


if (isset($_POST['eliminar'])) {
    $index = (int)$_POST['index'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); 
    }
    header("Location: carrito.php");
    exit;
}


if (isset($_POST['update_qty'])) {
    $index = (int)$_POST['index'];
    $cantidad = max(1, (int)$_POST['cantidad']);
    if (isset($_SESSION['cart'][$index])) {
        $_SESSION['cart'][$index]['cantidad'] = $cantidad;
    }
    header("Location: carrito.php");
    exit;
}


$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

require 'header.php';
?>

<h3>CARRITO DE COMPRA</h3>

<?php if (empty($cart)): ?>
    <div class="alert alert-info">El carrito está vacío.</div>
<?php else: ?>

<table class="table table-bordered table-striped">
<thead class="table-light">
<tr>
    <th>#</th>
    <th>Cant.</th>
    <th>Marca</th>
    <th>Modelo</th>
    <th>Talla</th>
    <th>Color</th>
    <th>Precio</th>
    <th>Subtotal</th>
    <th>Acción</th>
</tr>
</thead>
<tbody>

<?php
$total = 0;
$i = 1;

foreach ($cart as $index => $item):
    $p = $item['producto'];


    if (!isset($p['marca'])) {
        $st = $pdo->prepare("
            SELECT 
                z.id,
                m.nombre AS marca,
                z.modelo,
                z.talla,
                z.color,
                z.precio
            FROM zapatos z
            INNER JOIN marcas m ON m.id = z.marca_id
            WHERE z.id = ? LIMIT 1
        ");
        $st->execute([$p['id']]);
        $nuevo = $st->fetch(PDO::FETCH_ASSOC);
        if ($nuevo) {
            $_SESSION['cart'][$index]['producto'] = $nuevo;
            $p = $nuevo;
        }
    }

    $cantidad = $item['cantidad'] ?? 1;
    $subtotal = $p['precio'] * $cantidad;
    $total += $subtotal;
?>

<tr>
    <td><?= $i++; ?></td>

    <td>
        <form method="post" style="width:80px;">
            <input type="hidden" name="index" value="<?= $index ?>">
            <input type="number"
                   name="cantidad"
                   class="form-control"
                   value="<?= $cantidad ?>"
                   min="1">
            <button class="btn btn-sm btn-primary mt-1" name="update_qty">OK</button>
        </form>
    </td>

    <td><?= s($p['marca']); ?></td>
    <td><?= s($p['modelo']); ?></td>
    <td><?= s($p['talla']); ?></td>
    <td><?= s($p['color']); ?></td>
    <td><?= number_format($p['precio'], 2); ?></td>
    <td><?= number_format($subtotal, 2); ?></td>

    <td>
        <form method="post">
            <input type="hidden" name="index" value="<?= $index ?>">
            <button class="btn btn-danger btn-sm" name="eliminar">Eliminar</button>
        </form>
    </td>
</tr>

<?php endforeach; ?>

</tbody>
</table>

<div class="alert alert-success">
    <h4>Total: $<?= number_format($total, 2); ?></h4>
</div>


<div class="d-flex gap-3">


    <a href="ventas.php" class="btn btn-secondary btn-lg">Seguir comprando</a>


    <form method="post" action="procesar_venta.php">
        <button class="btn btn-primary btn-lg" name="confirmar">Confirmar compra</button>
    </form>

</div>

<?php endif; ?>

<?php require 'footer.php'; ?>