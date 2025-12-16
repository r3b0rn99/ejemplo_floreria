<?php
// admin/index.php
session_start();

// Si no está logueado o no es admin, mandar al login
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header('Location: ../includes/auth/login.php');
    exit;
}

// Si todo ok, redirigir al dashboard
header('Location: dashboard.php');
exit;
