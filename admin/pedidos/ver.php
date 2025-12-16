<?php
// admin/pedidos/ver.php
session_start();
require_once __DIR__ . '/../../includes/config/database.php';

// Solo admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header('Location: ../../includes/auth/login.php');
    exit;
}

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("ID de pedido no válido.");
}
$pedido_id = (int) $_GET['id'];

$db = new Database();
$connection = $db->getConnection();

// Pedido + cliente
$sql = "SELECT p.*, u.nombre AS cliente_nombre, u.email, u.telefono
        FROM pedidos p
        JOIN usuarios u ON u.id = p.usuario_id
        WHERE p.id = ?";
$stmt = $connection->prepare($sql);
$stmt->execute([$pedido_id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    die("Pedido no encontrado.");
}

// Detalle
$sqlDet = "SELECT dp.*, pr.nombre AS producto_nombre
           FROM detalle_pedido dp
           JOIN productos pr ON pr.id = dp.producto_id
           WHERE dp.pedido_id = ?";
$stmtDet = $connection->prepare($sqlDet);
$stmtDet->execute([$pedido_id]);
$detalle = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle pedido <?= htmlspecialchars($pedido['codigo_pedido']); ?> - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="../dashboard.php">
      <i class="bi bi-flower1"></i> Admin Florería
    </a>
    <a href="gestionar.php" class="btn btn-outline-light btn-sm">
      <i class="bi bi-arrow-left"></i> Volver a pedidos
    </a>
  </div>
</nav>

<div class="container mt-4">
    <h1 class="h4 mb-3">Pedido <?= htmlspecialchars($pedido['codigo_pedido']); ?></h1>

    <div class="row mb-4">
        <div class="col-md-6">
            <h6>Datos del cliente</h6>
            <p class="mb-1"><strong>Nombre:</strong> <?= htmlspecialchars($pedido['cliente_nombre']); ?></p>
            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($pedido['email']); ?></p>
            <p class="mb-1"><strong>Teléfono:</strong> <?= htmlspecialchars($pedido['telefono'] ?? ''); ?></p>
        </div>
        <div class="col-md-6">
            <h6>Datos del pedido</h6>
            <p class="mb-1"><strong>Fecha:</strong> <?= htmlspecialchars($pedido['fecha_pedido']); ?></p>
            <p class="mb-1"><strong>Estado:</strong> <?= htmlspecialchars($pedido['estado']); ?></p>
            <p class="mb-1"><strong>Total:</strong> S/ <?= number_format($pedido['total'], 2); ?></p>
            <?php if (!empty($pedido['direccion_envio'])): ?>
                <p class="mb-1"><strong>Dirección de envío:</strong> <?= htmlspecialchars($pedido['direccion_envio']); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <h5>Detalle de productos</h5>
    <?php if (empty($detalle)): ?>
        <p class="text-muted">Este pedido no tiene detalle registrado.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalle as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['producto_nombre']); ?></td>
                        <td><?= (int)$d['cantidad']; ?></td>
                        <td>S/ <?= number_format($d['precio_unitario'], 2); ?></td>
                        <td>S/ <?= number_format($d['subtotal'], 2); ?></td>
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
