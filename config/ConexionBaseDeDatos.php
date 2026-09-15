<?php

// UpperCamelCase para la clase
class ConexionBaseDeDatos {
    
    // ENCAPSULAMIENTO: Propiedades privadas en lowerCamelCase
    private string $host = "localhost";
    private string $usuario = "root"; 
    private string $contrasena = ""; 
    private string $nombreBaseDeDatos = "gestion_tickets"; 
    private ?PDO $conexion = null;

    // lowerCamelCase para el método
    public function obtenerConexion(): ?PDO {
        // Solo creamos la conexión si no existe una previa (Patrón Singleton básico)
        if ($this->conexion === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->nombreBaseDeDatos};charset=utf8";
                $this->conexion = new PDO($dsn, $this->usuario, $this->contrasena);
                
                // Le decimos a PDO que nos muestre los errores si algo falla en SQL
                $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $error) {
                // Si XAMPP está apagado o el nombre de la BD está mal, mostrará esto
                echo "Error de conexión: " . $error->getMessage();
            }
        }
        return $this->conexion;
    }
}
?>