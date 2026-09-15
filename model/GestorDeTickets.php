<?php
require_once __DIR__ . '/../config/ConexionBaseDeDatos.php';

class GestorDeTickets {
    
    private PDO $conexionDb;

    public function __construct() {
        // Instanciamos la conexión directamente al crear el gestor
        $conexion = new ConexionBaseDeDatos();
        $this->conexionDb = $conexion->obtenerConexion();
    }

    // Este método recibe CUALQUIER tipo de Ticket (sea Incidente o Requerimiento)
    public function guardarTicket(Ticket $nuevoTicket, string $tipoTicket): bool {
        
        $sql = "INSERT INTO tickets (usuario_solicitante, estado, tipo, tiempo_resolucion) VALUES (?, ?, ?, ?)";
        
        try {
            $sentencia = $this->conexionDb->prepare($sql);
            
            // Extraemos los datos del objeto usando sus métodos
            $sentencia->execute([
                $nuevoTicket->getUsuarioSolicitante(), // En PHP 8, si es protected, a veces requiere un getter, pero asumiremos visibilidad de paquete si no, crea un getUsuario()
                $nuevoTicket->getEstadoActual(),
                $tipoTicket,
                $nuevoTicket->calcularTiempoResolucion() // Ejecutará el método polimórfico correspondiente
            ]);
            
            return true;
        } catch (PDOException $e) {
            echo "Error al guardar en BD: " . $e->getMessage();
            return false;
        }
    }
}
?>