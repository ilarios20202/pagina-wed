<?php
require 'config.php';
if (!isset($_SESSION['usuario'])) { header('Location: index.php'); exit; }
require 'lib/pdf_simple.php';

$selected_user = $_SESSION['temp_user'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_user'])) {

    $st = $pdo->prepare("SELECT id,nombre,apellido,correo FROM usuarios WHERE id=? LIMIT 1");
    $st->execute([$_POST['user_id']]);
    $user = $st->fetch(PDO::FETCH_ASSOC);

    $_SESSION['selected_user'] = $user;
    unset($_SESSION['temp_user']); 

    $_SESSION['msg'] = "Usuario seleccionado correctamente";
    header("Location: ventas.php");
    exit;
}

if (isset($_POST['deselect_user'])) {
    unset($_SESSION['selected_user']);
    unset($_SESSION['temp_user']);

    $_SESSION['msg'] = "Usuario deseleccionado";
    header("Location: ventas.php");
    exit;
}


if (!empty($_GET['correo'])) {

    $correo = trim($_GET['correo']);

    $st = $pdo->prepare("
        SELECT id,nombre,apellido,correo 
        FROM usuarios 
        WHERE correo LIKE ? LIMIT 1
    ");
    $st->execute(["%$correo%"]);
    $found = $st->fetch(PDO::FETCH_ASSOC);

    if ($found) {
        $_SESSION['temp_user'] = $found; 
    } else {
        $_SESSION['msg'] = "No se encontró ningún usuario con ese correo";
    }

    header("Location: ventas.php");
    exit;
}

$prod_stmt = $pdo->query("
    SELECT 
        z.id, 
        m.nombre AS marca, 
        z.modelo, 
        z.talla, 
        z.color, 
        z.precio, 
        z.stock
    FROM zapatos z
    INNER JOIN marcas m ON m.id = z.marca_id
");
$productos = $prod_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {

    if (!isset($_SESSION['selected_user'])) {
        $_SESSION['msg'] = "Debe seleccionar un usuario antes de agregar productos";
        header('Location: ventas.php');
        exit;
    }

    $prod_id = (int)$_POST['prod_id'];

    $pstm = $pdo->prepare("
        SELECT 
            z.id, 
            m.nombre AS marca, 
            z.modelo, 
            z.talla, 
            z.color, 
            z.precio
        FROM zapatos z
        INNER JOIN marcas m ON m.id = z.marca_id
        WHERE z.id=? LIMIT 1
    ");

    $pstm->execute([$prod_id]);
    $producto = $pstm->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        $_SESSION['msg'] = "Producto no encontrado";
        header("Location: ventas.php");
        exit;
    }

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    $_SESSION['cart'][] = [
        "user" => $_SESSION['selected_user'],
        "producto" => $producto
    ];

    $_SESSION['msg'] = "Producto agregado al carrito";
    header("Location: carrito.php");
    exit;
}

require 'header.php';
?>

<h3>GESTOR DE VENTAS</h3>

<?php if (isset($_SESSION['msg'])): ?>
    <div class="alert alert-info"><?= s($_SESSION['msg']); unset($_SESSION['msg']); ?></div>
<?php endif; ?>


<?php if (!empty($_SESSION['selected_user'])):
    $su = $_SESSION['selected_user']; ?>
    <div class="alert alert-success d-flex justify-content-between align-items-center">
        <div>
            Usuario seleccionado:
            <strong><?= s($su['nombre'] . " " . $su['apellido']); ?></strong>
            (<?= s($su['correo']); ?>)
        </div>

        <form method="post">
            <input type="hidden" name="deselect_user" value="1">
            <button class="btn btn-outline-danger btn-sm">Deseleccionar</button>
        </form>
    </div>
<?php endif; ?>


<form class="row g-2 mb-3" method="get">
    <div class="col-auto">
        <input type="email" name="correo" class="form-control" placeholder="Buscar por correo">
    </div>
    <div class="col-auto">
        <button class="btn btn-primary">Buscar</button>
    </div>
</form>


<?php if (!empty($_SESSION['temp_user'])): 
    $u = $_SESSION['temp_user']; ?>
    <div class="card mb-3">
        <div class="card-body">
            <strong>Usuario encontrado:</strong>

            <table class="table table-sm">
                <tr>
                    <td><?= s($u['nombre']); ?></td>
                    <td><?= s($u['apellido']); ?></td>
                    <td><?= s($u['correo']); ?></td>
                </tr>
            </table>

            <form method="post">
                <input type="hidden" name="select_user" value="1">
                <input type="hidden" name="user_id" value="<?= s($u['id']); ?>">
                <button class="btn btn-success">Seleccionar usuario</button>
            </form>
        </div>
    </div>
<?php endif; ?>


<table class="table table-bordered table-striped">
<thead class="table-light">
<tr>
    <th>ID</th>
    <th>Marca</th>
    <th>Modelo</th>
    <th>Talla</th>
    <th>Color</th>
    <th>Precio</th>
    <th>Stock</th>
    <th>Acción</th>
</tr>
</thead>
<tbody>
<?php foreach ($productos as $p): ?>
<tr>
    <td><?= s($p['id']); ?></td>
    <td><?= s($p['marca']); ?></td>
    <td><?= s($p['modelo']); ?></td>
    <td><?= s($p['talla']); ?></td>
    <td><?= s($p['color']); ?></td>
    <td><?= s($p['precio']); ?></td>
    <td><?= s($p['stock']); ?></td>

    <td>
        <form method="post">
            <input type="hidden" name="prod_id" value="<?= s($p['id']); ?>">
            <button class="btn btn-sm btn-success" name="agregar">Agregar</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<?php require 'footer.php'; ?>