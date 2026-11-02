<?php
/**
 * Utilidades comunes de los controladores: sesión, permisos por rol,
 * CSRF, redirecciones y renderizado de vistas.
 * Las vistas NUNCA se abren directo: siempre las incluye un controlador.
 */
abstract class BaseController {

    // ---------- Sesión y permisos ----------
    protected function idSesion(): int {
        return (int)($_SESSION['id_usuario'] ?? 0);
    }

    protected function rolSesion(): string {
        return (string)($_SESSION['rol'] ?? '');
    }

    protected function exigirSesion(): void {
        if (!isset($_SESSION['id_usuario'])) {
            $this->redirigir('vista_login');
        }
    }

    protected function exigirRol(string ...$rolesPermitidos): void {
        $this->exigirSesion();
        if (!in_array($this->rolSesion(), $rolesPermitidos, true)) {
            $this->redirigir($this->accionInicio());
        }
    }

    protected function exigirPost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigir($this->accionInicio());
        }
    }

    /** Panel de inicio según el rol del usuario. */
    protected function accionInicio(): string {
        return match ($this->rolSesion()) {
            Usuario::ROL_ADMIN   => 'panel_admin',
            Usuario::ROL_TECNICO => 'panel_tecnico',
            Usuario::ROL_CLIENTE => 'listar_tickets',
            default              => 'vista_login',
        };
    }

    // ---------- CSRF ----------
    public static function tokenCsrf(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function tokenCsrfValido(): bool {
        return isset($_SESSION['csrf_token'], $_POST['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token']);
    }

    protected function validarCsrf(): void {
        if (!$this->tokenCsrfValido()) {
            http_response_code(419);
            exit('La sesión del formulario expiró. Vuelve atrás, recarga la página e inténtalo de nuevo.');
        }
    }

    // ---------- Respuesta ----------
    protected function redirigir(string $accion, array $parametros = []): never {
        header('Location: index.php?' . http_build_query(['accion' => $accion] + $parametros));
        exit();
    }

    protected function vista(string $nombre, array $datos = []): void {
        $csrf = self::tokenCsrf();
        $sesionIdUsuario = $this->idSesion();
        $sesionNombre = (string)($_SESSION['nombre'] ?? '');
        $sesionRol = $this->rolSesion();
        extract($datos, EXTR_SKIP);
        require __DIR__ . '/../views/' . $nombre . '.php';
    }

    // ---------- Entradas ----------
    /** Lee y valida los filtros (usuario, estado, fechas) de la URL. */
    protected function filtrosDesdeUrl(): array {
        $estado = $_GET['estado'] ?? '';
        $fechaValida = static function (string $f): string {
            $d = DateTime::createFromFormat('Y-m-d', $f);
            return ($d && $d->format('Y-m-d') === $f) ? $f : '';
        };
        return [
            'usuario'      => mb_substr(trim((string)($_GET['usuario'] ?? '')), 0, 100),
            'estado'       => in_array($estado, Ticket::ESTADOS, true) ? $estado : '',
            'fecha_inicio' => $fechaValida((string)($_GET['fecha_inicio'] ?? '')),
            'fecha_fin'    => $fechaValida((string)($_GET['fecha_fin'] ?? '')),
        ];
    }
}
