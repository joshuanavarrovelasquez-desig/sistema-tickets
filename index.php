<?php
session_start();

// Requerimos ambos controladores
require_once 'controller/TicketController.php';
require_once 'controller/UsuarioController.php';

// Si nadie envía una acción, mandamos al usuario a la pantalla de Login por defecto
$accion = $_GET['accion'] ?? 'vista_login'; 

if ($accion === 'vista_login') {
    header("Location: views/login.php");
    exit();

} elseif ($accion === 'login') {
    $controlador = new UsuarioController();
    $controlador->procesarLogin();

} elseif ($accion === 'logout') {
    $controlador = new UsuarioController();
    $controlador->cerrarSesion();

} elseif ($accion === 'crear') {
    if (!isset($_SESSION['id_usuario'])) {
        header("Location: views/login.php");
        exit();
    }
    $controlador = new TicketController();
    $controlador->registrarNuevoTicket();

} elseif ($accion === 'actualizar_estado') {
    // AQUÍ ESTÁ LA RUTA QUE SOLUCIONA LA PANTALLA BLANCA
    $controlador = new TicketController();
    $controlador->actualizarEstado();
}
?>