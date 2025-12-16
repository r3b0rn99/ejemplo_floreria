<?php
// public/perfil.php
session_start();
require_once __DIR__ . '/../includes/config/database.php';

// Bloquear acceso si no está logueado
if (!isset($_SESSION['usuario_id'])) {
    $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '/');
    header("Location: ../includes/auth/login.php?redirect={$redirect}");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$errores = [];
$exito = false;

// Conexión usando tu clase Database
$db = new Database();
$connection = $db->getConnection();

// Obtener datos del usuario
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $connection->prepare($sql);
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuario no encontrado.");
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre     = trim($_POST['nombre'] ?? '');
    $telefono   = trim($_POST['telefono'] ?? '');
    $direccion  = trim($_POST['direccion'] ?? '');

    if ($nombre === '') {
        $errores[] = "El nombre no puede estar vacío.";
    }

    if (empty($errores)) {
        try {
            $sqlUpd = "UPDATE usuarios
                       SET nombre = ?, telefono = ?, direccion = ?
                       WHERE id = ?";
            $stmtUpd = $connection->prepare($sqlUpd);
            $ok = $stmtUpd->execute([$nombre, $telefono, $direccion, $usuario_id]);

            if ($ok) {
                $exito = true;

                // Actualizar sesión visible (opcional)
                $_SESSION['usuario_nombre'] = $nombre;

                // refrescar los datos desde BD
                $stmt = $connection->prepare("SELECT * FROM usuarios WHERE id = ?");
                $stmt->execute([$usuario_id]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $info = $stmtUpd->errorInfo();
                $errores[] = "No se pudo actualizar. Error SQL: " . ($info[2] ?? 'desconocido');
            }

        } catch (Exception $e) {
            $errores[] = "Error al actualizar: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi perfil - Florería</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <img src="../assets/images/logos/logo.png" width="40" alt="Logo">
      Florería
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#menuPerfil" aria-expanded="false">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menuPerfil">
      <ul class="navbar-nav ms-auto">

        <li class="nav-item"><a class="nav-link" href="carrito.php">Carrito</a></li>
        <li class="nav-item"><a class="nav-link" href="pedidos.php">Mis pedidos</a></li>
        <li class="nav-item"><a class="nav-link active" href="perfil.php">Mi perfil</a></li>
        <?php if(($_SESSION['usuario_tipo'] ?? '') === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="../admin/dashboard.php">Panel Admin</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link text-danger" href="../includes/auth/logout.php">Cerrar sesión</a></li>

      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4" style="max-width: 600px;">

    <h2 class="mb-4">Mi perfil</h2>

    <?php if ($exito): ?>
        <div class="alert alert-success">Datos actualizados correctamente.</div>
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errores as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="card card-body">

        <div class="mb-3">
            <label class="form-label">Nombre completo</label>
            <input type="text" name="nombre" class="form-control"
                   value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Correo electrónico (no editable)</label>
            <input type="email" class="form-control"
                   value="<?= htmlspecialchars($usuario['email']) ?>" disabled>
        </div>

        <div class="mb-3">
            <label class="form-label">Teléfono</label>
            <input type="text" name="telefono" class="form-control"
                   value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Dirección</label>
            <textarea name="direccion" class="form-control" rows="3"><?= htmlspecialchars($usuario['direccion'] ?? '') ?></textarea>
        </div>

        <button class="btn btn-primary w-100">Actualizar perfil</button>

    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
