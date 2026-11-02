<?php
class UsuarioController extends BaseController {
    private const PASSWORD_MINIMA = 6;

    // ---------------- Gestión de usuarios (solo admin) ----------------
    public function gestionar(): void {
        $this->exigirRol(Usuario::ROL_ADMIN);
        $usuarioDao = new UsuarioDAO();

        $usuarioAEditar = isset($_GET['editar']) ? $usuarioDao->buscarPorId((int)$_GET['editar']) : null;

        $this->vista('gestion_usuarios', [
            'usuarios'       => $usuarioDao->listarTodos(),
            'usuarioAEditar' => $usuarioAEditar,
        ]);
    }

    public function registrarNuevoUsuario(): void {
        $this->exigirRol(Usuario::ROL_ADMIN);
        $this->exigirPost();
        $this->validarCsrf();

        $nombre   = trim((string)($_POST['nombre'] ?? ''));
        $correo   = trim((string)($_POST['correo'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $rol      = (string)($_POST['rol'] ?? Usuario::ROL_CLIENTE);

        if (!$this->datosValidos($nombre, $correo, $rol) || mb_strlen($password) < self::PASSWORD_MINIMA) {
            $this->redirigir('gestion_usuarios', ['mensaje' => 'datos_invalidos']);
        }

        $usuarioDao = new UsuarioDAO();
        if ($usuarioDao->existeCorreo($correo)) {
            $this->redirigir('gestion_usuarios', ['mensaje' => 'correo_duplicado']);
        }

        $nuevo = new Usuario(null, $nombre, $correo, $rol, password_hash($password, PASSWORD_DEFAULT));
        $exito = $usuarioDao->guardar($nuevo);
        $this->redirigir('gestion_usuarios', ['mensaje' => $exito ? 'creado' : 'error']);
    }

    public function actualizarUsuarioAdmin(): void {
        $this->exigirRol(Usuario::ROL_ADMIN);
        $this->exigirPost();
        $this->validarCsrf();

        $idUsuario = (int)($_POST['id_usuario'] ?? 0);
        $nombre    = trim((string)($_POST['nombre'] ?? ''));
        $correo    = trim((string)($_POST['correo'] ?? ''));
        $password  = (string)($_POST['password'] ?? '');
        $rol       = (string)($_POST['rol'] ?? Usuario::ROL_CLIENTE);

        $passwordInvalida = $password !== '' && mb_strlen($password) < self::PASSWORD_MINIMA;
        if ($idUsuario <= 0 || !$this->datosValidos($nombre, $correo, $rol) || $passwordInvalida) {
            $this->redirigir('gestion_usuarios', ['mensaje' => 'datos_invalidos']);
        }
        // El admin no puede quitarse a sí mismo el rol de administrador.
        if ($idUsuario === $this->idSesion() && $rol !== Usuario::ROL_ADMIN) {
            $this->redirigir('gestion_usuarios', ['mensaje' => 'error_propio']);
        }

        $usuarioDao = new UsuarioDAO();
        if ($usuarioDao->buscarPorId($idUsuario) === null) {
            $this->redirigir('gestion_usuarios', ['mensaje' => 'error']);
        }
        if ($usuarioDao->existeCorreo($correo, $idUsuario)) {
            $this->redirigir('gestion_usuarios', ['mensaje' => 'correo_duplicado']);
        }

        $hash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null;
        $exito = $usuarioDao->actualizar(new Usuario($idUsuario, $nombre, $correo, $rol, $hash));
        $this->redirigir('gestion_usuarios', ['mensaje' => $exito ? 'actualizado' : 'error']);
    }

    public function eliminarUsuario(): void {
        $this->exigirRol(Usuario::ROL_ADMIN);
        $this->exigirPost();
        $this->validarCsrf();

        $idUsuario = (int)($_POST['id_usuario'] ?? 0);
        if ($idUsuario <= 0) {
            $this->redirigir('gestion_usuarios', ['mensaje' => 'error']);
        }
        if ($idUsuario === $this->idSesion()) {
            $this->redirigir('gestion_usuarios', ['mensaje' => 'error_propio']);
        }

        $exito = (new UsuarioDAO())->eliminar($idUsuario);
        $this->redirigir('gestion_usuarios', ['mensaje' => $exito ? 'eliminado' : 'error']);
    }

    // ---------------- Perfil propio ----------------
    public function perfil(): void {
        $this->exigirSesion();
        $usuario = (new UsuarioDAO())->buscarPorId($this->idSesion());
        if ($usuario === null) {
            $this->redirigir('logout');
        }
        $this->vista('perfil', ['usuario' => $usuario, 'volverAccion' => $this->accionInicio()]);
    }

    public function actualizarPerfil(): void {
        $this->exigirSesion();
        $this->exigirPost();
        $this->validarCsrf();

        $nombre   = trim((string)($_POST['nombre'] ?? ''));
        $correo   = trim((string)($_POST['correo'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        $passwordInvalida = $password !== '' && mb_strlen($password) < self::PASSWORD_MINIMA;
        if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || $passwordInvalida) {
            $this->redirigir('perfil', ['mensaje' => 'datos_invalidos']);
        }

        $usuarioDao = new UsuarioDAO();
        if ($usuarioDao->existeCorreo($correo, $this->idSesion())) {
            $this->redirigir('perfil', ['mensaje' => 'correo_duplicado']);
        }

        $hash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null;
        $exito = $usuarioDao->actualizarPerfil(new Usuario($this->idSesion(), $nombre, $correo, $this->rolSesion(), $hash));

        if ($exito) {
            $_SESSION['nombre'] = $nombre;
        }
        $this->redirigir('perfil', ['mensaje' => $exito ? 'actualizado' : 'error']);
    }

    private function datosValidos(string $nombre, string $correo, string $rol): bool {
        return $nombre !== ''
            && filter_var($correo, FILTER_VALIDATE_EMAIL) !== false
            && in_array($rol, Usuario::ROLES, true);
    }
}
