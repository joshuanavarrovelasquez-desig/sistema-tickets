<?php
/** Crea la entidad correcta según el tipo ITIL elegido. */
class TicketFactory {
    public static function crear(
        string $tipo, int $idUsuario, string $correo, string $asunto,
        string $descripcion, ?string $codigoBarras, ?string $archivoAdjunto
    ): Ticket {
        return match ($tipo) {
            'Incidente' => new Incidente(
                $idUsuario, $correo, $asunto, $descripcion, $codigoBarras, $archivoAdjunto,
                Incidente::detectarUrgencia($asunto, $descripcion)
            ),
            'Requerimiento' => new Requerimiento($idUsuario, $correo, $asunto, $descripcion, $codigoBarras, $archivoAdjunto),
            default => new TicketPorClasificar($idUsuario, $correo, $asunto, $descripcion, $codigoBarras, $archivoAdjunto),
        };
    }
}
