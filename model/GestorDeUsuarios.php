<?php
require_once __DIR__ . '/../config/ConexionBaseDeDatos.php';

class GestorDeUsuarios {
    private PDO $conexionDb;

    public function __construct() {
        $conexion = new ConexionBaseDeDatos();
        $this->conexionDb = $conexion->obtenerConexion();
    }

    public function autenticarUsuario(string $correo, string $password): ?array {
        $sql = "SELECT id_usuario, nombre, password, rol FROM usuarios WHERE correo = ?";
        try {
            $stmt = $this->conexionDb->prepare($sql);
            $stmt->execute([$correo]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && $usuario['password'] === $password) {
                return $usuario;
            }
            return null;
        } catch (PDOException $e) {
            echo "Error de BD: " . $e->getMessage();
            return null;
        }
    }
    // Obtener los datos de un usuario por su ID
    public function obtenerUsuarioPorId(int $idUsuario): ?array {
        $sql = "SELECT id_usuario, nombre, correo, rol, password FROM usuarios WHERE id_usuario = ?";
        try {
            $stmt = $this->conexionDb->prepare($sql);
            $stmt->execute([$idUsuario]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    // Actualizar perfil y contraseña opcionalmente
    public function actualizarPerfil(int $idUsuario, string $nombre, string $correo, ?string $password = null): bool {
        try {
            if (!empty($password)) {
                $sql = "UPDATE usuarios SET nombre = ?, correo = ?, password = ? WHERE id_usuario = ?";
                $stmt = $this->conexionDb->prepare($sql);
                return $stmt->execute([$nombre, $correo, $password, $idUsuario]);
            } else {
                $sql = "UPDATE usuarios SET nombre = ?, correo = ? WHERE id_usuario = ?";
                $stmt = $this->conexionDb->prepare($sql);
                return $stmt->execute([$nombre, $correo, $idUsuario]);
            }
        } catch (PDOException $e) {
            return false;
        }
    }
    // --- NUEVO PARA PASO 1: Obtener todos los usuarios ---
    public function obtenerTodosLosUsuarios(): array {
        $sql = "SELECT id_usuario, nombre, correo, rol FROM usuarios ORDER BY id_usuario DESC";
        $stmt = $this->conexionDb->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- NUEVO PARA PASO 1: Registrar nuevo usuario ---
    public function registrarUsuario(string $nombre, string $correo, string $password, string $rol): bool {
        $sql = "INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?, ?, ?, ?)";
        try {
            $stmt = $this->conexionDb->prepare($sql);
            return $stmt->execute([$nombre, $correo, $password, $rol]);
        } catch (PDOException $e) {
            return false;
        }
    }
    // Actualizar usuario desde el panel de administración
    public function actualizarUsuarioAdmin(int $idUsuario, string $nombre, string $correo, ?string $password, string $rol): bool {
        try {
            if (!empty($password)) {
                $sql = "UPDATE usuarios SET nombre = ?, correo = ?, password = ?, rol = ? WHERE id_usuario = ?";
                $stmt = $this->conexionDb->prepare($sql);
                return $stmt->execute([$nombre, $correo, $password, $rol, $idUsuario]);
            } else {
                $sql = "UPDATE usuarios SET nombre = ?, correo = ?, rol = ? WHERE id_usuario = ?";
                $stmt = $this->conexionDb->prepare($sql);
                return $stmt->execute([$nombre, $correo, $rol, $idUsuario]);
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    // Eliminar usuario
    public function eliminarUsuario(int $idUsuario): bool {
        $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
        try {
            $stmt = $this->conexionDb->prepare($sql);
            return $stmt->execute([$idUsuario]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>