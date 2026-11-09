<?php
/**
 * FRONT CONTROLLER: único punto de entrada de la aplicación.
 * Flujo:  Navegador → index.php → Controlador → (Modelo + DAO/PDO → BD) → Vista
 */
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
define('APP_INICIADA', true);

// Carga automática de clases (controller, model, dao, config)
spl_autoload_register(function (string $clase): void {
    foreach (['controller', 'model', 'dao', 'config', 'service'] as $carpeta) {
        $archivo = __DIR__ . "/$carpeta/$clase.php";
        if (is_file($archivo)) {
            require_once $archivo;
            return;
        }
    }
});

// accion => [Controlador, método]
$rutas = [
    // Sesión
    'vista_login'        => ['AuthController', 'mostrarLogin'],
    'login'              => ['AuthController', 'procesarLogin'],
    'logout'             => ['AuthController', 'cerrarSesion'],
    // Tickets
    'panel_admin'        => ['TicketController', 'panelAdmin'],
    'dashboard'          => ['TicketController', 'dashboard'],
    'panel_tecnico'      => ['TicketController', 'panelTecnico'],
    'listar_tickets'     => ['TicketController', 'listarMisTickets'],
    'crear_ticket'       => ['TicketController', 'formularioCrear'],
    'crear'              => ['TicketController', 'registrarNuevoTicket'],
    'ver_ticket'         => ['TicketController', 'verTicket'],
    'actualizar_estado'  => ['TicketController', 'actualizarEstado'],
    'asignar_tecnico'    => ['TicketController', 'asignarTecnico'],
    'agregar_comentario' => ['TicketController', 'agregarComentario'],
    // Usuarios
    'gestion_usuarios'   => ['UsuarioController', 'gestionar'],
    'crear_usuario'      => ['UsuarioController', 'registrarNuevoUsuario'],
    'actualizar_usuario' => ['UsuarioController', 'actualizarUsuarioAdmin'],
    'eliminar_usuario'   => ['UsuarioController', 'eliminarUsuario'],
    'perfil'             => ['UsuarioController', 'perfil'],
    'actualizar_perfil'  => ['UsuarioController', 'actualizarPerfil'],
    // Reportes
    'exportar'           => ['ReporteController', 'procesarExportacion'],
];

$accion = $_GET['accion'] ?? 'vista_login';
if (!isset($rutas[$accion])) {
    header('Location: index.php?accion=vista_login');
    exit();
}

try {
    [$clase, $metodo] = $rutas[$accion];
    (new $clase())->$metodo();
} catch (Throwable $error) {
    error_log('[APP] ' . $error->getMessage() . ' en ' . $error->getFile() . ':' . $error->getLine());
    http_response_code(500);
    $detalle = ($error instanceof PDOException)
        ? 'No se pudo conectar o consultar la base de datos. Verifica que MySQL (XAMPP) esté encendido y que exista la base "gestion_tickets".'
        : 'Ocurrió un error inesperado. Revisa el log de errores de PHP.';
    echo '<h3 style="font-family:Arial; color:#b02a37; text-align:center; margin-top:50px;">' . htmlspecialchars($detalle) . '</h3>';
    echo '<p style="font-family:monospace; text-align:center; color:#555;">' . htmlspecialchars($error->getMessage() . ' — ' . basename($error->getFile()) . ':' . $error->getLine()) . '</p>';
}