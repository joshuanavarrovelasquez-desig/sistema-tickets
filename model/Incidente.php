<?php
require_once __DIR__ . '/Ticket.php';

class Incidente extends Ticket {
    private string $urgencia;

    public function __construct(int $idUsuario, string $correo, string $asunto, string $descripcion, ?string $codigoBarras, ?string $archivoAdjunto, string $urgencia) {
        parent::__construct($idUsuario, $correo, $asunto, $descripcion, $codigoBarras, $archivoAdjunto);
        $this->urgencia = $urgencia;
    }

    public function calcularTiempoResolucion(): string {
        if (strcasecmp($this->urgencia, 'Alta') === 0) {
            return '2 horas (SLA Crítico)';
        }
        return '24 horas (SLA Normal)';
    }
}
?>