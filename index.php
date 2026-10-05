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
    $controlador = new TicketController();
    $controlador->actualizarEstado();

// ESTA ES LA ÚNICA RUTA DE EXPORTACIÓN QUE DEBES TENER AHORA:
} elseif ($accion === 'exportar') {
    if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
        require_once 'controller/ReporteController.php';
        $controlador = new ReporteController();
        $controlador->procesarExportacion();
    } else {
        header("Location: views/login.php");
        exit();
    }
}
?>