<?php
/** Decide a quién y qué avisar en cada evento del sistema. */
class ServicioNotificaciones {
    private ServicioCorreo $correo;
    private UsuarioDAO $usuarioDao;
    private string $urlBase;

    public function __construct() {
        $archivo = __DIR__ . '/../config/config.local.php';
        $todo = is_file($archivo) ? require $archivo : [];
        $this->urlBase = rtrim($todo['url_base'] ?? '', '/');
        $this->correo = new ServicioCorreo();
        $this->usuarioDao = new UsuarioDAO();
    }

    /** Ticket nuevo: confirma al que lo creó y avisa a los administradores. */
    public function ticketCreado(Ticket $ticket): void {
        $this->correo->enviar(
            $ticket->getCorreo(),
            'Recibimos tu ticket: ' . $ticket->getAsunto(),
            $this->plantilla('Ticket registrado', 'Recibimos tu solicitud <strong>' . $this->e($ticket->getAsunto())
                . '</strong>. Un administrador la clasificará y te avisaremos cuando avance.')
        );

        foreach ($this->usuarioDao->listarPorRol(Usuario::ROL_ADMIN) as $admin) {
            $this->correo->enviar(
                $admin->getCorreo(),
                'Nuevo ticket por clasificar',
                $this->plantilla('Nuevo ticket', 'Se registró <strong>' . $this->e($ticket->getAsunto())
                    . '</strong> y está pendiente de clasificación.')
            );
        }
    }

    /** Asignación: avisa al técnico elegido. */
    public function tecnicoAsignado(array $ticket, Usuario $tecnico): void {
        $this->correo->enviar(
            $tecnico->getCorreo(),
            'Se te asignó el ticket #' . (int)$ticket['id'],
            $this->plantilla('Ticket asignado', 'Se te asignó <strong>' . $this->e($ticket['asunto'])
                . '</strong> (' . $this->e($ticket['tipo']) . ').', $this->enlaceTicket((int)$ticket['id']))
        );
    }

    /** Cambio de estado: avisa a quien creó el ticket. */
    public function estadoCambiado(array $ticket, string $nuevoEstado): void {
        $this->correo->enviar(
            $ticket['correo'],
            'Tu ticket #' . (int)$ticket['id'] . ' ahora está: ' . $nuevoEstado,
            $this->plantilla('Estado actualizado', 'Tu ticket <strong>' . $this->e($ticket['asunto'])
                . '</strong> cambió a <strong>' . $this->e($nuevoEstado) . '</strong>.', $this->enlaceTicket((int)$ticket['id']))
        );
    }

    /** Comentario: si escribe el cliente avisa al técnico; si escribe el técnico avisa al cliente. */
    public function comentarioNuevo(array $ticket, string $mensaje, string $rolAutor): void {
        $destinatario = null;
        if ($rolAutor === Usuario::ROL_CLIENTE && !empty($ticket['id_tecnico'])) {
            $tecnico = $this->usuarioDao->buscarPorId((int)$ticket['id_tecnico']);
            $destinatario = $tecnico ? $tecnico->getCorreo() : null;
        } elseif ($rolAutor === Usuario::ROL_TECNICO) {
            $destinatario = $ticket['correo'];
        }
        if ($destinatario === null) {
            return;
        }
        $this->correo->enviar(
            $destinatario,
            'Nuevo mensaje en el ticket #' . (int)$ticket['id'],
            $this->plantilla('Nuevo mensaje', '<em>' . nl2br($this->e(mb_substr($mensaje, 0, 300))) . '</em>',
                $this->enlaceTicket((int)$ticket['id']))
        );
    }

    // ---------- Ayudas privadas ----------
    private function e(string $texto): string {
        return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    }

    private function enlaceTicket(int $idTicket): string {
        return $this->urlBase . '/index.php?accion=ver_ticket&id=' . $idTicket;
    }

    private function plantilla(string $titulo, string $cuerpo, ?string $enlace = null): string {
        $boton = $enlace
            ? '<p><a href="' . $this->e($enlace) . '" style="background:#0056b3;color:#fff;padding:10px 16px;text-decoration:none;border-radius:5px;">Abrir ticket</a></p>'
            : '';
        return '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;border:1px solid #ddd;border-radius:8px;overflow:hidden;">'
            . '<div style="background:#343a40;color:#fff;padding:14px;"><strong>' . $this->e($titulo) . '</strong></div>'
            . '<div style="padding:18px;color:#333;"><p>' . $cuerpo . '</p>' . $boton . '</div>'
            . '<div style="background:#f4f4f9;color:#777;padding:10px;font-size:12px;">Sistema de Tickets</div></div>';
    }
}
