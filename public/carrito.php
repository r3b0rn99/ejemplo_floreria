<?php
session_start();
require_once '../includes/config/database.php';
require_once '../includes/config/functions.php';

$db = new Database();
$connection = $db->getConnection();


// Manejo de acciones por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

   // 1) Agregar al carrito
if (isset($_POST['agregar'])) {

    $producto_id = (int) $_POST['producto_id'];
    $cantidad    = max(1, (int) $_POST['cantidad']);

    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    $encontrado = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['producto_id'] == $producto_id) {
            $item['cantidad'] += $cantidad;
            $encontrado = true;
            break;
        }
    }
    unset($item);

    if (!$encontrado) {
        $query = "SELECT id, nombre, precio, imagen_principal FROM productos WHERE id = ?";
        $stmt = $connection->prepare($query);
        $stmt->execute([$producto_id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            $_SESSION['carrito'][] = [
                'producto_id' => $producto_id,
                'nombre'      => $producto['nombre'],
                'precio'      => $producto['precio'],
                'imagen'      => $producto['imagen_principal'],
                'cantidad'    => $cantidad
            ];
        }
    }

    $_SESSION['carrito_count'] = array_sum(array_column($_SESSION['carrito'], 'cantidad'));

    // 🟢 USAR EL REDIRECT QUE VIENE DEL FORM
    $redirect = $_POST['redirect'] ?? 'carrito.php';
    header('Location: ' . $redirect);
    exit();
}



    // 2) Actualizar cantidad
    if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {

        $index    = (int) ($_POST['index'] ?? -1);
        $cantidad = max(1, (int) ($_POST['cantidad'] ?? 1));

        if (isset($_SESSION['carrito'][$index])) {
            $_SESSION['carrito'][$index]['cantidad'] = $cantidad;
            // Recalcular contador
            $_SESSION['carrito_count'] = array_sum(array_column($_SESSION['carrito'], 'cantidad'));
        }

        header('Location: carrito.php');
        exit();
    }
}


// Calcular totales
$subtotal = 0;
if(isset($_SESSION['carrito'])) {
    foreach($_SESSION['carrito'] as $item) {
        $subtotal += $item['precio'] * $item['cantidad'];
    }
}
$envio = 15.00; // Costo fijo de envío
$total = $subtotal + $envio;

// Manejar eliminar del carrito (GET)
if (isset($_GET['eliminar'])) {
    $index = (int) $_GET['eliminar'];

    if (isset($_SESSION['carrito'][$index])) {
        unset($_SESSION['carrito'][$index]);

        // Reindexar el array para que los índices vuelvan a ser 0,1,2...
        $_SESSION['carrito'] = array_values($_SESSION['carrito']);
    }

    // Recalcular contador de items
    if (!empty($_SESSION['carrito'])) {
        $_SESSION['carrito_count'] = array_sum(array_column($_SESSION['carrito'], 'cantidad'));
    } else {
        $_SESSION['carrito_count'] = 0;
    }

    header('Location: carrito.php');
    exit;
}

?>




<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <!-- Navbar (similar al index) -->
    
    <div class="container my-5">
        <h1 class="mb-4">Carrito de Compras</h1>
        
        <?php if(empty($_SESSION['carrito'])): ?>
            <div class="alert alert-info">
                Tu carrito está vacío. <a href="productos.php">Ver productos</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($_SESSION['carrito'] as $index => $item): ?>
                                <tr>
                                    <td>
                                        <img src="../assets/uploads/productos/<?php echo $item['imagen']; ?>" 
                                             width="50" class="me-3">
                                        <?php echo $item['nombre']; ?>
                                    </td>
                                    <td>S/. <?php echo $item['precio']; ?></td>
                                    <td>
                                       <form method="POST" action="carrito.php" class="d-inline">
                                            <input type="hidden" name="accion" value="actualizar">
                                            <input type="hidden" name="index" value="<?php echo $index; ?>">
                                            <input type="number" name="cantidad" value="<?php echo $item['cantidad']; ?>" 
                                                min="1" max="10" class="form-control form-control-sm" style="width: 80px;" 
                                                onchange="this.form.submit()">
                                        </form>

                                    </td>
                                    <td>S/. <?php echo $item['precio'] * $item['cantidad']; ?></td>
                                    <td>
                                        <a href="carrito.php?eliminar=<?= $index ?>" 
                                            class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </a>

                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Resumen del Pedido</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td>Subtotal:</td>
                                    <td class="text-end">S/. <?php echo number_format($subtotal, 2); ?></td>
                                </tr>
                                <tr>
                                    <td>Envío:</td>
                                    <td class="text-end">S/. <?php echo number_format($envio, 2); ?></td>
                                </tr>
                                <tr class="table-success">
                                    <td><strong>Total:</strong></td>
                                    <td class="text-end"><strong>S/. <?php echo number_format($total, 2); ?></strong></td>
                                </tr>
                            </table>
                            <div class="d-grid gap-2">
                                <a href="productos.php" class="btn btn-outline-success">
                                    <i class="bi bi-arrow-left"></i> Seguir Comprando
                                </a>
                                <a href="checkout.php" class="btn btn-success">
                                    Proceder al Pago <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>