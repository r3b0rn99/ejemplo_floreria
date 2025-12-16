<?php
// admin/reportes/ventas_csv.php
session_start();
require_once __DIR__ . '/../../includes/config/database.php';

// Solo admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header('Location: ../../includes/auth/login.php');
    exit;
}

$db = new Database();
$connection = $db->getConnection();

// Filtros opcionales
$desde  = trim($_GET['desde'] ?? '');
$hasta  = trim($_GET['hasta'] ?? '');
$estado = trim($_GET['estado'] ?? '');

require_once __DIR__ . '/../../app/Controllers/ReporteController.php';
$ctrl = new ReporteController($connection);
$rows = $ctrl->ventas($desde, $hasta, $estado);

// Descargar CSV
$filename = "reporte_ventas_" . date("Ymd_His") . ".csv";
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=' . $filename);

// BOM para Excel (tildes/ñ)
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');

// Encabezados
fputcsv($out, ['ID Pedido','ID Usuario','Cliente','Email','Total','Método Pago','Estado','Fecha Pedido']);

// Filas
foreach ($rows as $r) {
    fputcsv($out, [
        $r['id'] ?? '',
        $r['usuario_id'] ?? '',
        $r['cliente_nombre'] ?? '',
        $r['cliente_email'] ?? '',
        $r['total'] ?? '',
        $r['metodo_pago'] ?? '',
        $r['estado'] ?? '',
        $r['fecha_pedido'] ?? '',
    ]);
}

fclose($out);
exit;
