<?php
// admin/dashboard.php
session_start();
require_once __DIR__ . '/../includes/config/database.php';
require_once __DIR__ . '/../includes/config/functions.php';



// Restringir a administradores
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header('Location: ../includes/auth/login.php');
    exit;
}

$db = new Database();
$connection = $db->getConnection();

// ====== CONFIG (ajusta si tu BD tiene otros nombres) ======
$pedidoFechaCol = 'fecha_pedido';   // alternativas comunes: fecha_pedido, created_at, fecha, fecha_registro
$pedidoTotalCol = 'total';            // alternativas comunes: total_pagar, total_final, monto_total
$usuarioFechaCol = 'fecha_creacion';  // alternativas: created_at, fecha_registro

// Helper: ejecuta query y devuelve array
function fetchAllSafe($connection, $sql, $params = []) {
    $stmt = $connection->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 1) Ventas por mes (últimos 6-12 meses)
$ventasMes = [];
try {
    $sqlVentas = "
        SELECT DATE_FORMAT($pedidoFechaCol, '%Y-%m') AS mes,
               SUM($pedidoTotalCol) AS total
        FROM pedidos
        WHERE $pedidoFechaCol IS NOT NULL
        GROUP BY mes
        ORDER BY mes ASC
    ";
    $ventasMes = fetchAllSafe($connection, $sqlVentas);
} catch (Throwable $e) {
    $ventasMes = []; // si tu columna no existe, no rompe la página
}

// 2) Pedidos por estado
$pedidosEstado = [];
try {
    $sqlEstado = "
        SELECT COALESCE(estado, 'SIN_ESTADO') AS estado, COUNT(*) AS cantidad
        FROM pedidos
        GROUP BY estado
        ORDER BY cantidad DESC
    ";
    $pedidosEstado = fetchAllSafe($connection, $sqlEstado);
} catch (Throwable $e) {
    $pedidosEstado = [];
}

// 3) Usuarios registrados por mes
$usuariosMes = [];
try {
    $sqlUsuarios = "
        SELECT DATE_FORMAT($usuarioFechaCol, '%Y-%m') AS mes,
               COUNT(*) AS cantidad
        FROM usuarios
        WHERE $usuarioFechaCol IS NOT NULL
        GROUP BY mes
        ORDER BY mes ASC
    ";
    $usuariosMes = fetchAllSafe($connection, $sqlUsuarios);
} catch (Throwable $e) {
    $usuariosMes = [];
}

// Convertir a arrays para JS
$labelsVentas = array_map(fn($r) => $r['mes'], $ventasMes);
$dataVentas   = array_map(fn($r) => (float)$r['total'], $ventasMes);

$labelsEstado = array_map(fn($r) => $r['estado'], $pedidosEstado);
$dataEstado   = array_map(fn($r) => (int)$r['cantidad'], $pedidosEstado);

$labelsUsuarios = array_map(fn($r) => $r['mes'], $usuariosMes);
$dataUsuarios   = array_map(fn($r) => (int)$r['cantidad'], $usuariosMes);




// ====== KPI's del dashboard (evita Undefined variable) ======
$totProductos  = 0;
$totPedidos    = 0;
$totClientes   = 0;
$ventasTotales = 0.00;


$stmt = $connection->prepare("SELECT COUNT(*) FROM usuarios WHERE tipo = 'cliente'");
$stmt->execute();
$totClientes = (int)$stmt->fetchColumn();



try {
    $stmt = $connection->prepare("SELECT COUNT(*) FROM productos");
    $stmt->execute();
    $totProductos = (int)$stmt->fetchColumn();
} catch (Throwable $e) {
    app_log("Error contando productos en dashboard", [
        'error' => $e->getMessage()
    ]);
    $totProductos = 0;
}

try {
    $stmt = $connection->prepare("SELECT COUNT(*) FROM pedidos");
    $stmt->execute();
    $totPedidos = (int)$stmt->fetchColumn();
} catch (Throwable $e) { $totPedidos = 0; }

try {
    $stmt = $connection->prepare("SELECT COALESCE(SUM(total),0) FROM pedidos");
    $stmt->execute();
    $ventasTotales = (float)$stmt->fetchColumn();
} catch (Throwable $e) { $ventasTotales = 0.00; }





?>






<meta name="viewport" content="width=device-width, initial-scale=1">


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de administración - Florería</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<nav class="navbar navbar-expand-lg" style="background-color:#28a745;">
  <div class="container-fluid">

    <a class="navbar-brand text-white fw-bold" href="dashboard.php">
      <i class="bi bi-flower1"></i> Admin Florería
    </a>

    <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse"
            data-bs-target="#adminMenu">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="adminMenu">
      <ul class="navbar-nav ms-auto">

        <li class="nav-item">
          <a class="nav-link text-white" href="productos/listar.php">
            <i class="bi bi-basket"></i> Productos
          </a>
        </li>
      



        <li class="nav-item">
          <a class="nav-link text-white" href="pedidos/gestionar.php">
            <i class="bi bi-receipt"></i> Pedidos
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link text-white" href="boletas/">
            <i class="bi bi-file-earmark-pdf"></i> Boletas
          </a>
        </li>

        <li class="nav-item">
          <span class="nav-link text-white">
            <i class="bi bi-person-circle"></i>
            <?= htmlspecialchars($_SESSION['usuario_nombre']); ?>
          </span>
        </li>

        <li class="nav-item">
          <a class="nav-link text-warning fw-bold" href="../includes/auth/logout.php">
            <i class="bi bi-box-arrow-right"></i> Salir
          </a>
        </li>

      </ul>
    </div>

  </div>
</nav>


