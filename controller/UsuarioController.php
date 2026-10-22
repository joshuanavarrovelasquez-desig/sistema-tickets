<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../model/GestorDeUsuarios.php';

class UsuarioController {

    public function procesarLogin(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = $_POST['correo'];
            $password = $_POST['password'];

            $gestor = new GestorDeUsuarios();
            $usuarioLogueado = $gestor->autenticarUsuario($correo, $password);

            if ($usuarioLogueado !== null) {
                $_SESSION['id_usuario'] = $usuarioLogueado['id_usuario'];
                $_SESSION['nombre'] = $usuarioLogueado['nombre'];
                $_SESSION['rol'] = $usuarioLogueado['rol'];
                
                if ($_SESSION['rol'] === 'admin') {
                    header("Location: /sistema_tickets/views/panel_admin.php");
                } elseif ($_SESSION['rol'] === 'tecnico') {
                    // NUEVO: Redirige al panel exclusivo del técnico
                    header("Location: /sistema_tickets/views/panel_tecnico.php");
                } else {
                    header("Location: /sistema_tickets/views/listar_tickets.php");
                }
                exit();
            } else {
                header("Location: /sistema_tickets/views/login.php?error=1");
                exit();
            }
        }
    }

    public function cerrarSesion(): void {
        session_destroy();
        header("Location: /sistema_tickets/views/login.php");
        exit();
    }

    // MÉTODO PARA CREAR USUARIOS
    public function registrarNuevoUsuario(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = $_POST['nombre'] ?? '';
            $correo = $_POST['correo'] ?? '';
            $password = $_POST['password'] ?? '';
            $rol = $_POST['rol'] ?? 'cliente';

            $gestor = new GestorDeUsuarios();
            $exito = $gestor->registrarUsuario($nombre, $correo, $password, $rol);

            if ($exito) {
                header("Location: /sistema_tickets/views/gestion_usuarios.php?mensaje=creado");
            } else {
                header("Location: /sistema_tickets/views/gestion_usuarios.php?mensaje=error");
            }
            exit();
        }
    }
    // Método para procesar la actualización del perfil
    public function actualizarPerfil(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idUsuario = $_SESSION['id_usuario'];
            $nombre = $_POST['nombre'] ?? '';
            $correo = $_POST['correo'] ?? '';
            $password = $_POST['password'] ?? '';

            $gestor = new GestorDeUsuarios();
            $exito = $gestor->actualizarPerfil($idUsuario, $nombre, $correo, $password);

            if ($exito) {
                // Actualizamos el nombre en la sesión si cambió
                $_SESSION['nombre'] = $nombre;
                header("Location: /sistema_tickets/views/perfil.php?mensaje=actualizado");
            } else {
                header("Location: /sistema_tickets/views/perfil.php?mensaje=error");
            }
            exit();
        }
    }
}
?>