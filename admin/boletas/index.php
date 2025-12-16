<?php
// admin/boletas/index.php
session_start();
require_once __DIR__ . '/../../includes/config/database.php';

// Solo admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header('Location: ../../includes/auth/login.php');
    exit;
}

$db = new Database();
$connection = $db->getConnection();

$sql = "SELECT b.*, p.codigo_pedido, p.fecha_pedido, u.nombre AS cliente
        FROM boletas b
        JOIN pedidos p ON p.id = b.pedido_id
        JOIN usuarios u ON u.id = p.usuario_id
        ORDER BY b.id DESC";
$stmt = $connection->prepare($sql);
$stmt->execute();
$boletas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boletas - Admin</title>
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
    <a href="../dashboard.php" class="btn btn-outline-light btn-sm">
      <i class="bi bi-arrow-left"></i> Volver al dashboard
    </a>
  </div>
</nav>

<div class="container mt-4">
    <h1 class="h4 mb-3">Boletas generadas</h1>

    <?php if (empty($boletas)): ?>
        <div class="alert alert-info">Aún no se han generado boletas.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>N° boleta</th>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Fecha pedido</th>
                        <th>PDF</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($boletas as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['numero_boleta']); ?></td>
                        <td><?= htmlspecialchars($b['codigo_pedido']); ?></td>
                        <td><?= htmlspecialchars($b['cliente']); ?></td>
                        <td><?= htmlspecialchars($b['fecha_pedido']); ?></td>
                        <td>
                            <a href="../../includes/pdf/generar_pdf.php?id=<?= (int)$b['pedido_id'] ?>"
                               target="_blank" class="btn btn-sm btn-outline-primary">
                               <i class="bi bi-file-earmark-pdf"></i> Ver PDF
                            </a>
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
