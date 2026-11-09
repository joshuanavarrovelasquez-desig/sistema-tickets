<?php
class Incidente extends Ticket {
    private const PALABRAS_CRITICAS = ['crítico', 'critico', 'urgente', 'placa', 'caida', 'caída', 'sin internet'];

    public function __construct(
        int $idUsuario, string $correo, string $asunto, string $descripcion,
        ?string $codigoBarras, ?string $archivoAdjunto,
        private string $urgencia = 'Normal'
    ) {
        parent::__construct($idUsuario, $correo, $asunto, $descripcion, $codigoBarras, $archivoAdjunto);
    }

    /** Regla de negocio: deduce la urgencia leyendo asunto y descripción. */
    public static function detectarUrgencia(string $asunto, string $descripcion): string {
        $texto = mb_strtolower($asunto . ' ' . $descripcion);
        foreach (self::PALABRAS_CRITICAS as $palabra) {
            if (str_contains($texto, $palabra)) {
                return 'Alta';
            }
        }
        return 'Normal';
    }

    public function getTipo(): string { return 'Incidente'; }

    public function calcularTiempoResolucion(): string {
        return strcasecmp($this->urgencia, 'Alta') === 0
            ? '2 horas (SLA Crítico)'
            : '24 horas (SLA Normal)';
    }

    /** Incidentes: horas corridas contadas desde que se creó el ticket. */
    public function calcularFechaLimite(DateTimeImmutable $desde, array $feriados): ?DateTimeImmutable {
        return strcasecmp($this->urgencia, 'Alta') === 0
            ? $desde->modify('+2 hours')
            : $desde->modify('+24 hours');
    }
}
