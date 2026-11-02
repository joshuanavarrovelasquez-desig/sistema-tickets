<?php
/**
 * Capa de conexión (PDO).
 * Una sola conexión por petición (Singleton básico). Si falla, lanza la
 * excepción: el front controller (index.php) se encarga de mostrar el error.
 */
class ConexionBaseDeDatos {
    private const HOST     = 'localhost';
    private const USUARIO  = 'root';
    private const PASSWORD = '';
    private const BASE     = 'gestion_tickets';

    private static ?PDO $conexion = null;

    public function obtenerConexion(): PDO {
        if (self::$conexion === null) {
            $dsn = 'mysql:host=' . self::HOST . ';dbname=' . self::BASE . ';charset=utf8mb4';
            self::$conexion = new PDO($dsn, self::USUARIO, self::PASSWORD, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$conexion;
    }
}
