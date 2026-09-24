<?php
require_once __DIR__ . '/../config/ConexionBaseDeDatos.php';

class GestorDeTickets {
    
    private PDO $conexionDb;

    public function __construct() {
        // Instanciamos la conexión directamente al crear el gestor
        $conexion = new ConexionBaseDeDatos();
        $this->conexionDb = $conexion->obtenerConexion();
    }

    // 1. Método para crear un nuevo ticket (Usado por los clientes)
    public function guardarTicket(Ticket $nuevoTicket, string $tipoTicket): bool {
        $sql = "INSERT INTO tickets (id_usuario, estado, tipo, tiempo_resolucion) VALUES (?, ?, ?, ?)";
        try {
            $sentencia = $this->conexionDb->prepare($sql);
            $sentencia->execute([
                $nuevoTicket->getIdUsuario(), 
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

    // 2. Método para ver el historial personal (Usado por listar_tickets.php)
    public function obtenerHistorialPorUsuario(int $idUsuario): array {
        // Usamos 'ORDER BY id DESC' que corregimos antes
        $sql = "SELECT id, tipo, estado, tiempo_resolucion FROM tickets WHERE id_usuario = ? ORDER BY id DESC";
        $sentencia = $this->conexionDb->prepare($sql);
        $sentencia->execute([$idUsuario]);
        return $sentencia->fetchAll(PDO::FETCH_ASSOC); 
    }

    // 3. Método para ver TODOS los tickets (Usado por panel_admin.php)
    public function obtenerTodosLosTickets(): array {
        $sql = "SELECT t.id, t.tipo, t.estado, t.tiempo_resolucion, u.nombre as nombre_usuario 
                FROM tickets t 
                JOIN usuarios u ON t.id_usuario = u.id_usuario 
                ORDER BY t.id DESC";
        $sentencia = $this->conexionDb->prepare($sql);
        $sentencia->execute();
        return $sentencia->fetchAll(PDO::FETCH_ASSOC); 
    }

    // 4. EL MÉTODO QUE FALTABA: Actualiza el estado en la base de datos
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