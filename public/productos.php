<?php
// public/productos.php
session_start();
require_once __DIR__ . '/../includes/config/database.php';
require_once __DIR__ . '/../includes/config/functions.php';

$db = new Database();
$connection = $db->getConnection();



$stmtCat = $connection->prepare("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
$stmtCat->execute();
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);


$q = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);


// Obtener todos los productos activos
$q = trim($_GET['q'] ?? '');

$sql = "SELECT p.*, c.nombre AS categoria
        FROM productos p
        LEFT JOIN categorias c ON c.id = p.categoria_id
        WHERE p.activo = 1";
$params = [];

if ($q !== '') {
    $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

if ($cat > 0) {
    $sql .= " AND p.categoria_id = ?";
    $params[] = $cat;
}

$sql .= " ORDER BY p.destacado DESC, p.nombre ASC";

$stmt = $connection->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todos los productos - Florería Bella</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-success">
        <div class="container">
            <a class="navbar-brand text-white" href="index.php">
                <i class="bi bi-flower1"></i> Florería Bella
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#menuProductos" aria-expanded="false">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuProductos">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a href="index.php" class="nav-link text-white">Inicio</a></li>
                    <li class="nav-item"><a href="carrito.php" class="nav-link text-white">
                        <i class="bi bi-cart"></i> Carrito
                    </a></li>

                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" role="button"
                               data-bs-toggle="dropdown">
                                <?= htmlspecialchars($_SESSION['usuario_nombre']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="perfil.php">Mi Perfil</a></li>
                                <li><a class="dropdown-item" href="pedidos.php">Mis pedidos</a></li>
                                <?php if(($_SESSION['usuario_tipo'] ?? '') === 'admin'): ?>
                                    <li><a class="dropdown-item" href="../admin/dashboard.php">Panel del Administrador</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="../includes/auth/logout.php">Cerrar sesión</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="../includes/auth/login.php" class="btn btn-light btn-sm ms-2">Iniciar sesión</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Listado de productos -->
   <section id="lista-productos" class="container my-5">
        <h2 class="text-center mb-4">Todos nuestros productos</h2>

    <form method="get" class="row g-2 mb-3">
    <div class="col-md-6">
        <input type="text" name="q" class="form-control"
            placeholder="Buscar por nombre o descripción..."
            value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    </div>

    <div class="col-md-4">
        <select name="cat" class="form-select">
        <option value="0">Todas las categorías</option>
        <?php foreach ($categorias as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= ((int)($_GET['cat'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['nombre']) ?>
            </option>
        <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-2 d-grid">
        <button class="btn btn-success" type="submit">
        <i class="bi bi-search"></i> Filtrar
        </button>
    </div>
    </form>





            <form method="get" class="row g-2 mb-3">
            <div class="col-md-10">
                <input type="text" name="q" class="form-control"
                    placeholder="Buscar productos por nombre o descripción..."
                    value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-success" type="submit">
                <i class="bi bi-search"></i> Buscar
                </button>
            </div>
            </form>




        <?php if (empty($productos)): ?>
            <p class="text-center text-muted">Aún no hay productos registrados.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($productos as $producto): ?>

                    <?php
                                            // Armar ruta de imagen compatible (viejo y nuevo)
                        $imgDb = $producto['imagen_principal'] ?? '';
                        $img = "../assets/images/productos/default.jpg";

                        if ($imgDb !== '') {
                            if (str_starts_with($imgDb, 'assets/')) {
                                // NUEVO: ya viene ruta completa desde la BD
                                $img = "../" . $imgDb;
                            } else {
                                // ANTIGUO: solo nombre del archivo
                                $img = "../assets/uploads/productos/" . $imgDb;
                            }
                        }

                    ?>

                    <div class="col-md-3 mb-4" id="prod-<?= $producto['id']; ?>">
                            <div class="card h-100">
                            <img src="<?= htmlspecialchars($img); ?>" 
                                 class="card-img-top"
                                 alt="<?= htmlspecialchars($producto['nombre']); ?>">

                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">
                                    <?= htmlspecialchars($producto['nombre']); ?>
                                </h5>

                                <?php if (!empty($producto['categoria'])): ?>
                                    <span class="badge bg-light text-muted mb-2">
                                        <?= htmlspecialchars($producto['categoria']); ?>
                                    </span>
                                <?php endif; ?>

                                <p class="card-text mb-2">
                                    <?= htmlspecialchars(substr($producto['descripcion'], 0, 80)); ?>...
                                </p>

                                <p class="h5 text-success mb-3">
                                    S/. <?= number_format($producto['precio'], 2); ?>
                                </p>

                                <!-- Form agregar al carrito -->
                              <form action="carrito.php" method="POST" class="form-agregar-carrito mt-auto">
                                <input type="hidden" name="agregar" value="1">
                                <input type="hidden" name="producto_id" value="<?= $producto['id']; ?>">

                                <!-- Volver directamente al producto -->
                                <input type="hidden" name="redirect" value="productos.php#prod-<?= $producto['id']; ?>">

                                <div class="input-group mb-3">
                                    <input type="number" name="cantidad" class="form-control" value="1" min="1" max="10">
                                    <button class="btn btn-success" type="submit">
                                        <i class="bi bi-cart-plus"></i>
                                    </button>
                                </div>
                            </form>




                                <a href="producto.php?id=<?= $producto['id']; ?>"
                                   class="btn btn-outline-success w-100">
                                    Ver detalles
                                </a>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
