<?php
// public/pedidos.php
session_start();
require_once __DIR__ . '/../includes/config/database.php';

// Solo usuarios logueados
if (!isset($_SESSION['usuario_id'])) {
    $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '/');
    header("Location: ../includes/auth/login.php?redirect={$redirect}");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$db = new Database();
$connection = $db->getConnection();


// Obtener pedidos del usuario
$sql = "SELECT p.*, b.id AS boleta_id, b.numero_boleta
        FROM pedidos p
        LEFT JOIN boletas b ON b.pedido_id = p.id
        WHERE p.usuario_id = ?
        ORDER BY p.fecha_pedido DESC";
$stmt = $connection->prepare($sql);
$stmt->execute([$usuario_id]);
$pedidos = $stmt->fetchAll();

function traducir_estado($estado) {
    switch ($estado) {
        case 'pendiente':   return 'Pendiente';
        case 'confirmado':  return 'Confirmado';
        case 'preparando':  return 'Preparando';
        case 'enviado':     return 'En camino';
        case 'entregado':   return 'Entregado';
        case 'cancelado':   return 'Cancelado';
        default:            return ucfirst($estado);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis pedidos - Florería</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <img src="../assets/images/logos/logo.png" alt="Logo" width="40">
      Florería
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav" aria-controls="navbarNav"
            aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
        <li class="nav-item"><a class="nav-link" href="carrito.php">Carrito</a></li>
        <li class="nav-item"><a class="nav-link active" href="pedidos.php">Mis pedidos</a></li>
        <?php if(($_SESSION['usuario_tipo'] ?? '') === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="../admin/dashboard.php">Panel Admin</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <h1>Mis pedidos</h1>

    <?php if (empty($pedidos)): ?>
        <div class="alert alert-info">
            Aún no has realizado ningún pedido.
        </div>
        <a href="index.php" class="btn btn-primary">Ir a comprar</a>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Método de pago</th>
                        <th>Boleta</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pedidos as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['codigo_pedido']) ?></td>
                        <td><?= htmlspecialchars($p['fecha_pedido']) ?></td>
                        <td>S/ <?= number_format($p['total'], 2) ?></td>
                        <td><?= traducir_estado($p['estado']) ?></td>
                        <td><?= htmlspecialchars($p['metodo_pago']) ?></td>
                        <td>
                            <?php if (!empty($p['boleta_id'])): ?>
                                <a href="../includes/pdf/generar_pdf.php?id=<?= $p['id'] ?>" 
                                class="btn btn-sm btn-outline-primary" target="_blank">
                                    Ver PDF
                                </a>
                            <?php else: ?>
                                <a href="../includes/pdf/generar_pdf.php?id=<?= $p['id'] ?>" 
                                class="btn btn-sm btn-primary" target="_blank">
                                    Generar PDF
                                </a>
                            <?php endif; ?>
                        </td>

                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
