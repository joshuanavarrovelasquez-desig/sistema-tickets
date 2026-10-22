<?php
require_once __DIR__ . '/../config/ConexionBaseDeDatos.php';

class GestorDeTickets {
    private PDO $conexionDb;

    public function __construct() {
        $conexion = new ConexionBaseDeDatos();
        $this->conexionDb = $conexion->obtenerConexion();
    }

    public function guardarTicket(Ticket $nuevoTicket, string $tipoTicket): bool {
        $sql = "INSERT INTO tickets (id_usuario, correo, asunto, descripcion, codigo_barras, archivo_adjunto, estado, tipo, tiempo_resolucion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        try {
            $sentencia = $this->conexionDb->prepare($sql);
            $sentencia->execute([
                $nuevoTicket->getIdUsuario(),
                $nuevoTicket->getCorreo(),
                $nuevoTicket->getAsunto(),
                $nuevoTicket->getDescripcion(),
                $nuevoTicket->getCodigoBarras(),
                $nuevoTicket->getArchivoAdjunto(),
                $nuevoTicket->getEstadoActual(),
                $tipoTicket,
                $nuevoTicket->calcularTiempoResolucion()
            ]);
            return true;
        } catch (PDOException $e) {
            echo "Error al guardar en BD: " . $e->getMessage();
            return false;
        }
    }

    public function obtenerHistorialPorUsuario(int $idUsuario): array {
        $sql = "SELECT t.*, u_tec.nombre AS nombre_tecnico 
                FROM tickets t 
                LEFT JOIN usuarios u_tec ON t.id_tecnico = u_tec.id_usuario 
                WHERE t.id_usuario = ? 
                ORDER BY t.id DESC";
        $stmt = $this->conexionDb->prepare($sql);
        $stmt->execute([$idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTodosLosTickets(): array {
        // Añadimos t.id_tecnico a la consulta SELECT para que el panel administrativo lo reconozca
        $sql = "SELECT t.id, t.tipo, t.asunto, t.descripcion, t.codigo_barras, t.archivo_adjunto, t.estado, t.tiempo_resolucion, t.correo, t.fecha_registro, t.id_tecnico, u.nombre as nombre_usuario 
                FROM tickets t 
                JOIN usuarios u ON t.id_usuario = u.id_usuario 
                ORDER BY t.id DESC";
        $sentencia = $this->conexionDb->prepare($sql);
        $sentencia->execute();
        return $sentencia->fetchAll(PDO::FETCH_ASSOC); 
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

    // Nuevo: Método para que el Admin actualice Tipo ITIL y Estado a la vez
    public function actualizarTicketAdmin(int $idTicket, string $nuevoEstado, string $nuevoTipo): bool {
        $sql = "UPDATE tickets SET estado = ?, tipo = ? WHERE id = ?";
        try {
            $stmt = $this->conexionDb->prepare($sql);
            return $stmt->execute([$nuevoEstado, $nuevoTipo, $idTicket]);
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

    // Actualizado: Ordena por tiempo de resolución (SLA) ascendente para dar máxima prioridad a los más urgentes
    public function obtenerTicketsPorTecnico(int $idTecnico): array {
        $sql = "SELECT t.*, u.nombre as nombre_usuario, u.correo 
                FROM tickets t 
                JOIN usuarios u ON t.id_usuario = u.id_usuario 
                WHERE t.id_tecnico = ? 
                ORDER BY t.tiempo_resolucion ASC, t.id DESC";
        $stmt = $this->conexionDb->prepare($sql);
        $stmt->execute([$idTecnico]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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