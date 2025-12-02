<?php
require 'config.php';
if(isset($_SESSION['usuario'])) { header('Location: ventas.php'); exit; }

$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    $sql = "SELECT * FROM admins WHERE usuario = ? AND password = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([$usuario, $password]);
        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['usuario'] = $row['usuario'];
            header('Location: ventas.php'); exit;
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    } catch (Exception $e) {
        $error = 'Error en la consulta: ' . $e->getMessage();
    }
}

require 'header.php';
?>
<div class="row justify-content-center">
  <div class="col-4">
    <div class="card p-4">
      <h3 class="text-center">LOGIN</h3>
      <?php if($error): ?><div class="alert alert-danger"><?php echo s($error); ?></div><?php endif; ?>
      <form method="post">
        <div class="mb-2"><label>Usuario</label><input name="usuario" class="form-control" required></div>
        <div class="mb-2"><label>Contraseña</label><input type="password" name="password" class="form-control" required></div>
        <button class="btn btn-primary">Ingresar</button>
      </form>
    </div>
  </div>
</div>
<?php require 'footer.php'; ?>