<?php
session_start();
require_once '../includes/config/database.php';
require_once '../includes/config/functions.php';



$db = new Database();
$connection = $db->getConnection();

$pageTitle = "Inicio - Florería Bella";
$basePath  = "../";
require_once __DIR__ . '/../views/partials/header.php';


// Categorías para filtro
$stmtCat = $connection->prepare("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
$stmtCat->execute();
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// Filtros de destacados
$q = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);

require_once __DIR__ . '/../app/Models/ProductoModel.php';
$model = new ProductoModel($connection);
$productos_destacados = $model->destacados($q, $cat);






// Obtener productos destacados
$sql = "SELECT * FROM productos WHERE destacado = 1 AND activo = 1";
$params = [];

if ($q !== '') {
    $sql .= " AND (nombre LIKE ? OR descripcion LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

if ($cat > 0) {
    $sql .= " AND categoria_id = ?";
    $params[] = $cat;
}

$sql .= " ORDER BY id DESC"; // sin límite

$stmt = $connection->prepare($sql);
$stmt->execute($params);
$productos_destacados = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>

<?php
$pageTitle = "Inicio - Florería Bella";
$basePath  = "../";
require_once __DIR__ . '/../views/partials/header.php';
?>




   <!-- Carrusel de Banner -->
<div id="carouselBanner" class="carousel slide" data-bs-ride="carousel">

    <!-- PUNTITOS DE NAVEGACIÓN -->
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#carouselBanner" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#carouselBanner" data-bs-slide-to="1" aria-label="Slide 2"></button>
    </div>

    <!-- SLIDES -->
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="../assets/images/banner/banner1.jpg" class="d-block w-100" alt="Flores frescas">
            <div class="carousel-caption d-none d-md-block">
                <h5>Flores Frescas Todos los Días</h5>
                <p>Envíos a domicilio en 2 horas</p>
            </div>
        </div>
        <div class="carousel-item">
            <img src="../assets/images/banner/banner2.jpg" class="d-block w-100" alt="San Valentín">
            <div class="carousel-caption d-none d-md-block">
                <h5>San Valentín 30% OFF</h5>
                <p>Prepara tu sorpresa con anticipación</p>
            </div>
        </div>
    </div>

    <!-- FLECHAS -->
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselBanner" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Anterior</span>
    </button>

    <button class="carousel-control-next" type="button" data-bs-target="#carouselBanner" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Siguiente</span>
    </button>

</div>


    <!-- Productos Destacados -->
    <section id="productos-destacados" class="container my-5">
        <h2 class="text-center mb-4">Nuestros Productos Destacados</h2>


            <form method="get" class="row g-2 mb-4">
            <div class="col-md-6">
                <input type="text" name="q" class="form-control"
                    placeholder="Buscar destacados por nombre o descripción..."
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









        <div class="row">
            <?php foreach($productos_destacados as $producto): ?>
            <div class="col-md-3 mb-4" id="dest-<?= $producto['id']; ?>">
                <div class="card h-100">

                    <?php
                $imgDb = $producto['imagen_principal'] ?? '';
                $src = '';

                if ($imgDb !== '') {
                    if (str_starts_with($imgDb, 'assets/')) {
                        // nuevo: ya es ruta completa
                        $src = '../' . $imgDb;
                    } else {
                        // antiguo: solo nombre de archivo
                        $src = '../assets/uploads/productos/' . $imgDb;
                    }
                }
                ?>
                <img src="<?= htmlspecialchars($src ?: '../assets/images/no-image.png') ?>"
                    class="card-img-top"
                    alt="<?= htmlspecialchars($producto['nombre']); ?>">


                    <div class="card-body">
                        <h5 class="card-title"><?php echo $producto['nombre']; ?></h5>
                        <p class="card-text"><?php echo substr($producto['descripcion'], 0, 100); ?>...</p>
                        <p class="h5 text-success">S/. <?php echo $producto['precio']; ?></p>
                        
                       <form action="carrito.php" method="POST" class="form-agregar-carrito">
                        <input type="hidden" name="agregar" value="1">
                        <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">

                        <input type="hidden" name="redirect" value="index.php#dest-<?php echo $producto['id']; ?>">

                        <div class="input-group mb-3">
                            <input type="number" name="cantidad" class="form-control" value="1" min="1" max="10">
                            <button class="btn btn-success" type="submit">
                                <i class="bi bi-cart-plus"></i>
                            </button>
                        </div>
                    </form>




                        <a href="producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-outline-success w-100">
                            Ver Detalles
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <?php require_once __DIR__ . '/../views/partials/footer.php'; ?>
