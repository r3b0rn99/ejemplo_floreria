<?php
// includes/auth/auth_admin.php
session_start();

// Compatibilidad: en el proyecto se usa "usuario_tipo" (login) y en otros lados "usuario_rol".
$rol = $_SESSION['usuario_tipo'] ?? ($_SESSION['usuario_rol'] ?? null);

if (!isset($_SESSION['usuario_id']) || $rol !== 'admin') {
    // Redirección robusta (no depende del nombre de carpeta en htdocs)
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $pos = strpos($script, '/admin');
    $base = ($pos !== false) ? substr($script, 0, $pos) : '';
    $loginUrl = $base . '/includes/auth/login.php';
    header('Location: ' . $loginUrl);
    exit;
}
