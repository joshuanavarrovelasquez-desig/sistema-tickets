<?php
class Requerimiento extends Ticket {
    public function __construct(
        int $idUsuario, string $correo, string $asunto, string $descripcion,
        ?string $codigoBarras, ?string $archivoAdjunto,
        private bool $requiereAprobacion = false
    ) {
        parent::__construct($idUsuario, $correo, $asunto, $descripcion, $codigoBarras, $archivoAdjunto);
    }

    public function getTipo(): string { return 'Requerimiento'; }


    public function calcularTiempoResolucion(): string {
        return $this->requiereAprobacion
            ? '5 días hábiles (Requiere visto bueno de gerencia)'
            : '3 días hábiles';
    }
}
