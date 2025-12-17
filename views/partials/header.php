<?php
// views/partials/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Florería Bella') ?></title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link href="<?= htmlspecialchars($basePath ?? '../') ?>assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-success">
  <div class="container">
    <a class="navbar-brand text-white" href="<?= htmlspecialchars($basePath ?? '../') ?>public/index.php">
      <i class="bi bi-flower1"></i> Florería Bella
    </a>

    <div class="d-flex">
      <a href="<?= htmlspecialchars($basePath ?? '../') ?>public/carrito.php" class="btn btn-light position-relative me-3">
        <i class="bi bi-cart"></i> Carrito
      </a>

      <?php if(isset($_SESSION['usuario_id'])): ?>
        <div class="dropdown">
          <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario'); ?>
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?= htmlspecialchars($basePath ?? '../') ?>public/perfil.php">Mi Perfil</a></li>
            <li><a class="dropdown-item" href="<?= htmlspecialchars($basePath ?? '../') ?>public/pedidos.php">Mis Pedidos</a></li>

            <?php if(($_SESSION['usuario_tipo'] ?? '') === 'admin'): ?>
              <li><a class="dropdown-item" href="<?= htmlspecialchars($basePath ?? '../') ?>admin/dashboard.php">Panel del Administrador</a></li>
            <?php endif; ?>

            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= htmlspecialchars($basePath ?? '../') ?>includes/auth/logout.php">Cerrar Sesión</a></li>
          </ul>
        </div>
      <?php else: ?>
        <a href="<?= htmlspecialchars($basePath ?? '../') ?>includes/auth/login.php" class="btn btn-light">Iniciar sesión</a>
      <?php endif; ?>
    </div>

  </div>
</nav>
