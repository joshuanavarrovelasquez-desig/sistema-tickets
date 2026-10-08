<?php
// Reemplaza el require de UsuarioController por LoginController
require_once __DIR__ . '/controller/LoginController.php';

$accion = $_GET['accion'] ?? '';

if ($accion === 'login') {
    $controller = new LoginController();
    $controller->procesarLogin();
} elseif ($accion === 'logout') {
    $controller = new LoginController();
    $controller->cerrarSesion();
}
// ... el resto de tus rutas (crear, exportar, etc.) se mantienen igual
?>