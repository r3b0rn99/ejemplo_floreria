<?php
// admin/pedidos/gestionar.php
session_start();
require_once __DIR__ . '/../../includes/config/database.php';

// Solo admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header('Location: ../../includes/auth/login.php');
    exit;
}

$db = new Database();
$connection = $db->getConnection();

/* =======================
   1. CAMBIAR ESTADO (POST)
   ======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['pedido_id'], $_POST['nuevo_estado'])) {

    $pedido_id    = (int) $_POST['pedido_id'];
    $nuevo_estado = $_POST['nuevo_estado'];

    // Seguridad básica: limitar a estados válidos
    $estados_validos = ['pendiente','confirmado','preparando','enviado','entregado','cancelado'];
    if (in_array($nuevo_estado, $estados_validos, true)) {
        $sqlUpd = "UPDATE pedidos SET estado = ? WHERE id = ?";
        $stmtUpd = $connection->prepare($sqlUpd);
        $stmtUpd->execute([$nuevo_estado, $pedido_id]);
    }

    // Redirigir para evitar reenvío de formulario
    header('Location: gestionar.php?actualizado=1');
    exit;
}

/* =======================
   2. LISTAR PEDIDOS
   ======================= */
$sql = "SELECT p.id, p.codigo_pedido, p.fecha_pedido, p.total, p.estado,
               u.nombre AS cliente, u.email
        FROM pedidos p
        JOIN usuarios u ON u.id = p.usuario_id
        ORDER BY p.fecha_pedido DESC";
$stmt = $connection->prepare($sql);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

function badgeEstado($estado) {
    switch ($estado) {
        case 'pendiente':   return 'warning';
        case 'confirmado':  return 'info';
        case 'preparando':  return 'primary';
        case 'enviado':     return 'secondary';
        case 'entregado':   return 'success';
        case 'cancelado':   return 'danger';
        default:            return 'light';
    }
}
?>
<!DOCTYPE html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar pedidos - Admin</title>
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
    <h1 class="h4 mb-3">Gestionar pedidos</h1>

    <?php if (isset($_GET['actualizado']) && $_GET['actualizado'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Estado del pedido actualizado correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($pedidos)): ?>
        <div class="alert alert-info">Todavía no hay pedidos registrados.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Cambiar estado</th>
                        <th>Ver</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['codigo_pedido']); ?></td>
                        <td>
                            <?= htmlspecialchars($p['cliente']); ?><br>
                            <small class="text-muted"><?= htmlspecialchars($p['email']); ?></small>
                        </td>
                        <td><?= htmlspecialchars($p['fecha_pedido']); ?></td>
                        <td>S/ <?= number_format($p['total'], 2); ?></td>
                        <td>
                            <span class="badge bg-<?= badgeEstado($p['estado']); ?>">
                                <?= ucfirst($p['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <form method="post" class="d-flex">
                                <input type="hidden" name="pedido_id" value="<?= $p['id']; ?>">
                                <select name="nuevo_estado" class="form-select form-select-sm me-2">
                                    <?php
                                    $estados = ['pendiente','confirmado','preparando','enviado','entregado','cancelado'];
                                    foreach ($estados as $est):
                                    ?>
                                        <option value="<?= $est; ?>" <?= $est === $p['estado'] ? 'selected' : ''; ?>>
                                            <?= ucfirst($est); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-arrow-down-square"></i> <!-- o bi-save si quieres -->
                                </button>
                            </form>
                        </td>
                        <td>
                            <a href="ver.php?id=<?= $p['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye"></i> Detalle
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
