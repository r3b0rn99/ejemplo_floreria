<?php
// public/producto.php
session_start();
require_once __DIR__ . '/../includes/config/database.php';

// Crear conexión usando tu clase Database
$db = new Database();
$connection = $db->getConnection();

// Validar ID del producto
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die("Producto no válido.");
}

// Obtener producto
$sql = "SELECT p.*, c.nombre AS categoria
        FROM productos p
        LEFT JOIN categorias c ON c.id = p.categoria_id
        WHERE p.id = ? AND p.activo = 1";
$stmt = $connection->prepare($sql);
$stmt->execute([$id]);
$producto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    die("Producto no encontrado.");
}

// Ruta de imagen: misma lógica que en index.php
$img = $producto['imagen_principal']
    ? "../assets/uploads/productos/" . $producto['imagen_principal']
    : "../assets/images/productos/default.jpg";
?>
<!DOCTYPE html>

<meta name="viewport" content="width=device-width, initial-scale=1">

<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($producto['nombre']) ?> - Florería</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light bg-success mb-4">
  <div class="container">
    <a class="navbar-brand text-white" href="index.php">
        <i class="bi bi-flower1"></i> Florería Bella
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#menu" aria-expanded="false">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link text-white" href="index.php">Inicio</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="carrito.php">Carrito</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="pedidos.php">Mis pedidos</a></li>
        <li class="nav-item"><a class="nav-link text-white" href="perfil.php">Perfil</a></li>
        <?php if(($_SESSION['usuario_tipo'] ?? '') === 'admin'): ?>
            <li class="nav-item"><a class="nav-link text-white" href="../admin/dashboard.php">Panel Admin</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container">

        <?php
        $imgDb = $producto['imagen_principal'] ?? '';
        $img = "../assets/images/productos/default.jpg"; // tu fallback

        if ($imgDb !== '') {
            if (str_starts_with($imgDb, 'assets/')) {
                $img = "../" . $imgDb; // nuevo (ruta completa)
            } else {
                $img = "../assets/uploads/productos/" . $imgDb; // antiguo (solo nombre)
            }
        }
        ?>



    <div class="row">
        <!-- Imagen -->
        <div class="col-md-5 mb-4">
            <img src="<?= htmlspecialchars($img) ?>"
                 class="img-fluid rounded shadow-sm"
                 alt="<?= htmlspecialchars($producto['nombre']) ?>">
        </div>

        <!-- Información -->
        <div class="col-md-7">

            <h2><?= htmlspecialchars($producto['nombre']) ?></h2>

            <?php if (!empty($producto['categoria'])): ?>
                <p class="text-muted">
                    Categoría: <strong><?= htmlspecialchars($producto['categoria']) ?></strong>
                </p>
            <?php endif; ?>

            <h3 class="text-success mb-3">
                S/ <?= number_format($producto['precio'], 2) ?>
            </h3>

            <p class="mb-4"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>

            <!-- Form agregar al carrito -->
           <form action="carrito.php" method="post" class="mt-4">
                <input type="hidden" name="agregar" value="1">
                <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">

                <div class="input-group mb-3" style="max-width: 200px;">
                    <input type="number" name="cantidad" class="form-control" min="1" value="1">
                    <button class="btn btn-success">
                        Agregar al carrito
                    </button>
                </div>
            </form>


            <!-- Enlace para volver -->
            <a href="index.php" class="btn btn-outline-secondary mt-2">Volver al inicio</a>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
