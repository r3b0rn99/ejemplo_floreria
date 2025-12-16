<?php
// public/contacto.php
session_start();

$errores = [];
$exito = false;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    if ($nombre === '') $errores[] = "El nombre es obligatorio.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = "El correo no es válido.";
    if ($mensaje === '') $errores[] = "El mensaje no puede estar vacío.";

    if (empty($errores)) {
        // Datos del destino (puedes reemplazarlo)
        $destino = "contacto@floreria.com";
        $asunto = "Nuevo mensaje de contacto – Florería";

        $cuerpo = "Nombre: $nombre\n";
        $cuerpo .= "Correo: $email\n\n";
        $cuerpo .= "Mensaje:\n$mensaje\n";

        // Enviar correo
        if (@mail($destino, $asunto, $cuerpo)) {
            $exito = true;
        } else {
            $errores[] = "No se pudo enviar el mensaje. (Servidor sin mail configurado)";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contacto - Florería</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand" href="index.php">
        <img src="../assets/images/logos/logo.png" width="40">
        Florería
    </a>

    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">

        <li class="nav-item"><a class="nav-link" href="productos.php">Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="carrito.php">Carrito</a></li>
        <li class="nav-item"><a class="nav-link" href="pedidos.php">Mis pedidos</a></li>
        <li class="nav-item"><a class="nav-link" href="perfil.php">Perfil</a></li>
        <li class="nav-item"><a class="nav-link active" href="contacto.php">Contacto</a></li>

      </ul>
    </div>
  </div>
</nav>


<div class="container mt-4" style="max-width: 700px;">

    <h2 class="mb-4">Contáctanos</h2>

    <p class="text-muted">
        Si tienes dudas, pedidos especiales o quieres cotizar un arreglo personalizado, envíanos un mensaje.
        Te responderemos lo antes posible.
    </p>

    <?php if ($exito): ?>
        <div class="alert alert-success">
            ¡Gracias por tu mensaje! Te responderemos pronto.
        </div>
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


    <form method="post" class="card card-body shadow-sm">

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
            <label class="form-label">Mensaje</label>
            <textarea name="mensaje" class="form-control" rows="4" required><?= htmlspecialchars($_POST['mensaje'] ?? '') ?></textarea>
        </div>

        <button class="btn btn-primary w-100">Enviar mensaje</button>

    </form>

    <hr class="my-4">

    <h5>Datos de contacto</h5>
    <p><strong>Teléfono:</strong> +51 999 999 999</p>
    <p><strong>Correo:</strong> contacto@floreria.com</p>
    <p><strong>Dirección:</strong> Av. Principal 123, Lima – Perú</p>

</div>

</body>
</html>
