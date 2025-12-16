<?php
// includes/config/app.php
// Configuración general (boletas/tickets y QR). No afecta el front.

if (!defined('FLORERIA_NOMBRE')) {
    define('FLORERIA_NOMBRE', 'FLORERÍA BELLA');
}

if (!defined('FLORERIA_DIRECCION')) {
    define('FLORERIA_DIRECCION', 'Av. Principal 123, Lima – Perú');
}

if (!defined('FLORERIA_TELEFONO')) {
    define('FLORERIA_TELEFONO', '+51 925 576 823');
}

// WhatsApp de la florería (formato internacional SOLO NÚMEROS)
// Ejemplo Perú: 51999999999
if (!defined('FLORERIA_WHATSAPP')) {
    define('FLORERIA_WHATSAPP', '51925576823');
}
