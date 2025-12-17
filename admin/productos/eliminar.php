<?php
// admin/productos/eliminar.php
session_start();
require_once __DIR__ . '/../../includes/config/database.php';

// Solo admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header('Location: ../../includes/auth/login.php');
    exit;
}

// Validar ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("ID de producto no válido.");
}
$id = (int) $_GET['id'];

$db = new Database();
$connection = $db->getConnection();

// 1) Verificar si el producto está asociado a algún pedido
$stmt = $connection->prepare("SELECT COUNT(*) FROM detalle_pedido WHERE producto_id = ?");
$stmt->execute([$id]);
$usado = (int) $stmt->fetchColumn();

if ($usado > 0) {
    // 2) Si está usado: eliminación lógica (desactivar)
    $stmt = $connection->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
    $stmt->execute([$id]);

    header('Location: listar.php?desactivado=1');
    exit;
}

// 3) Si no está usado: eliminar físico
$stmt = $connection->prepare("DELETE FROM productos WHERE id = ?");
$stmt->execute([$id]);

header('Location: listar.php?eliminado=1');
exit;
