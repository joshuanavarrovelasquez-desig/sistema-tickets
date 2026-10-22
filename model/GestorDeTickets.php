<?php
require_once __DIR__ . '/../config/ConexionBaseDeDatos.php';

class GestorDeTickets {
    private PDO $conexionDb;

    public function __construct() {
        $conexion = new ConexionBaseDeDatos();
        $this->conexionDb = $conexion->obtenerConexion();
    }

    public function guardarTicket(Ticket $nuevoTicket, string $tipoTicket): bool {
        // Si el ticket se crea como "Por Clasificar", el SLA queda pendiente
        $tiempoResolucion = ($tipoTicket === 'Por Clasificar') ? 'Pendiente de clasificación' : $nuevoTicket->calcularTiempoResolucion();
        
        $sql = "INSERT INTO tickets (id_usuario, correo, asunto, descripcion, codigo_barras, archivo_adjunto, estado, tipo, tiempo_resolucion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        try {
            $sentencia = $this->conexionDb->prepare($sql);
            $sentencia.execute([
                $nuevoTicket->getIdUsuario(),
                $nuevoTicket->getCorreo(),
                $nuevoTicket->getAsunto(),
                $nuevoTicket->getDescripcion(),
                $nuevoTicket->getCodigoBarras(),
                $nuevoTicket->getArchivoAdjunto(),
                $nuevoTicket->getEstadoActual(),
                $tipoTicket,
                $tiempoResolucion
            ]);
            return true;
        } catch (PDOException $e) {
            echo "Error al guardar en BD: " . $e->getMessage();
            return false;
        }
    }

    private function calcularSlaSegunTipo(string $tipo, string $asunto, string $descripcion): string {
        if ($tipo === 'Por Clasificar') {
            return 'Pendiente de clasificación';
        }
        $texto = strtolower($asunto . ' ' . $descripcion);
        if ($tipo === 'Incidente') {
            if (str_contains($texto, 'crítico') || str_contains($texto, 'critico') || str_contains($texto, 'urgente') || str_contains($texto, 'placa') || str_contains($texto, 'caida') || str_contains($texto, 'sin internet')) {
                return '2 horas (SLA Crítico)';
            }
            return '24 horas (SLA Normal)';
        } elseif ($tipo === 'Requerimiento') {
            return '24 horas (SLA Normal)';
        }
        return 'Pendiente de clasificación';
    }

    // Historial del cliente con filtros opcionales de búsqueda
    public function obtenerHistorialPorUsuarioFiltrado(int $idUsuario, ?string $estado = null, ?string $fechaInicio = null, ?string $fechaFin = null): array {
        $sql = "SELECT t.*, u_tec.nombre AS nombre_tecnico 
                FROM tickets t 
                LEFT JOIN usuarios u_tec ON t.id_tecnico = u_tec.id_usuario 
                WHERE t.id_usuario = ?";
        
        $params = [$idUsuario];

        if (!empty($estado)) {
            $sql .= " AND t.estado = ?";
            $params[] = $estado;
        }
        if (!empty($fechaInicio)) {
            $sql .= " AND DATE(t.fecha_registro) >= ?";
            $params[] = $fechaInicio;
        }
        if (!empty($fechaFin)) {
            $sql .= " AND DATE(t.fecha_registro) <= ?";
            $params[] = $fechaFin;
        }

        $sql .= " ORDER BY t.id DESC";
        $stmt = $this->conexionDb->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mantener compatibilidad si se llama sin filtros
    public function obtenerHistorialPorUsuario(int $idUsuario): array {
        return $this->obtenerHistorialPorUsuarioFiltrado($idUsuario);
    }

    // Obtener todos los tickets con filtros opcionales de búsqueda (para el Admin)
    public function obtenerTodosLosTicketsFiltrados(?string $nombreUsuario = null, ?string $estado = null, ?string $fechaInicio = null, ?string $fechaFin = null): array {
        $sql = "SELECT t.id, t.tipo, t.asunto, t.descripcion, t.codigo_barras, t.archivo_adjunto, t.estado, t.tiempo_resolucion, t.correo, t.fecha_registro, t.id_tecnico, u.nombre as nombre_usuario 
                FROM tickets t 
                JOIN usuarios u ON t.id_usuario = u.id_usuario 
                WHERE 1=1";
        
        $params = [];

        if (!empty($nombreUsuario)) {
            $sql .= " AND u.nombre LIKE ?";
            $params[] = "%" . $nombreUsuario . "%";
        }
        if (!empty($estado)) {
            $sql .= " AND t.estado = ?";
            $params[] = $estado;
        }
        if (!empty($fechaInicio)) {
            $sql .= " AND DATE(t.fecha_registro) >= ?";
            $params[] = $fechaInicio;
        }
        if (!empty($fechaFin)) {
            $sql .= " AND DATE(t.fecha_registro) <= ?";
            $params[] = $fechaFin;
        }

        $sql .= " ORDER BY t.id DESC";
        $sentencia = $this->conexionDb->prepare($sql);
        $sentencia->execute($params);
        return $sentencia->fetchAll(PDO::FETCH_ASSOC); 
    }

    public function obtenerTodosLosTickets(): array {
        return $this->obtenerTodosLosTicketsFiltrados();
    }

    public function actualizarEstadoTicket(int $idTicket, string $nuevoEstado): bool {
        $sql = "UPDATE tickets SET estado = ? WHERE id = ?";
        try {
            $stmt = $this->conexionDb->prepare($sql);
            return $stmt->execute([$nuevoEstado, $idTicket]);
        } catch (PDOException $e) {
            echo "Error al actualizar: " . $e->getMessage();
            return false;
        }
    }

    public function actualizarTicketAdmin(int $idTicket, string $nuevoEstado, string $nuevoTipo): bool {
        // Obtenemos asunto y descripción para recalcular el SLA al cambiar el tipo ITIL
        $sqlInfo = "SELECT asunto, descripcion FROM tickets WHERE id = ?";
        $stmtInfo = $this->conexionDb->prepare($sqlInfo);
        $stmtInfo->execute([$idTicket]);
        $ticketInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

        $tiempoResolucion = 'Pendiente de clasificación';
        if ($ticketInfo) {
            $tiempoResolucion = $this->calcularSlaSegunTipo($nuevoTipo, $ticketInfo['asunto'], $ticketInfo['descripcion']);
        }

        $sql = "UPDATE tickets SET estado = ?, tipo = ?, tiempo_resolucion = ? WHERE id = ?";
        try {
            $stmt = $this->conexionDb->prepare($sql);
            return $stmt->execute([$nuevoEstado, $nuevoTipo, $tiempoResolucion, $idTicket]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerTecnicos(): array {
        $sql = "SELECT id_usuario, nombre FROM usuarios WHERE rol = 'tecnico'";
        $stmt = $this->conexionDb->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function asignarTecnicoTicket(int $idTicket, ?int $idTecnico): bool {
        $sql = "UPDATE tickets SET id_tecnico = ? WHERE id = ?";
        try {
            $stmt = $this->conexionDb->prepare($sql);
            return $stmt->execute([$idTecnico, $idTicket]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Tickets asignados al técnico con filtros opcionales de búsqueda
    public function obtenerTicketsPorTecnicoFiltrados(int $idTecnico, ?string $nombreUsuario = null, ?string $estado = null, ?string $fechaInicio = null, ?string $fechaFin = null): array {
        $sql = "SELECT t.*, u.nombre as nombre_usuario, u.correo 
                FROM tickets t 
                JOIN usuarios u ON t.id_usuario = u.id_usuario 
                WHERE t.id_tecnico = ?";
        
        $params = [$idTecnico];

        if (!empty($nombreUsuario)) {
            $sql .= " AND u.nombre LIKE ?";
            $params[] = "%" . $nombreUsuario . "%";
        }
        if (!empty($estado)) {
            $sql .= " AND t.estado = ?";
            $params[] = $estado;
        }
        if (!empty($fechaInicio)) {
            $sql .= " AND DATE(t.fecha_registro) >= ?";
            $params[] = $fechaInicio;
        }
        if (!empty($fechaFin)) {
            $sql .= " AND DATE(t.fecha_registro) <= ?";
            $params[] = $fechaFin;
        }

        $sql .= " ORDER BY t.tiempo_resolucion ASC, t.id DESC";
        $stmt = $this->conexionDb->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTicketsPorTecnico(int $idTecnico): array {
        return $this->obtenerTicketsPorTecnicoFiltrados($idTecnico);
    }

    public function obtenerComentariosPorTicket(int $idTicket): array {
        $sql = "SELECT c.*, u.nombre as nombre_usuario, u.rol 
                FROM ticket_comentarios c 
                JOIN usuarios u ON c.id_usuario = u.id_usuario 
                WHERE c.id_ticket = ? 
                ORDER BY c.id ASC";
        $stmt = $this->conexionDb->prepare($sql);
        $stmt->execute([$idTicket]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function agregarComentario(int $idTicket, int $idUsuario, string $mensaje): bool {
        $sql = "INSERT INTO ticket_comentarios (id_ticket, id_usuario, mensaje) VALUES (?, ?, ?)";
        try {
            $stmt = $this->conexionDb->prepare($sql);
            return $stmt->execute([$idTicket, $idUsuario, $mensaje]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerUltimoComentario(int $idTicket): ?array {
        $sql = "SELECT id_usuario, fecha_registro 
                FROM ticket_comentarios 
                WHERE id_ticket = ? 
                ORDER BY id DESC LIMIT 1";
        $stmt = $this->conexionDb->prepare($sql);
        $stmt->execute([$idTicket]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ?: null;
    }
}
?>