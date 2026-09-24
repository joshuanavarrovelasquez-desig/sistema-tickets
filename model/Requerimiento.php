<?php
require_once __DIR__ . '/Ticket.php';

class Requerimiento extends Ticket {
    private bool $requiereAprobacion;

    public function __construct(int $idUsuario, string $correo, string $asunto, string $descripcion, ?string $codigoBarras, ?string $archivoAdjunto, bool $requiereAprobacion) {
        parent::__construct($idUsuario, $correo, $asunto, $descripcion, $codigoBarras, $archivoAdjunto);
        $this->requiereAprobacion = $requiereAprobacion;
    }

    public function calcularTiempoResolucion(): string {
        if ($this->requiereAprobacion) {
            return '5 días hábiles (Requiere visto bueno de gerencia)';
        }
        return '3 días hábiles';
    }
}
?>