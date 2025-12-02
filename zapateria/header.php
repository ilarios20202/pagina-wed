<?php

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Zapatería</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
  .bg-celeste {
    background: #cfeefc;
  }

  .menu-fixed {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    background: #cfeefc;
    height: 70px; 
  }

  body {
    margin: 0;
    padding-top: 70px; 
  }
</style>

</head>
<body>
<?php if(isset($_SESSION['usuario'])): ?>
<nav class="navbar navbar-expand-lg navbar-light bg-celeste menu-fixed">
  <div class="container-fluid">
    <a class="navbar-brand" href="ventas.php">Zapatería</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="ventas.php">Ventas</a></li>
        <li class="nav-item"><a class="nav-link" href="usuarios.php">Gestión Usuarios</a></li>
        <li class="nav-item"><a class="nav-link" href="zapatos.php">Gestión Zapatos</a></li>
        <li class="nav-item"><a class="nav-link" href="movimientos.php">Movimientos</a></li>
      </ul>
      <div class="d-flex">
        <span class="me-3">Usuario: <?php echo s($_SESSION['usuario']); ?></span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Cerrar sesión</a>
      </div>
    </div>
  </div>
</nav>
<?php endif; ?>
<div class="container mt-4">