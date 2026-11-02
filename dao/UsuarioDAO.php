<?php
require_once __DIR__ . '/BaseDAO.php';
require_once __DIR__ . '/../model/Usuario.php';

class UsuarioDAO extends BaseDAO {

    public function buscarPorCorreo(string $correo): ?Usuario {
        $filas = $this->consultar(
            "SELECT id_usuario, nombre, correo, password, rol FROM usuarios WHERE correo = ?", [$correo]
        );
        return $filas ? Usuario::desdeFila($filas[0]) : null;
    }

    public function buscarPorId(int $idUsuario): ?Usuario {
        $filas = $this->consultar(
            "SELECT id_usuario, nombre, correo, password, rol FROM usuarios WHERE id_usuario = ?", [$idUsuario]
        );
        return $filas ? Usuario::desdeFila($filas[0]) : null;
    }

    /** @return Usuario[] */
    public function listarTodos(): array {
        $filas = $this->consultar("SELECT id_usuario, nombre, correo, rol FROM usuarios ORDER BY id_usuario DESC");
        return array_map([Usuario::class, 'desdeFila'], $filas);
    }

    /** @return Usuario[] */
    public function listarPorRol(string $rol): array {
        $filas = $this->consultar(
            "SELECT id_usuario, nombre, correo, rol FROM usuarios WHERE rol = ? ORDER BY nombre", [$rol]
        );
        return array_map([Usuario::class, 'desdeFila'], $filas);
    }

    public function existeCorreo(string $correo, ?int $excluirIdUsuario = null): bool {
        $filas = $this->consultar(
            "SELECT 1 FROM usuarios WHERE correo = ? AND id_usuario <> ? LIMIT 1",
            [$correo, $excluirIdUsuario ?? 0]
        );
        return !empty($filas);
    }

    public function guardar(Usuario $usuario): bool {
        return $this->ejecutar(
            "INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?, ?, ?, ?)",
            [$usuario->getNombre(), $usuario->getCorreo(), $usuario->getPasswordHash(), $usuario->getRol()]
        );
    }

    /** Actualiza nombre, correo y rol; la contraseña solo si la entidad trae un hash nuevo. */
    public function actualizar(Usuario $usuario): bool {
        if ($usuario->getPasswordHash() !== null && $usuario->getPasswordHash() !== '') {
            return $this->ejecutar(
                "UPDATE usuarios SET nombre = ?, correo = ?, password = ?, rol = ? WHERE id_usuario = ?",
                [$usuario->getNombre(), $usuario->getCorreo(), $usuario->getPasswordHash(), $usuario->getRol(), $usuario->getId()]
            );
        }
        return $this->ejecutar(
            "UPDATE usuarios SET nombre = ?, correo = ?, rol = ? WHERE id_usuario = ?",
            [$usuario->getNombre(), $usuario->getCorreo(), $usuario->getRol(), $usuario->getId()]
        );
    }

    /** Perfil propio: nunca toca el rol. */
    public function actualizarPerfil(Usuario $usuario): bool {
        if ($usuario->getPasswordHash() !== null && $usuario->getPasswordHash() !== '') {
            return $this->ejecutar(
                "UPDATE usuarios SET nombre = ?, correo = ?, password = ? WHERE id_usuario = ?",
                [$usuario->getNombre(), $usuario->getCorreo(), $usuario->getPasswordHash(), $usuario->getId()]
            );
        }
        return $this->ejecutar(
            "UPDATE usuarios SET nombre = ?, correo = ? WHERE id_usuario = ?",
            [$usuario->getNombre(), $usuario->getCorreo(), $usuario->getId()]
        );
    }

    public function actualizarPassword(int $idUsuario, string $hash): bool {
        return $this->ejecutar("UPDATE usuarios SET password = ? WHERE id_usuario = ?", [$hash, $idUsuario]);
    }

    public function eliminar(int $idUsuario): bool {
        return $this->ejecutar("DELETE FROM usuarios WHERE id_usuario = ?", [$idUsuario]);
    }
}
