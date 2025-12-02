<?php
require 'config.php';
if(!isset($_SESSION['usuario'])) { header('Location: index.php'); exit; }

$marcas = [];
try {
    $mstmt = $pdo->query('SELECT id, nombre FROM marcas ORDER BY nombre ASC');
    $marcas = $mstmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $marcas = [];
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $marca_id = $_POST['marca'] ?? null;
    $modelo = $_POST['modelo'] ?? '';
    $talla  = $_POST['talla'] ?? '';
    $color  = $_POST['color'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $stock  = $_POST['stock'] ?? 0;

    if ($marca_id === null || $modelo === '') {
        $_SESSION['msg'] = 'Faltan datos obligatorios para guardar.';
        header('Location: zapatos.php'); exit;
    }

    $ins = $pdo->prepare('INSERT INTO zapatos (marca_id,modelo,talla,color,precio,stock) VALUES (?,?,?,?,?,?)');
    $ins->execute([$marca_id, $modelo, $talla, $color, $precio, $stock]);
    $_SESSION['msg']='Guardado exitosamente';
    header('Location: zapatos.php'); exit;
}


$edit = null;
if(isset($_GET['edit'])) {
    $id=(int)$_GET['edit'];
    $s = $pdo->prepare('SELECT * FROM zapatos WHERE id=? LIMIT 1');
    $s->execute([$id]);
    $edit = $s->fetch(PDO::FETCH_ASSOC);
    if(!$edit) { $_SESSION['msg']='Zapato no encontrado'; header('Location: zapatos.php'); exit; }
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id = (int)($_POST['id'] ?? 0);

    $marca_id = $_POST['marca'] ?? ($_POST['marca_id'] ?? null);
    $modelo   = $_POST['modelo'] ?? '';
    $talla    = $_POST['talla'] ?? '';
    $color    = $_POST['color'] ?? '';
    $precio   = $_POST['precio'] ?? 0;
    $stock    = $_POST['stock'] ?? 0;

    if ($id <= 0 || $marca_id === null || $modelo === '') {
        $_SESSION['msg'] = 'Faltan datos o id inválido.';
        header('Location: zapatos.php'); exit;
    }

    $up = $pdo->prepare('UPDATE zapatos SET marca_id=?, modelo=?, talla=?, color=?, precio=?, stock=? WHERE id=?');
    $up->execute([$marca_id, $modelo, $talla, $color, $precio, $stock, $id]);

    $_SESSION['msg']='Editado exitosamente';
    header('Location: zapatos.php'); exit;
}


if(isset($_GET['delete'])) {
    $id=(int)$_GET['delete'];
    $pdo->prepare('DELETE FROM zapatos WHERE id=?')->execute([$id]);
    $_SESSION['msg']='Eliminado exitosamente';
    header('Location: zapatos.php'); exit;
}

$where = "";
$params = [];

if (!empty($_GET['marca'])) {
    $busqueda = '%' . $_GET['marca'] . '%';
    $where = "WHERE m.nombre LIKE ?";
    $params[] = $busqueda;
}

$sql = "
    SELECT 
        z.id,
        z.marca_id,
        COALESCE(m.nombre, z.marca_id) AS marca,
        z.modelo,
        z.talla,
        z.color,
        z.precio,
        z.stock
    FROM zapatos z
    LEFT JOIN marcas m ON z.marca_id = m.id
    $where
    ORDER BY z.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$zapatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

require 'header.php';
?>

<h3>Gestión Zapatos</h3>

<?php if(isset($_SESSION['msg'])): ?>
<div class="alert alert-info"><?php echo s($_SESSION['msg']); unset($_SESSION['msg']); ?></div>
<?php endif; ?>

<form method="post" class="mb-3">
  <input type="hidden" name="id" value="<?php echo s($edit['id']??''); ?>">
  <div class="row g-2">

    <div class="col">
      <label class="form-label">Marca</label>
      <?php if(!empty($marcas)): ?>
        <select class="form-control" name="marca" required>
          <option value="">-- Seleccione --</option>
          <?php foreach($marcas as $m): ?>
            <option value="<?php echo s($m['id']); ?>" 
                <?php if(isset($edit['marca_id']) && $edit['marca_id']==$m['id']) echo 'selected'; ?>>
               <?php echo s($m['nombre']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      <?php else: ?>
        <input class="form-control" name="marca" placeholder="ID de marca" value="<?php echo s($edit['marca_id']??''); ?>" required>
      <?php endif; ?>
    </div>

    <div class="col"><label class="form-label">Modelo</label><input class="form-control" name="modelo" value="<?php echo s($edit['modelo']??''); ?>" required></div>
    <div class="col"><label class="form-label">Talla</label><input class="form-control" name="talla" value="<?php echo s($edit['talla']??''); ?>"></div>
    <div class="col"><label class="form-label">Color</label><input class="form-control" name="color" value="<?php echo s($edit['color']??''); ?>"></div>
    <div class="col"><label class="form-label">Precio</label><input class="form-control" name="precio" type="number" step="0.01" value="<?php echo s($edit['precio']??'0'); ?>"></div>
    <div class="col"><label class="form-label">Stock</label><input class="form-control" name="stock" type="number" value="<?php echo s($edit['stock']??'0'); ?>"></div>
  </div>

  <div class="mt-2">
    <?php if($edit): ?>
      <button class="btn btn-warning" name="actualizar">Actualizar</button>
      <a href="zapatos.php" class="btn btn-secondary">Cancelar</a>
    <?php else: ?>
      <button class="btn btn-primary" name="guardar">Guardar</button>
    <?php endif; ?>
  </div>
</form>

<form method="get" class="mb-3">
  <div class="input-group">
    <input name="marca" class="form-control" placeholder="Buscar por marca (nombre)" 
           value="<?php echo s($_GET['marca']??''); ?>">
    <button class="btn btn-outline-secondary">Buscar</button>
  </div>
</form>

<table class="table table-bordered">
<thead>
<tr>
  <th>ID</th>
  <th>Marca</th>
  <th>Modelo</th>
  <th>Talla</th>
  <th>Color</th>
  <th>Precio</th>
  <th>Stock</th>
  <th>Acciones</th>
</tr>
</thead>
<tbody>

<?php foreach($zapatos as $z): ?>
<tr>
  <td><?php echo s($z['id']); ?></td>
  <td><?php echo s($z['marca']); ?></td>
  <td><?php echo s($z['modelo']); ?></td>
  <td><?php echo s($z['talla']); ?></td>
  <td><?php echo s($z['color']); ?></td>
  <td>$<?php echo s($z['precio']); ?></td>
  <td><?php echo s($z['stock']); ?></td>
  <td>
    <a href="zapatos.php?edit=<?php echo $z['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
    <a href="zapatos.php?delete=<?php echo $z['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar?')">Eliminar</a>
  </td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

<?php require 'footer.php'; ?>