<?php
require 'config.php';
if(!isset($_SESSION['usuario'])) { header('Location: index.php'); exit; }

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $fecha = $_POST['fecha_creado'] ?? '';
    $ins = $pdo->prepare('INSERT INTO usuarios (nombre,apellido,correo,fecha_creado) VALUES (?,?,?,?)');
    $ins->execute([$nombre,$apellido,$correo,$fecha]);
    $_SESSION['msg']='Guardado exitosamente';
    header('Location: usuarios.php'); exit;
}

$edit = null;
if(isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $s = $pdo->prepare('SELECT * FROM usuarios WHERE id=? LIMIT 1'); $s->execute([$id]); $edit = $s->fetch(PDO::FETCH_ASSOC);
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $id = (int)$_POST['id'];
    $up = $pdo->prepare('UPDATE usuarios SET nombre=?,apellido=?,correo=?,fecha_creado=? WHERE id=?');
    $up->execute([$_POST['nombre'],$_POST['apellido'],$_POST['correo'],$_POST['fecha_creado'],$id]);
    $_SESSION['msg']='Editado exitosamente';
    header('Location: usuarios.php'); exit;
}

if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM usuarios WHERE id=?')->execute([$id]);
    $_SESSION['msg']='Eliminado exitosamente';
    header('Location: usuarios.php'); exit;
}

$where=''; $params=[];
if(!empty($_GET['buscar_correo'])) { $where=' WHERE correo LIKE ?'; $params[]='%'.$_GET['buscar_correo'].'%'; }
$stmt = $pdo->prepare('SELECT id,nombre,apellido,correo,fecha_creado FROM usuarios'.$where.' ORDER BY id DESC');
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

require 'header.php';
?>
<h3>Agregar Usuario</h3>
<?php if(isset($_SESSION['msg'])): ?><div class="alert alert-info"><?php echo s($_SESSION['msg']); unset($_SESSION['msg']); ?></div><?php endif; ?>

<form method="post" class="mb-3">
  <input type="hidden" name="id" value="<?php echo s($edit['id']??''); ?>">
  <div class="row">
    <div class="col"><input class="form-control" name="nombre" placeholder="Nombre" value="<?php echo s($edit['nombre']??''); ?>" required></div>
    <div class="col"><input class="form-control" name="apellido" placeholder="Apellido" value="<?php echo s($edit['apellido']??''); ?>" required></div>
    <div class="col"><input class="form-control" name="correo" placeholder="Correo" type="email" value="<?php echo s($edit['correo']??''); ?>" required></div>
    <div class="col"><input class="form-control" name="fecha_creado" type="datetime-local" value="<?php echo isset($edit['fecha_creado'])?date('Y-m-d\TH:i', strtotime($edit['fecha_creado'])):''; ?>" required></div>
  </div>
  <div class="mt-2">
    <?php if($edit): ?>
      <button class="btn btn-warning" name="actualizar">Actualizar</button>
      <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
    <?php else: ?>
      <button class="btn btn-primary" name="guardar">Guardar</button>
    <?php endif; ?>
  </div>
</form>

<form method="get" class="mb-3">
  <div class="input-group">
    <input name="buscar_correo" class="form-control" placeholder="Buscar por correo" value="<?php echo s($_GET['buscar_correo']??''); ?>">
    <button class="btn btn-outline-secondary">Buscar</button>
  </div>
</form>

<table class="table table-bordered">
<thead><tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Correo</th><th>Fecha creado</th><th>Acciones</th></tr></thead>
<tbody>
<?php foreach($usuarios as $u): ?>
<tr>
  <td><?php echo s($u['id']); ?></td>
  <td><?php echo s($u['nombre']); ?></td>
  <td><?php echo s($u['apellido']); ?></td>
  <td><?php echo s($u['correo']); ?></td>
  <td><?php echo s($u['fecha_creado']); ?></td>
  <td>
    <a href="usuarios.php?edit=<?php echo $u['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
    <a href="usuarios.php?delete=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar?')">Eliminar</a>
  </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php require 'footer.php'; ?>