<div class="container-fluid mt-4">
  <div class="row">
    <!-- Columna principal -->
    <div class="col-12 col-lg-9">

      <h1 class="h3 mb-4">Dashboard</h1>

      <div class="d-flex gap-2 mb-3">
  <a href="reportes/ventas_csv.php" class="btn btn-outline-success">
    <i class="bi bi-filetype-csv"></i> Exportar ventas (CSV)
  </a>
</div>



      <!-- Tarjetas de métricas -->
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6 class="card-subtitle mb-2 text-muted">Productos</h6>
              <h2 class="card-title"><?= (int) $totProductos; ?></h2>
              <p class="mb-0"><a href="productos/listar.php" class="small">Ver catálogo</a></p>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6 class="card-subtitle mb-2 text-muted">Pedidos</h6>
              <h2 class="card-title"><?= (int) $totPedidos; ?></h2>
              <p class="mb-0"><a href="pedidos/gestionar.php" class="small">Gestionar pedidos</a></p>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6 class="card-subtitle mb-2 text-muted">Clientes</h6>
              <h2 class="card-title"><?= (int) $totClientes; ?></h2>
              <p class="mb-0"><span class="small text-muted">Usuarios registrados</span></p>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6 class="card-subtitle mb-2 text-muted">Ventas totales</h6>
              <h2 class="card-title">S/ <?= number_format($ventasTotales, 2); ?></h2>
              <p class="mb-0"><span class="small text-muted">Acumulado</span></p>
            </div>
          </div>
        </div>
      </div>

      <!-- Pedidos recientes -->
      <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span><i class="bi bi-clock-history"></i> Pedidos recientes</span>
          <a href="pedidos/gestionar.php" class="btn btn-sm btn-outline-secondary">
            Ver todos
          </a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($pedidosRecientes)): 
                    $pedidosRecientes = [];
        try {
            $sql = "SELECT p.id, p.codigo_pedido, p.fecha_pedido, p.total, p.estado,
                          COALESCE(u.nombre, 'Cliente') AS cliente
                    FROM pedidos p
                    LEFT JOIN usuarios u ON u.id = p.usuario_id
                    ORDER BY p.id DESC
                    LIMIT 5";
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            $pedidosRecientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $pedidosRecientes = [];
        }

            
            ?>
            <p class="p-3 mb-0 text-muted">Aún no hay pedidos registrados.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover mb-0 align-middle">
                <thead>
                  <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($pedidosRecientes as $p): ?>
                  <tr>
                    <td><?= htmlspecialchars($p['codigo_pedido']); ?></td>
                    <td><?= htmlspecialchars($p['cliente']); ?></td>
                    <td><?= htmlspecialchars($p['fecha_pedido']); ?></td>
                    <td>S/ <?= number_format($p['total'], 2); ?></td>
                    <td>
                      <span class="badge bg-<?= badgeEstado($p['estado']); ?>">
                        <?= ucfirst($p['estado']); ?>
                      </span>
                    </td>
                    <td class="text-end">
                      <a href="pedidos/ver.php?id=<?= $p['id']; ?>" class="btn btn-sm btn-outline-primary">
                        Ver
                      </a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>

    

    <!-- Columna lateral -->
    <div class="col-12 col-lg-3">
      <div class="card shadow-sm mb-3">
        <div class="card-header">
          <i class="bi bi-gear"></i> Accesos rápidos
        </div>
        <div class="list-group list-group-flush">
          <a href="productos/agregar.php" class="list-group-item list-group-item-action">
            <i class="bi bi-plus-circle"></i> Nuevo producto
          </a>
          

          <a href="pedidos/gestionar.php" class="list-group-item list-group-item-action">
            <i class="bi bi-receipt-cutoff"></i> Gestionar pedidos
          </a>
          <a href="../public/index.php" class="list-group-item list-group-item-action">
            <i class="bi bi-house"></i> Ver tienda
          </a>
        </div>
      </div>
    </div>

  </div>

<div class="row g-3 mt-2">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title mb-3">Ventas por mes</h5>
        <canvas id="chartVentasMes"></canvas>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title mb-3">Pedidos por estado</h5>
        <canvas id="chartPedidosEstado"></canvas>
      </div>
    </div>
  </div>

  <div class="col-lg-12">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title mb-3">Usuarios registrados por mes</h5>
        <canvas id="chartUsuariosMes"></canvas>
      </div>
    </div>
  </div>
</div>



</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script>
const labelsVentas   = <?= json_encode($labelsVentas) ?>;
const dataVentas     = <?= json_encode($dataVentas) ?>;

const labelsEstado   = <?= json_encode($labelsEstado) ?>;
const dataEstado     = <?= json_encode($dataEstado) ?>;

const labelsUsuarios = <?= json_encode($labelsUsuarios) ?>;
const dataUsuarios   = <?= json_encode($dataUsuarios) ?>;

// Ventas por mes (bar)
new Chart(document.getElementById('chartVentasMes'), {
  type: 'bar',
  data: {
    labels: labelsVentas,
    datasets: [{ label: 'Ventas (S/.)', data: dataVentas }]
  },
  options: { responsive: true }
});

// Pedidos por estado (pie)
new Chart(document.getElementById('chartPedidosEstado'), {
  type: 'pie',
  data: {
    labels: labelsEstado,
    datasets: [{ data: dataEstado }]
  },
  options: { responsive: true }
});

// Usuarios por mes (line)
new Chart(document.getElementById('chartUsuariosMes'), {
  type: 'line',
  data: {
    labels: labelsUsuarios,
    datasets: [{ label: 'Usuarios', data: dataUsuarios, tension: 0.3 }]
  },
  options: { responsive: true }
});
</script>

</body>
</html>
