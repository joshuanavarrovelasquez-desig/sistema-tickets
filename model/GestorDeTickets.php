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
            
            // CORRECCIÓN: Usamos la flecha (->) en lugar del punto (.)
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
        $sql = "SELECT id, tipo, asunto, descripcion, codigo_barras, archivo_adjunto, estado, tiempo_resolucion FROM tickets WHERE id_usuario = ? ORDER BY id DESC";
        $sentencia = $this->conexionDb->prepare($sql);
        $sentencia->execute([$idUsuario]);
        return $sentencia->fetchAll(PDO::FETCH_ASSOC); 
    }

    public function obtenerTodosLosTickets(): array {
        // Añadimos t.fecha_registro a la consulta
        $sql = "SELECT t.id, t.tipo, t.asunto, t.descripcion, t.codigo_barras, t.archivo_adjunto, t.estado, t.tiempo_resolucion, t.correo, t.fecha_registro, u.nombre as nombre_usuario 
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
}
?>