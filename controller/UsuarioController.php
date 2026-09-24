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
                // Guardamos los datos en la sesión (ahora incluimos el rol)
                $_SESSION['id_usuario'] = $usuarioLogueado['id_usuario'];
                $_SESSION['nombre'] = $usuarioLogueado['nombre'];
                $_SESSION['rol'] = $usuarioLogueado['rol'];
                
                // REDIRECCIÓN INTELIGENTE SEGÚN EL ROL
                if ($_SESSION['rol'] === 'admin') {
                    // Si es el administrador, va al panel general de control
                    header("Location: /sistema_tickets/views/panel_admin.php");
                } else {
                    // Si es cliente, va a su historial personal
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
}
?>