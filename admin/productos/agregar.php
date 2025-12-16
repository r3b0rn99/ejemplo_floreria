<?php
// admin/productos/agregar.php
session_start();
require_once __DIR__ . '/../../includes/config/database.php';

// Solo admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header('Location: ../../includes/auth/login.php');
    exit;
}

$db = new Database();
$connection = $db->getConnection();

$errores = [];

function subirImagenProducto(string $campoFile): ?string
{
    if (!isset($_FILES[$campoFile]) || $_FILES[$campoFile]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$campoFile]['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Error al subir imagen.");
    }

    if ($_FILES[$campoFile]['size'] > 2_000_000) {
        throw new Exception("La imagen supera 2MB.");
    }

    $tmp = $_FILES[$campoFile]['tmp_name'];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);

    $permitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($permitidos[$mime])) {
        throw new Exception("Formato no permitido. Solo JPG, PNG o WEBP.");
    }

    $ext = $permitidos[$mime];
    $nombre = 'prod_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;

    $carpetaRel = 'assets/uploads/productos/';
    $carpetaAbs = __DIR__ . '/../../' . $carpetaRel;

    if (!is_dir($carpetaAbs)) {
        mkdir($carpetaAbs, 0775, true);
    }

    if (!move_uploaded_file($tmp, $carpetaAbs . $nombre)) {
        throw new Exception("No se pudo guardar la imagen.");
    }

    return $carpetaRel . $nombre; // esto se guarda en BD
}





if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio      = (float) ($_POST['precio'] ?? 0);
    try {
    $imagen = subirImagenProducto('imagen_principal'); // aquí se sube y devuelve la ruta
    if ($imagen === null) {
        $errores[] = "Debes subir una imagen principal.";
    }
} catch (Exception $e) {
    $errores[] = $e->getMessage();
}

    $destacado   = isset($_POST['destacado']) ? 1 : 0;

    if ($nombre === '') {
        $errores[] = "El nombre es obligatorio.";
    }
    if ($precio <= 0) {
        $errores[] = "El precio debe ser mayor a cero.";
    }

    if (empty($errores)) {
        $sql = "INSERT INTO productos (nombre, descripcion, precio, imagen_principal, destacado, activo)
                VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $connection->prepare($sql);
        $stmt->execute([
            $nombre,
            $descripcion,
            $precio,
            $imagen,
            $destacado
        ]);

        // 🟢 PRG: Redirigir para evitar reenvío de formulario
        header('Location: listar.php?creado=1');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar producto - Admin</title>
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
    <h1 class="h4 mb-3">Nuevo producto</h1>
    

    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Nombre *</label>
            <input type="text" name="nombre" class="form-control"
                   value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Precio (S/.) *</label>
            <input type="number" step="0.01" min="0" name="precio" class="form-control"
                   value="<?= htmlspecialchars($_POST['precio'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Imagen principal *</label>
            <input type="file" name="imagen_principal" class="form-control" accept="image/*" required>
            <div class="form-text">
                La imagen debe ser JPG, PNG o WEBP y no debe superar 2MB.
            </div>
        </div>


        <div class="form-check mb-3">
            <input type="checkbox" name="destacado" id="destacado"
                   class="form-check-input"
                   <?= isset($_POST['destacado']) ? 'checked' : '' ?>>
            <label for="destacado" class="form-check-label">Mostrar como producto destacado</label>
        </div>

        <button class="btn btn-success">
            <i class="bi bi-save"></i> Guardar producto
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
