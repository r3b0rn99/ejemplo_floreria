<?php
// admin/productos/editar.php
session_start();
require_once __DIR__ . '/../../includes/config/database.php';

// Solo admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header('Location: ../../includes/auth/login.php');
    exit;
}

// Validar ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("ID de producto no válido.");
}
$id = (int) $_GET['id'];

$db = new Database();
$connection = $db->getConnection();

$errores = [];

// Obtener producto actual
$sql = "SELECT * FROM productos WHERE id = ?";
$stmt = $connection->prepare($sql);
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    die("Producto no encontrado.");
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio      = (float) ($_POST['precio'] ?? 0);
    $imagen      = trim($_POST['imagen_principal'] ?? '');
    $destacado   = isset($_POST['destacado']) ? 1 : 0;
    $activo      = isset($_POST['activo']) ? 1 : 0;

    if ($nombre === '') {
        $errores[] = "El nombre es obligatorio.";
    }
    if ($precio <= 0) {
        $errores[] = "El precio debe ser mayor a cero.";
    }

    if (empty($errores)) {
        $sqlUpd = "UPDATE productos
                   SET nombre = ?, descripcion = ?, precio = ?, imagen_principal = ?, destacado = ?, activo = ?
                   WHERE id = ?";
        $stmtUpd = $connection->prepare($sqlUpd);
        $stmtUpd->execute([
            $nombre,
            $descripcion,
            $precio,
            $imagen,
            $destacado,
            $activo,
            $id
        ]);

        // Redirigir para evitar reenvío de formulario
        header('Location: listar.php?actualizado=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar producto - Admin</title>
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
    <a href="listar.php" class="btn btn-outline-light btn-sm">
      <i class="bi bi-arrow-left"></i> Volver a productos
    </a>
  </div>
</nav>

<div class="container mt-4" style="max-width: 700px;">
    <h1 class="h4 mb-3">Editar producto #<?= $producto['id']; ?></h1>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Nombre *</label>
            <input type="text" name="nombre" class="form-control"
                   value="<?= htmlspecialchars($_POST['nombre'] ?? $producto['nombre']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($_POST['descripcion'] ?? $producto['descripcion']); ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Precio (S/.) *</label>
            <input type="number" step="0.01" min="0" name="precio" class="form-control"
                   value="<?= htmlspecialchars($_POST['precio'] ?? $producto['precio']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Nombre de archivo de imagen principal</label>
            <input type="text" name="imagen_principal" class="form-control"
                   value="<?= htmlspecialchars($_POST['imagen_principal'] ?? $producto['imagen_principal']); ?>">
            <div class="form-text">
                Carpeta: <code>/ejemplo_floreria/assets/uploads/productos/</code>
            </div>
        </div>

        <div class="form-check mb-2">
            <input type="checkbox" name="destacado" id="destacado"
                   class="form-check-input"
                   <?= (($_POST['destacado'] ?? $producto['destacado']) ? 'checked' : ''); ?>>
            <label for="destacado" class="form-check-label">Producto destacado</label>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="activo" id="activo"
                   class="form-check-input"
                   <?= (($_POST['activo'] ?? $producto['activo']) ? 'checked' : ''); ?>>
            <label for="activo" class="form-check-label">Producto activo (visible en la tienda)</label>
        </div>

        <button class="btn btn-primary">
            <i class="bi bi-save"></i> Guardar cambios
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
