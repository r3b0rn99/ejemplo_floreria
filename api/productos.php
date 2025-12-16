<?php
// api/productos.php
require_once __DIR__ . '/../includes/config/database.php';
require_once __DIR__ . '/_auth.php';

require_api_key();

header('Content-Type: application/json; charset=utf-8');

$db = new Database();
$connection = $db->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Helper: leer JSON body
function read_json(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// ============ GET ============
if ($method === 'GET') {
    if ($id > 0) {
        $stmt = $connection->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$p) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Producto no encontrado'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode(['ok' => true, 'data' => $p], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // listado
    $q = trim($_GET['q'] ?? '');
    $cat = (int)($_GET['categoria_id'] ?? 0);

    $sql = "SELECT * FROM productos WHERE 1=1";
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

    $sql .= " ORDER BY id DESC";

    $stmt = $connection->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============ POST (crear) ============
if ($method === 'POST') {
    $data = read_json();

    $nombre = trim($data['nombre'] ?? '');
    $descripcion = trim($data['descripcion'] ?? '');
    $precio = (float)($data['precio'] ?? 0);
    $categoria_id = isset($data['categoria_id']) ? (int)$data['categoria_id'] : null;
    $stock = isset($data['stock']) ? (int)$data['stock'] : 0;
    $imagen_principal = trim($data['imagen_principal'] ?? '');
    $imagenes = $data['imagenes'] ?? null; // puede ser string o array
    $destacado = !empty($data['destacado']) ? 1 : 0;
    $activo = isset($data['activo']) ? (int)!!$data['activo'] : 1;

    if ($nombre === '' || $precio <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'nombre es obligatorio y precio debe ser > 0'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Si "imagenes" viene como array, lo guardamos JSON en text
    if (is_array($imagenes)) {
        $imagenes = json_encode($imagenes, JSON_UNESCAPED_UNICODE);
    }

    $stmt = $connection->prepare("
        INSERT INTO productos (nombre, descripcion, precio, categoria_id, stock, imagen_principal, imagenes, destacado, activo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $nombre,
        $descripcion,
        $precio,
        $categoria_id,
        $stock,
        $imagen_principal !== '' ? $imagen_principal : null,
        $imagenes,
        $destacado,
        $activo
    ]);

    echo json_encode(['ok' => true, 'id' => (int)$connection->lastInsertId()], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============ PUT/PATCH (actualizar) ============
if ($method === 'PUT' || $method === 'PATCH') {
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Falta id en la URL ?id='], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $data = read_json();

    // Traer actual para patch
    $stmt = $connection->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $actual = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$actual) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Producto no encontrado'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $nombre = array_key_exists('nombre', $data) ? trim($data['nombre']) : $actual['nombre'];
    $descripcion = array_key_exists('descripcion', $data) ? trim($data['descripcion']) : $actual['descripcion'];
    $precio = array_key_exists('precio', $data) ? (float)$data['precio'] : (float)$actual['precio'];
    $categoria_id = array_key_exists('categoria_id', $data) ? (int)$data['categoria_id'] : $actual['categoria_id'];
    $stock = array_key_exists('stock', $data) ? (int)$data['stock'] : (int)$actual['stock'];
    $imagen_principal = array_key_exists('imagen_principal', $data) ? trim($data['imagen_principal']) : $actual['imagen_principal'];
    $imagenes = array_key_exists('imagenes', $data) ? $data['imagenes'] : $actual['imagenes'];
    $destacado = array_key_exists('destacado', $data) ? (int)!!$data['destacado'] : (int)$actual['destacado'];
    $activo = array_key_exists('activo', $data) ? (int)!!$data['activo'] : (int)$actual['activo'];

    if ($nombre === '' || $precio <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'nombre es obligatorio y precio debe ser > 0'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (is_array($imagenes)) {
        $imagenes = json_encode($imagenes, JSON_UNESCAPED_UNICODE);
    }

    $stmt = $connection->prepare("
        UPDATE productos
        SET nombre = ?, descripcion = ?, precio = ?, categoria_id = ?, stock = ?,
            imagen_principal = ?, imagenes = ?, destacado = ?, activo = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $nombre,
        $descripcion,
        $precio,
        $categoria_id,
        $stock,
        $imagen_principal !== '' ? $imagen_principal : null,
        $imagenes,
        $destacado,
        $activo,
        $id
    ]);

    echo json_encode(['ok' => true, 'updated' => true], JSON_UNESCAPED_UNICODE);
    exit;
}

// ============ DELETE ============
if ($method === 'DELETE') {
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Falta id en la URL ?id='], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // borrado lógico (recomendado): activo = 0
    $stmt = $connection->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['ok' => true, 'deleted' => true], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
