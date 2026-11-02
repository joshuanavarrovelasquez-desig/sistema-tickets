<?php
/** Entidad Usuario: representa una fila de la tabla usuarios. */
class Usuario {
    public const ROL_ADMIN   = 'admin';
    public const ROL_TECNICO = 'tecnico';
    public const ROL_CLIENTE = 'cliente';
    public const ROLES = [self::ROL_ADMIN, self::ROL_TECNICO, self::ROL_CLIENTE];

    public function __construct(
        private ?int $id = null,
        private string $nombre = '',
        private string $correo = '',
        private string $rol = self::ROL_CLIENTE,
        private ?string $passwordHash = null
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getNombre(): string { return $this->nombre; }
    public function getCorreo(): string { return $this->correo; }
    public function getRol(): string { return $this->rol; }
    public function getPasswordHash(): ?string { return $this->passwordHash; }

    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
    public function setCorreo(string $correo): void { $this->correo = $correo; }
    public function setRol(string $rol): void { $this->rol = $rol; }
    public function setPasswordHash(?string $hash): void { $this->passwordHash = $hash; }

    public static function desdeFila(array $fila): self {
        return new self(
            (int)$fila['id_usuario'],
            $fila['nombre'],
            $fila['correo'],
            $fila['rol'],
            $fila['password'] ?? null
        );
    }

    /** Comprueba la contraseña; acepta contraseñas antiguas en texto plano (migración). */
    public function verificarPassword(string $passwordPlano): bool {
        $guardado = (string)$this->passwordHash;
        if (password_get_info($guardado)['algo'] !== null) {
            return password_verify($passwordPlano, $guardado);
        }
        return $guardado !== '' && hash_equals($guardado, $passwordPlano);
    }

    /** true si la contraseña guardada es antigua (texto plano) o necesita nuevo hash. */
    public function necesitaRehash(): bool {
        $guardado = (string)$this->passwordHash;
        return password_get_info($guardado)['algo'] === null
            || password_needs_rehash($guardado, PASSWORD_DEFAULT);
    }
}
