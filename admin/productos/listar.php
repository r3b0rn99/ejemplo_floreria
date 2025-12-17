<?php
// admin/productos/listar.php
session_start();
require_once __DIR__ . '/../../includes/config/database.php';

// Solo admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header('Location: ../../includes/auth/login.php');
    exit;
}

$db = new Database();
$connection = $db->getConnection();

$porPagina = 10; // puedes cambiarlo
$pagina = max(1, (int)($_GET['page'] ?? 1));
$offset = ($pagina - 1) * $porPagina;

// TOTAL SOLO ACTIVOS
$stmtTotal = $connection->prepare("SELECT COUNT(*) FROM productos WHERE activo = 1");
$stmtTotal->execute();
$totalRegistros = (int)$stmtTotal->fetchColumn();
$totalPaginas = max(1, (int)ceil($totalRegistros / $porPagina));

if ($pagina > $totalPaginas) {
    $pagina = $totalPaginas;
    $offset = ($pagina - 1) * $porPagina;
}

// LISTAR SOLO ACTIVOS
$sql = "SELECT id, nombre, precio, destacado, activo, imagen_principal
        FROM productos
        WHERE activo = 1
        ORDER BY id DESC
        LIMIT ? OFFSET ?";
$stmt = $connection->prepare($sql);
$stmt->bindValue(1, $porPagina, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos - Admin</title>
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

    <?php if (isset($_GET['creado']) && $_GET['creado'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Producto creado correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['actualizado']) && $_GET['actualizado'] == 1): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            Producto actualizado correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['eliminado']) && $_GET['eliminado'] == 1): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            Producto eliminado correctamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['desactivado']) && $_GET['desactivado'] == 1): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            Producto desactivado (no se puede eliminar porque está asociado a pedidos).
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Productos</h1>
        <a href="agregar.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Nuevo producto
        </a>
    </div>

    <?php if (empty($productos)): ?>
        <div class="alert alert-info">No hay productos activos registrados.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Destacado</th>
                        <th>Activo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><?= $p['id']; ?></td>
                        <td>
                            <?php
                            $imgDb = $p['imagen_principal'] ?? '';
                            $src = '';

                            if ($imgDb !== '') {
                                if (str_starts_with($imgDb, 'assets/')) {
                                    $src = '../../' . $imgDb;
                                } else {
                                    $src = '../../assets/uploads/productos/' . $imgDb;
                                }
                            }
                            ?>

                            <?php if ($src !== ''): ?>
                                <img src="<?= htmlspecialchars($src) ?>"
                                     style="width:60px;height:60px;object-fit:cover;border-radius:8px;">
                            <?php else: ?>
                                <span class="text-muted">Sin imagen</span>
                            <?php endif; ?>
                        </td>

                        <td><?= htmlspecialchars($p['nombre']); ?></td>
                        <td>S/ <?= number_format($p['precio'], 2); ?></td>
                        <td><?= $p['destacado'] ? 'Sí' : 'No'; ?></td>
                        <td><?= $p['activo'] ? 'Sí' : 'No'; ?></td>
                        <td class="text-end">
                            <a href="editar.php?id=<?= $p['id']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="eliminar.php?id=<?= $p['id']; ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('¿Seguro que deseas eliminar este producto?');">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($totalPaginas > 1): ?>
            <nav aria-label="Paginación productos" class="mt-3">
                <ul class="pagination justify-content-center">

                    <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="?<?= http_build_query(array_merge($_GET, ['page' => max(1, $pagina - 1)])) ?>">
                            Anterior
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                        <a class="page-link"
                           href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>

                    <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="?<?= http_build_query(array_merge($_GET, ['page' => min($totalPaginas, $pagina + 1)])) ?>">
                            Siguiente
                        </a>
                    </li>

                </ul>
            </nav>
            <?php endif; ?>

        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
