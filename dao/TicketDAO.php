<?php
require_once __DIR__ . '/BaseDAO.php';
require_once __DIR__ . '/../model/Ticket.php';

/**
 * Solo acceso a datos: guarda y lee. Las reglas de negocio (SLA, clasificación,
 * permisos) viven en el modelo y en los controladores.
 */
class TicketDAO extends BaseDAO {

    /** Último comentario de cada ticket (evita una consulta por fila en las vistas). */
    private const SQL_ULTIMO_COMENTARIO =
        "(SELECT c.id_usuario FROM ticket_comentarios c WHERE c.id_ticket = t.id ORDER BY c.id DESC LIMIT 1) AS ultimo_comentario_usuario";

    private const SQL_BASE =
        "SELECT t.*, u.nombre AS nombre_usuario, tec.nombre AS nombre_tecnico, " . self::SQL_ULTIMO_COMENTARIO . "
         FROM tickets t
         JOIN usuarios u ON t.id_usuario = u.id_usuario
         LEFT JOIN usuarios tec ON t.id_tecnico = tec.id_usuario";

    public function guardar(Ticket $ticket): bool {
        return $this->ejecutar(
            "INSERT INTO tickets (id_usuario, correo, asunto, descripcion, codigo_barras, archivo_adjunto, estado, tipo, tiempo_resolucion)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $ticket->getIdUsuario(), $ticket->getCorreo(), $ticket->getAsunto(), $ticket->getDescripcion(),
                $ticket->getCodigoBarras(), $ticket->getArchivoAdjunto(), $ticket->getEstadoActual(),
                $ticket->getTipo(), $ticket->calcularTiempoResolucion(),
            ]
        );
    }

    public function buscarPorId(int $idTicket): ?array {
        $filas = $this->consultar(self::SQL_BASE . " WHERE t.id = ?", [$idTicket]);
        return $filas[0] ?? null;
    }

    public function listarPorUsuario(int $idUsuario, array $filtros = []): array {
        $parametros = [$idUsuario];
        $sql = self::SQL_BASE . " WHERE t.id_usuario = ?" . $this->condicionesFiltro($filtros, $parametros) . " ORDER BY t.id DESC";
        return $this->consultar($sql, $parametros);
    }

    public function listarTodos(array $filtros = []): array {
        $parametros = [];
        $sql = self::SQL_BASE . " WHERE 1=1" . $this->condicionesFiltro($filtros, $parametros) . " ORDER BY t.id DESC";
        return $this->consultar($sql, $parametros);
    }

    public function listarPorTecnico(int $idTecnico, array $filtros = []): array {
        $parametros = [$idTecnico];
        $sql = self::SQL_BASE . " WHERE t.id_tecnico = ?" . $this->condicionesFiltro($filtros, $parametros) . " ORDER BY t.id DESC";
        return $this->consultar($sql, $parametros);
    }

    public function actualizarEstado(int $idTicket, string $estado): bool {
        return $this->ejecutar("UPDATE tickets SET estado = ? WHERE id = ?", [$estado, $idTicket]);
    }

    public function actualizarClasificacion(int $idTicket, string $estado, string $tipo, string $tiempoResolucion, ?string $fechaLimite): bool {
        return $this->ejecutar(
            "UPDATE tickets SET estado = ?, tipo = ?, tiempo_resolucion = ?, fecha_limite = ? WHERE id = ?",
            [$estado, $tipo, $tiempoResolucion, $fechaLimite, $idTicket]
        );
    }

    public function asignarTecnico(int $idTicket, ?int $idTecnico): bool {
        return $this->ejecutar("UPDATE tickets SET id_tecnico = ? WHERE id = ?", [$idTecnico, $idTicket]);
    }

    public function listarComentarios(int $idTicket): array {
        return $this->consultar(
            "SELECT c.*, u.nombre AS nombre_usuario, u.rol
             FROM ticket_comentarios c
             JOIN usuarios u ON c.id_usuario = u.id_usuario
             WHERE c.id_ticket = ? ORDER BY c.id ASC",
            [$idTicket]
        );
    }

    public function agregarComentario(int $idTicket, int $idUsuario, string $mensaje): bool {
        return $this->ejecutar(
            "INSERT INTO ticket_comentarios (id_ticket, id_usuario, mensaje) VALUES (?, ?, ?)",
            [$idTicket, $idUsuario, $mensaje]
        );
    }
        // ---------- Estadísticas del dashboard ----------

    /** @return array<string,int> ej. ['Abierto' => 4, 'Finalizado' => 2] */
    public function contarPorEstado(): array {
        return $this->contarAgrupado("SELECT estado AS etiqueta, COUNT(*) AS total FROM tickets GROUP BY estado");
    }

    public function contarPorTipo(): array {
        return $this->contarAgrupado("SELECT tipo AS etiqueta, COUNT(*) AS total FROM tickets GROUP BY tipo");
    }

    public function contarPorTecnico(): array {
        return $this->contarAgrupado(
            "SELECT COALESCE(u.nombre, 'Sin asignar') AS etiqueta, COUNT(*) AS total
             FROM tickets t LEFT JOIN usuarios u ON t.id_tecnico = u.id_usuario
             WHERE t.estado <> 'Finalizado'
             GROUP BY etiqueta ORDER BY total DESC"
        );
    }

    public function contarCriticosAbiertos(): int {
        $filas = $this->consultar(
            "SELECT COUNT(*) AS total FROM tickets
             WHERE tiempo_resolucion LIKE '2 horas%' AND estado <> 'Finalizado'"
        );
        return (int)$filas[0]['total'];
    }

    private function contarAgrupado(string $sql): array {
        $resultado = [];
        foreach ($this->consultar($sql) as $fila) {
            $resultado[$fila['etiqueta']] = (int)$fila['total'];
        }
        return $resultado;
    }

    /** Arma las condiciones opcionales (usuario, estado, fechas) con parámetros preparados. */
    private function condicionesFiltro(array $filtros, array &$parametros): string {
        $sql = '';
        if (!empty($filtros['usuario'])) {
            $sql .= " AND u.nombre LIKE ?";
            $parametros[] = '%' . $filtros['usuario'] . '%';
        }
        if (!empty($filtros['estado'])) {
            $sql .= " AND t.estado = ?";
            $parametros[] = $filtros['estado'];
        }
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(t.fecha_registro) >= ?";
            $parametros[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(t.fecha_registro) <= ?";
            $parametros[] = $filtros['fecha_fin'];
        }
        return $sql;
    }
}
