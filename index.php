<?php
session_start();

// 1. IMPORTANTE: Requerimos el controlador de usuarios
require_once 'controller/UsuarioController.php';
require_once 'controller/TicketController.php';

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
} elseif ($accion === 'crear_usuario') {
    // 2. Aquí procesamos la creación del nuevo usuario
    $controlador = new UsuarioController();
    $controlador->registrarNuevoUsuario();
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

}elseif ($accion === 'agregar_comentario') {
    $controlador = new TicketController();
    $controlador->agregarComentario();
    
} elseif ($accion === 'actualizar_perfil') {
    $controlador = new UsuarioController();
    $controlador->actualizarPerfil();
} elseif ($accion === 'asignar_tecnico') {
    $controlador = new TicketController();
    $controlador->asignarTecnico();
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