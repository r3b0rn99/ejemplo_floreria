<?php
// api/_auth.php
function require_api_key(): void
{
    // Cambia este token por uno tuyo
    $TOKEN = 'MI_TOKEN_123456';

    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $key = $headers['X-API-KEY'] ?? $headers['x-api-key'] ?? ($_GET['api_key'] ?? '');

    if (!$key || $key !== $TOKEN) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'error' => 'No autorizado. Falta o es incorrecto el token X-API-KEY.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
