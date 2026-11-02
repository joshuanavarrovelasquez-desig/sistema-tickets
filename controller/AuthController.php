<?php
class AuthController extends BaseController {

    public function mostrarLogin(): void {
        if (isset($_SESSION['id_usuario'])) {
            $this->redirigir($this->accionInicio());
        }
        $this->vista('login');
    }

    public function procesarLogin(): void {
        $this->exigirPost();
        if (!$this->tokenCsrfValido()) {
            $this->redirigir('vista_login', ['error' => 1]);
        }

        $correo   = trim((string)($_POST['correo'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        $usuarioDao = new UsuarioDAO();
        $usuario = $usuarioDao->buscarPorCorreo($correo);

        if ($usuario === null || !$usuario->verificarPassword($password)) {
            $this->redirigir('vista_login', ['error' => 1]);
        }

        // Contraseñas antiguas (texto plano) se convierten a hash en el primer acceso.
        if ($usuario->necesitaRehash()) {
            $usuarioDao->actualizarPassword($usuario->getId(), password_hash($password, PASSWORD_DEFAULT));
        }

        session_regenerate_id(true);
        $_SESSION['id_usuario'] = $usuario->getId();
        $_SESSION['nombre']     = $usuario->getNombre();
        $_SESSION['rol']        = $usuario->getRol();

        $this->redirigir($this->accionInicio());
    }

    public function cerrarSesion(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        header('Location: index.php?accion=vista_login');
        exit();
    }
}
