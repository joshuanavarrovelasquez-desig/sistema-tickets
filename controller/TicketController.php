<?php
class TicketController extends BaseController {
    private const EXTENSIONES_PERMITIDAS = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'doc', 'docx', 'xls', 'xlsx'];
    private const TAMANO_MAXIMO_ADJUNTO  = 5 * 1024 * 1024; // 5 MB

    // ---------------- Paneles (lectura) ----------------
    public function panelAdmin(): void {
        $this->exigirRol(Usuario::ROL_ADMIN);
        $filtros = $this->filtrosDesdeUrl();

        $this->vista('panel_admin', [
            'todosLosTickets'   => (new TicketDAO())->listarTodos($filtros),
            'tecnicos'          => (new UsuarioDAO())->listarPorRol(Usuario::ROL_TECNICO),
            'usuarioFiltro'     => $filtros['usuario'],
            'estadoFiltro'      => $filtros['estado'],
            'fechaInicioFiltro' => $filtros['fecha_inicio'],
            'fechaFinFiltro'    => $filtros['fecha_fin'],
        ]);
    }
    
        public function dashboard(): void {
        $this->exigirRol(Usuario::ROL_ADMIN);
        $ticketDao = new TicketDAO();

        $porEstado = $ticketDao->contarPorEstado();
        $porTipo   = $ticketDao->contarPorTipo();

        $this->vista('dashboard', [
            'porEstado'        => $porEstado,
            'porTipo'          => $porTipo,
            'porTecnico'       => $ticketDao->contarPorTecnico(),
            'criticos'         => $ticketDao->contarCriticosAbiertos(),
            'totalTickets'     => array_sum($porEstado),
            'porClasificar'    => $porTipo['Por Clasificar'] ?? 0,
            'abiertos'         => $porEstado['Abierto'] ?? 0,
            'enProceso'        => $porEstado['En proceso'] ?? 0,
            'finalizados'      => $porEstado['Finalizado'] ?? 0,
        ]);
    }

    public function panelTecnico(): void {
        $this->exigirRol(Usuario::ROL_TECNICO);
        $filtros = $this->filtrosDesdeUrl();

        $this->vista('panel_tecnico', [
            'misTicketsTecnico' => (new TicketDAO())->listarPorTecnico($this->idSesion(), $filtros),
            'usuarioFiltro'     => $filtros['usuario'],
            'estadoFiltro'      => $filtros['estado'],
            'fechaInicioFiltro' => $filtros['fecha_inicio'],
            'fechaFinFiltro'    => $filtros['fecha_fin'],
        ]);
    }

    public function listarMisTickets(): void {
        $this->exigirRol(Usuario::ROL_CLIENTE);
        $filtros = $this->filtrosDesdeUrl();
        $misTickets = (new TicketDAO())->listarPorUsuario($this->idSesion(), $filtros);

        $enEspera = $enProceso = $finalizados = [];
        foreach ($misTickets as $t) {
            if ($t['estado'] === 'Finalizado') {
                $finalizados[] = $t;
            } elseif ($t['estado'] === 'En proceso' || !empty($t['id_tecnico'])) {
                $enProceso[] = $t;
            } else {
                $enEspera[] = $t;
            }
        }

        $this->vista('listar_tickets', [
            'ticketsEnEspera'    => $enEspera,
            'ticketsEnProceso'   => $enProceso,
            'ticketsFinalizados' => $finalizados,
            'estadoFiltro'       => $filtros['estado'],
            'fechaInicioFiltro'  => $filtros['fecha_inicio'],
            'fechaFinFiltro'     => $filtros['fecha_fin'],
        ]);
    }

    public function formularioCrear(): void {
        $this->exigirSesion();
        $usuario = (new UsuarioDAO())->buscarPorId($this->idSesion());
        $this->vista('crear_ticket', ['correoSugerido' => $usuario ? $usuario->getCorreo() : '']);
    }

    public function verTicket(): void {
        $this->exigirSesion();
        $idTicket = (int)($_GET['id'] ?? 0);
        if ($idTicket <= 0) {
            $this->redirigir($this->accionInicio());
        }

        $ticketDao = new TicketDAO();
        $ticket = $ticketDao->buscarPorId($idTicket);
        if ($ticket === null || !$this->puedeVer($ticket)) {
            http_response_code(404);
            echo "<h3 style='color:red; text-align:center; margin-top:50px;'>Ticket no encontrado o no tienes permisos para visualizarlo.</h3>";
            echo "<div style='text-align:center;'><a href='index.php?accion=" . $this->accionInicio() . "'>Regresar</a></div>";
            return;
        }

        $this->vista('ver_ticket', [
            'ticketActual' => $ticket,
            'comentarios'  => $ticketDao->listarComentarios($idTicket),
            'idTicket'     => $idTicket,
            'volverAccion' => $this->accionInicio(),
        ]);
    }

    // ---------------- Acciones (escritura) ----------------
    public function registrarNuevoTicket(): void {
        $this->exigirSesion();
        $this->exigirPost();
        $this->validarCsrf();

        $correo      = trim((string)($_POST['correo'] ?? ''));
        $asunto      = trim((string)($_POST['asunto'] ?? ''));
        $descripcion = trim((string)($_POST['descripcion'] ?? ''));
        $codigo      = trim((string)($_POST['codigo_barras'] ?? ''));
        $codigoBarras = $codigo !== '' ? $codigo : null;

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || $asunto === '' || $descripcion === '') {
            $this->redirigir('crear_ticket', ['mensaje' => 'datos_invalidos']);
        }

        $adjunto = $this->guardarAdjunto();
        if ($adjunto === false) {
            $this->redirigir('crear_ticket', ['mensaje' => 'adjunto_invalido']);
        }

        // Todo ticket nace "Por Clasificar"; el admin lo clasifica después.
        $ticket = TicketFactory::crear('Por Clasificar', $this->idSesion(), $correo, $asunto, $descripcion, $codigoBarras, $adjunto);
        $exito = (new TicketDAO())->guardar($ticket);

        if ($exito) {
            (new ServicioNotificaciones())->ticketCreado($ticket);
            $this->redirigir($this->accionInicio(), ['mensaje' => 'exito']);
        }
        $this->redirigir('crear_ticket', ['mensaje' => 'error']);
    }

    public function actualizarEstado(): void {
        $this->exigirRol(Usuario::ROL_ADMIN, Usuario::ROL_TECNICO);
        $this->exigirPost();
        $this->validarCsrf();

        $idTicket    = (int)($_POST['id_ticket'] ?? 0);
        $nuevoEstado = (string)($_POST['nuevo_estado'] ?? '');
        $ticketDao   = new TicketDAO();
        $ticket      = $ticketDao->buscarPorId($idTicket);

        if ($ticket === null || !in_array($nuevoEstado, Ticket::ESTADOS, true)) {
            $this->redirigir($this->accionInicio(), ['mensaje' => 'error']);
        }

        if ($this->rolSesion() === Usuario::ROL_ADMIN) {
            $nuevoTipo = (string)($_POST['nuevo_tipo'] ?? '');
            if (!in_array($nuevoTipo, Ticket::TIPOS, true)) {
                $this->redirigir('panel_admin', ['mensaje' => 'error']);
            }
            // La regla de negocio (SLA) la calcula el modelo; el DAO solo guarda el resultado.
            $entidad = TicketFactory::crear(
                $nuevoTipo, (int)$ticket['id_usuario'], $ticket['correo'], $ticket['asunto'],
                $ticket['descripcion'], $ticket['codigo_barras'], $ticket['archivo_adjunto']
            );
            // Fecha límite real: se cuenta desde que se creó el ticket y salta fines de semana y feriados.
            $desde    = new DateTimeImmutable($ticket['fecha_registro']);
            $feriados = (new ServicioFeriados('PE'))->feriadosDesde($desde);
            $limite   = $entidad->calcularFechaLimite($desde, $feriados);

            $exito = $ticketDao->actualizarClasificacion(
                $idTicket, $nuevoEstado, $entidad->getTipo(), $entidad->calcularTiempoResolucion(),
                $limite ? $limite->format('Y-m-d H:i:s') : null
            );
        } else {
            // El técnico solo puede cambiar el estado de los tickets que tiene asignados.
            if (!$this->puedeVer($ticket)) {
                $this->redirigir('panel_tecnico', ['mensaje' => 'sin_permiso']);
            }
            $exito = $ticketDao->actualizarEstado($idTicket, $nuevoEstado);
        }
        if ($exito && $ticket['estado'] !== $nuevoEstado) {
            (new ServicioNotificaciones())->estadoCambiado($ticket, $nuevoEstado);
        }

        $this->redirigir($this->accionInicio(), ['mensaje' => $exito ? 'actualizado' : 'error']);
    }

    public function asignarTecnico(): void {
        $this->exigirRol(Usuario::ROL_ADMIN);
        $this->exigirPost();
        $this->validarCsrf();

        $idTicket  = (int)($_POST['id_ticket'] ?? 0);
        $idTecnico = !empty($_POST['id_tecnico']) ? (int)$_POST['id_tecnico'] : null;

        $ticketDao = new TicketDAO();
        if ($ticketDao->buscarPorId($idTicket) === null) {
            $this->redirigir('panel_admin', ['mensaje' => 'error']);
        }
        if ($idTecnico !== null) {
            $tecnico = (new UsuarioDAO())->buscarPorId($idTecnico);
            if ($tecnico === null || $tecnico->getRol() !== Usuario::ROL_TECNICO) {
                $this->redirigir('panel_admin', ['mensaje' => 'error']);
            }
        }

        $exito = $ticketDao->asignarTecnico($idTicket, $idTecnico);
        if ($exito && $idTecnico !== null) {
            (new ServicioNotificaciones())->tecnicoAsignado($ticketDao->buscarPorId($idTicket), $tecnico);
        }
        $this->redirigir('panel_admin', ['mensaje' => $exito ? 'asignado' : 'error']);
    }

    public function agregarComentario(): void {
        $this->exigirSesion();
        $this->exigirPost();
        $this->validarCsrf();

        $idTicket = (int)($_POST['id_ticket'] ?? 0);
        // El administrador solo observa la conversación.
        if ($this->rolSesion() === Usuario::ROL_ADMIN) {
            $this->redirigir('ver_ticket', ['id' => $idTicket]);
        }

        $ticketDao = new TicketDAO();
        $ticket = $ticketDao->buscarPorId($idTicket);
        if ($ticket === null || !$this->puedeVer($ticket)) {
            $this->redirigir($this->accionInicio(), ['mensaje' => 'sin_permiso']);
        }

        $mensaje = mb_substr(trim((string)($_POST['mensaje'] ?? '')), 0, 2000);
        if ($mensaje !== '' && $ticketDao->agregarComentario($idTicket, $this->idSesion(), $mensaje)) {
            (new ServicioNotificaciones())->comentarioNuevo($ticket, $mensaje, $this->rolSesion());
        }
        $this->redirigir('ver_ticket', ['id' => $idTicket]);
    }

    // ---------------- Privados ----------------
    /** Permisos de acceso a un ticket: admin todos, técnico los asignados, cliente los propios. */
    private function puedeVer(array $ticket): bool {
        return match ($this->rolSesion()) {
            Usuario::ROL_ADMIN   => true,
            Usuario::ROL_TECNICO => (int)$ticket['id_tecnico'] === $this->idSesion(),
            Usuario::ROL_CLIENTE => (int)$ticket['id_usuario'] === $this->idSesion(),
            default              => false,
        };
    }

    /** @return string|null|false nombre guardado, null si no hay archivo, false si es inválido */
    private function guardarAdjunto(): string|null|false {
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $archivo = $_FILES['archivo'];
        if ($archivo['error'] !== UPLOAD_ERR_OK || $archivo['size'] > self::TAMANO_MAXIMO_ADJUNTO) {
            return false;
        }

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::EXTENSIONES_PERMITIDAS, true)) {
            return false;
        }

        $base = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($archivo['name'], PATHINFO_FILENAME));
        $nombreFinal = time() . '_' . ($base !== '' ? $base : 'adjunto') . '.' . $extension;

        $carpeta = __DIR__ . '/../uploads/';
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0755, true);
        }
        return move_uploaded_file($archivo['tmp_name'], $carpeta . $nombreFinal) ? $nombreFinal : false;
    }
}
