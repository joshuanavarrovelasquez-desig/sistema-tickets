<?php
require_once __DIR__ . '/../config/ConexionBaseDeDatos.php';

class GestorDeReportes {
    private PDO $conexionDb;

    public function __construct() {
        $conexion = new ConexionBaseDeDatos();
        $this->conexionDb = $conexion->obtenerConexion();
    }

    // Método que recibe un arreglo de filtros y arma la consulta dinámicamente
    public function obtenerTicketsFiltrados(array $filtros): array {
        $sql = "SELECT t.id, t.tipo, t.asunto, t.descripcion, t.codigo_barras, t.archivo_adjunto, t.estado, t.tiempo_resolucion, t.correo, t.fecha_registro, u.nombre as nombre_usuario 
                FROM tickets t 
                JOIN usuarios u ON t.id_usuario = u.id_usuario 
                WHERE 1=1"; // 'WHERE 1=1' es un truco para añadir "ANDs" dinámicos después
        
        $parametros = [];

        // 1. Filtro por Nombre de Usuario (búsqueda parcial)
        if (!empty($filtros['usuario'])) {
            $sql .= " AND u.nombre LIKE ?";
            $parametros[] = '%' . $filtros['usuario'] . '%';
        }
        
        // 2. Filtro por Estado (Abierto, En proceso, Finalizado)
        if (!empty($filtros['estado'])) {
            $sql .= " AND t.estado = ?";
            $parametros[] = $filtros['estado'];
        }
        
        // 3. Filtro por Rango de Fechas
        if (!empty($filtros['fecha_inicio']) && !empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(t.fecha_registro) BETWEEN ? AND ?";
            $parametros[] = $filtros['fecha_inicio'];
            $parametros[] = $filtros['fecha_fin'];
        } elseif (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(t.fecha_registro) >= ?";
            $parametros[] = $filtros['fecha_inicio'];
        } elseif (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(t.fecha_registro) <= ?";
            $parametros[] = $filtros['fecha_fin'];
        }

        $sql .= " ORDER BY t.id DESC";

        $sentencia = $this->conexionDb->prepare($sql);
        $sentencia->execute($parametros);
        return $sentencia->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>