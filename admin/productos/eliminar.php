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

// Opcional: podrías verificar si el producto está en algún pedido antes de borrarlo

$sql = "DELETE FROM productos WHERE id = ?";
$stmt = $connection->prepare($sql);
$stmt->execute([$id]);

header('Location: listar.php?eliminado=1');
exit;
