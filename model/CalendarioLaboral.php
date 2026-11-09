<?php
/**
 * Cálculos de días hábiles (lunes a viernes, sin feriados).
 * Es una regla de negocio pura: NO consulta internet, recibe los feriados ya listos.
 */
class CalendarioLaboral {

    /** @param string[] $feriados fechas en formato Y-m-d */
    public static function esDiaHabil(DateTimeInterface $fecha, array $feriados): bool {
        $diaSemana = (int)$fecha->format('N'); // 1 = lunes ... 7 = domingo
        return $diaSemana <= 5 && !in_array($fecha->format('Y-m-d'), $feriados, true);
    }

    /**
     * Devuelve el N-ésimo día hábil posterior a $desde, a la hora indicada.
     * Ej.: creado un jueves, +3 días hábiles = martes siguiente (si no hay feriados).
     *
     * @param string[] $feriados fechas en formato Y-m-d
     */
    public static function sumarDiasHabiles(
        DateTimeImmutable $desde, int $dias, array $feriados, string $hora = '18:00'
    ): DateTimeImmutable {
        $fecha = $desde;
        $contados = 0;
        while ($contados < $dias) {
            $fecha = $fecha->modify('+1 day');
            if (self::esDiaHabil($fecha, $feriados)) {
                $contados++;
            }
        }
        [$h, $m] = array_map('intval', explode(':', $hora));
        return $fecha->setTime($h, $m);
    }
}
