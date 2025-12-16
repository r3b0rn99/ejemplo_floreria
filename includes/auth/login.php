<?php
session_start();
require_once '../config/database.php';

$db = new Database();
$connection = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Buscar usuario
    $sql = "SELECT * FROM usuarios WHERE email = ? LIMIT 1";
    $stmt = $connection->prepare($sql);
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password'])) {

        // Guardar datos en sesión
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_tipo'] = $usuario['tipo'];  // 👈 IMPORTANTE
        // Alias para compatibilidad con páginas que verifican "usuario_rol"
        $_SESSION['usuario_rol'] = $usuario['tipo'];

        // Si es admin → panel admin
        if ($usuario['tipo'] === 'admin') {
            // Ruta RELATIVA (evita 404 cuando el proyecto está dentro de otra carpeta)
            header('Location: ../../admin/dashboard.php');
            exit;
        }

        // Si es cliente → página normal
        header('Location: ../../public/index.php');
        exit;
    }

    $error = "Credenciales inválidas";
}
?>

<!DOCTYPE html>
<meta name="viewport" content="width=device-width, initial-scale=1">
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión - Florería</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 420px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="text-center mb-4">Iniciar sesión</h3>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="email" class="form-control"
                           required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button class="btn btn-primary w-100">Ingresar</button>
            </form>
                <div class="mt-3 text-center">
                ¿No tienes cuenta?
                <a href="register.php">Regístrate aquí</a>
                </div>


            <div class="mt-3 text-center">
                <a href="../../public/index.php">Volver a la tienda</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
