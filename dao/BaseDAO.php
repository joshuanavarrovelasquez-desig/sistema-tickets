<?php
require_once __DIR__ . '/../config/ConexionBaseDeDatos.php';

/**
 * Clase base de los DAO. Recibe la conexión PDO (inyectable) y ofrece un
 * helper para escrituras: captura el error, lo registra y devuelve false.
 * Las lecturas dejan subir la excepción hasta el front controller.
 */
abstract class BaseDAO {
    protected PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? (new ConexionBaseDeDatos())->obtenerConexion();
    }

    protected function ejecutar(string $sql, array $parametros): bool {
        try {
            return $this->pdo->prepare($sql)->execute($parametros);
        } catch (PDOException $e) {
            error_log('[DAO] ' . $e->getMessage());
            return false;
        }
    }

    protected function consultar(string $sql, array $parametros = []): array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll();
    }
}
