<?php
require_once __DIR__ . '/../config/ConexionBaseDeDatos.php';

class GestorDeUsuarios {
    private PDO $conexionDb;

    public function __construct() {
        $conexion = new ConexionBaseDeDatos();
        $this->conexionDb = $conexion->obtenerConexion();
    }

    public function autenticarUsuario(string $correo, string $password): ?array {
        // CAMBIO: Añadimos 'rol' a la lista de campos que le pedimos a MySQL
        $sql = "SELECT id_usuario, nombre, password, rol FROM usuarios WHERE correo = ?";
        
        try {
            $stmt = $this->conexionDb->prepare($sql);
            $stmt->execute([$correo]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Validamos la contraseña
            if ($usuario && $usuario['password'] === $password) {
                return $usuario; 
            }
            return null; 
        } catch (PDOException $e) {
            echo "Error de BD: " . $e->getMessage();
            return null;
        }
    }
}
?>