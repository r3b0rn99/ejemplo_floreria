<?php
// public/checkout.php
session_start();
require_once __DIR__ . '/../includes/config/database.php';

// OPCIONAL: si quieres usar funciones extra
// require_once __DIR__ . '/../includes/config/functions.php';

$db = new Database();
$connection = $db->getConnection();

// Si el carrito está vacío de verdad
if (empty($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $carrito_vacio = true;
    $items = [];
    $subtotal = 0;
} else {
    $carrito_vacio = false;
    $items = $_SESSION['carrito'];

    // Calcular subtotal
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['precio'] * $item['cantidad'];
    }
}

$envio = 15.00;
$total = $subtotal + $envio;

// Si envías el formulario de pago (lógica de pedido REAL la podemos hacer luego)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$carrito_vacio) {

    if (!isset($_SESSION['usuario_id'])) {
        $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '/');
        header("Location: ../includes/auth/login.php?redirect={$redirect}");
        exit;
    }

    $usuario_id = $_SESSION['usuario_id'];
    $metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';
    $direccion_envio = trim($_POST['direccion_envio'] ?? 'Sin dirección');

    // Calcular totales
    $subtotal = 0;
    foreach ($items as $it) {
        $subtotal += $it['precio'] * $it['cantidad'];
    }
    $envio = 15.00;
    $total = $subtotal + $envio;

    // Crear conexión
    $db = new Database();
    $connection = $db->getConnection();

    try {
        $connection->beginTransaction();

        // Crear código de pedido
        $codigo = "PED-" . strtoupper(uniqid());

        // INSERTAR PEDIDO
        $sqlPedido = "INSERT INTO pedidos (usuario_id, codigo_pedido, fecha_pedido, total, estado, metodo_pago)
                      VALUES (?, ?, NOW(), ?, 'pendiente', ?)";
        $stmt = $connection->prepare($sqlPedido);
        $stmt->execute([$usuario_id, $codigo, $total, $metodo_pago]);

        $pedido_id = $connection->lastInsertId();

        // INSERTAR DETALLES
        $sqlDetalle = "INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal)
                       VALUES (?, ?, ?, ?, ?)";

        $stmtDet = $connection->prepare($sqlDetalle);

        foreach ($items as $it) {
            $stmtDet->execute([
                $pedido_id,
                $it['producto_id'],
                $it['cantidad'],
                $it['precio'],
                $it['precio'] * $it['cantidad']
            ]);
        }

        $connection->commit();

        // Vaciar carrito
        $_SESSION['carrito'] = [];
        $_SESSION['carrito_count'] = 0;

        // Redirigir a mis pedidos
        header("Location: pedidos.php");
        exit;

    } catch (Exception $e) {
        $connection->rollBack();
        die("Error al registrar pedido: " . $e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Florería Bella</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                data-bs-target="#menuCheckout" aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuCheckout">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a href="index.php" class="nav-link text-white">Inicio</a></li>
                <li class="nav-item"><a href="carrito.php" class="nav-link text-white">Carrito</a></li>
                <li class="nav-item"><a href="checkout.php" class="nav-link text-white active">Checkout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">

    <h1 class="mb-4">Checkout</h1>

    <?php if ($carrito_vacio): ?>

        <div class="alert alert-info">
            Tu carrito está vacío. <a href="productos.php" class="alert-link">Ver productos</a>
        </div>

    <?php else: ?>

        <div class="row">
            <!-- Resumen del carrito -->
            <div class="col-md-7 mb-4">
                <h4>Resumen de tu pedido</h4>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nombre']); ?></td>
                                <td class="text-center"><?= (int) $item['cantidad']; ?></td>
                                <td class="text-end">S/ <?= number_format($item['precio'], 2); ?></td>
                                <td class="text-end">S/ <?= number_format($item['precio'] * $item['cantidad'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Datos de pago / envío -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Datos para el pago</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Subtotal:</strong> S/ <?= number_format($subtotal, 2); ?></p>
                        <p><strong>Envío:</strong> S/ <?= number_format($envio, 2); ?></p>
                        <p class="h5 text-success">
                            <strong>Total: S/ <?= number_format($total, 2); ?></strong>
                        </p>

                        <hr>

                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Método de pago</label>
                                <select name="metodo_pago" class="form-select" required>
                                    <option value="efectivo">Efectivo contra entrega</option>
                                    <option value="tarjeta">Tarjeta de crédito/débito</option>
                                    <option value="yape">Yape / Plin</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Dirección de entrega</label>
                                <textarea name="direccion_envio" class="form-control" rows="3"
                                          placeholder="Ingresa la dirección donde se entregarán las flores"
                                          required></textarea>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                Confirmar pedido
                            </button>

                            <a href="carrito.php" class="btn btn-link w-100 mt-2">
                                Volver al carrito
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
