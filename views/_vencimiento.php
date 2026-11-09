<?php
/** Pieza reutilizable: muestra la fecha límite del SLA con color según el plazo. */
if (!function_exists('etiquetaVencimiento')) {
    function etiquetaVencimiento(array $ticket): string {
        if (empty($ticket['fecha_limite'])) {
            return '';
        }
        $limite = new DateTimeImmutable($ticket['fecha_limite']);
        $texto = $limite->format('d/m/Y H:i');

        if ($ticket['estado'] === 'Finalizado') {
            return '<br><small style="color:#555;">Plazo: ' . $texto . '</small>';
        }
        $ahora = new DateTimeImmutable();
        if ($limite < $ahora) {
            return '<br><small style="color:#b02a37; font-weight:bold;">⚠ Vencido: ' . $texto . '</small>';
        }
        $restante = $limite->getTimestamp() - $ahora->getTimestamp();
        $color = $restante <= 4 * 3600 ? '#fd7e14' : '#28a745';
        return '<br><small style="color:' . $color . '; font-weight:bold;">Vence: ' . $texto . '</small>';
    }
}
