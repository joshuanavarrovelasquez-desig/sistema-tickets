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

    /** Requerimientos: días hábiles (sin fines de semana ni feriados); vence a las 18:00. */
    public function calcularFechaLimite(DateTimeImmutable $desde, array $feriados): ?DateTimeImmutable {
        $dias = $this->requiereAprobacion ? 5 : 3;
        return CalendarioLaboral::sumarDiasHabiles($desde, $dias, $feriados);
    }
}
