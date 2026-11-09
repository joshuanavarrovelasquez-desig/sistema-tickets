<?php
/**
 * Obtiene los feriados oficiales desde la API gratuita Nager.Date y los guarda
 * en una caché local (un archivo por país y año), para no depender de internet
 * en cada consulta. Si no hay internet ni caché, devuelve una lista vacía
 * (entonces solo se saltan los fines de semana).
 */
class ServicioFeriados {
    private const URL = 'https://date.nager.at/api/v3/PublicHolidays/%d/%s';

    public function __construct(private string $pais = 'PE') {}

    /**
     * Feriados del año de $desde y del siguiente (un plazo puede cruzar el fin de año).
     * @return string[] fechas Y-m-d
     */
    public function feriadosDesde(DateTimeInterface $desde): array {
        $anio = (int)$desde->format('Y');
        return array_values(array_unique(array_merge($this->delAnio($anio), $this->delAnio($anio + 1))));
    }

    /** @return string[] fechas Y-m-d */
    public function delAnio(int $anio): array {
        $archivo = __DIR__ . '/../cache/feriados_' . $this->pais . '_' . $anio . '.json';

        if (is_file($archivo)) {
            $guardado = json_decode((string)file_get_contents($archivo), true);
            if (is_array($guardado) && !empty($guardado)) {
                return $guardado;
            }
        }

        $fechas = $this->consultarApi($anio);
        if (!empty($fechas)) {
            if (!is_dir(dirname($archivo))) {
                mkdir(dirname($archivo), 0755, true);
            }
            file_put_contents($archivo, json_encode($fechas));
            return $fechas;
        }
        return [];
    }

    /** @return string[]|null null si la consulta falló */
    private function consultarApi(int $anio): ?array {
        $ch = curl_init(sprintf(self::URL, $anio, $this->pais));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $respuesta = curl_exec($ch);
        $codigo    = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorCurl = curl_error($ch);
        curl_close($ch);

        if ($respuesta === false || $codigo !== 200) {
            error_log("[FERIADOS] No se pudo consultar {$this->pais} $anio (HTTP $codigo) $errorCurl");
            return null;
        }

        $datos = json_decode($respuesta, true);
        if (!is_array($datos)) {
            return null;
        }
        $fechas = [];
        foreach ($datos as $feriado) {
            if (isset($feriado['date']) && in_array('Public', $feriado['types'] ?? ['Public'], true)) {
                $fechas[] = $feriado['date'];
            }
        }
        return $fechas;
    }
}
