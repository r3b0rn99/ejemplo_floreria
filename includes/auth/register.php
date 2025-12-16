<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$errores = [];
$exito = false;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre   = trim($_POST['nombre'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Validaciones
    if ($nombre === '') $errores[] = "El nombre es obligatorio.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = "El correo no es válido.";
    if (strlen($password) < 6) $errores[] = "La contraseña debe tener al menos 6 caracteres.";
    if ($password !== $password2) $errores[] = "Las contraseñas no coinciden.";

    // Conexión usando tu clase Database
    $db = new Database();
    $connection = $db->getConnection();

    // Verificar si correo existe
    $sql = "SELECT id FROM usuarios WHERE email = ?";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $errores[] = "Este correo ya está registrado.";
    }

    // Si no hay errores → insertar
    if (empty($errores)) {

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $sqlInsert = "INSERT INTO usuarios (nombre, email, password, tipo, activo)
                      VALUES (?, ?, ?, 'cliente', 1)";
        $stmtInsert = $connection->prepare($sqlInsert);

        if ($stmtInsert->execute([$nombre, $email, $passwordHash])) {
            $exito = true;
        } else {
            $errores[] = "Error al registrar el usuario.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrarse - Florería</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5" style="max-width: 420px;">

    <div class="card shadow-sm">
        <div class="card-body">

            <h3 class="text-center mb-4">Crear cuenta</h3>

            <?php if ($exito): ?>
                <div class="alert alert-success">
                    ¡Registro exitoso! Ahora puedes iniciar sesión.
                </div>
                <a href="login.php"
                   class="btn btn-primary w-100">Ir al login</a>
                <?php exit; ?>
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

            <form method="post">

                <div class="mb-3">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" name="nombre" class="form-control"
                           value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Repetir contraseña</label>
                    <input type="password" name="password2" class="form-control" required>
                </div>

                <button class="btn btn-success w-100">Registrarme</button>

                <p class="text-center mt-3">
                    ¿Ya tienes cuenta?
                    <a href="login.php">Inicia sesión</a>
                </p>

            </form>

        </div>
    </div>

</div>

</body>
</html>
