<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteController extends BaseController {

    public function procesarExportacion(): void {
        $this->exigirRol(Usuario::ROL_ADMIN);

        $tickets = (new TicketDAO())->listarTodos($this->filtrosDesdeUrl());
        if (empty($tickets)) {
            $this->redirigir('panel_admin', ['mensaje' => 'vacio']);
        }

        if (($_GET['formato'] ?? 'pdf') === 'excel') {
            $this->exportarExcel($tickets);
        } else {
            $this->exportarPDF($tickets);
        }
    }

    private function exportarPDF(array $tickets): void {
        $e = static fn($v): string => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

        $html = '<h1 style="text-align:center; font-family:sans-serif;">Reporte Filtrado de Tickets ITIL</h1>';
        $html .= '<table border="1" style="width:100%; border-collapse:collapse; font-family:sans-serif; font-size:12px; text-align:left;">
                    <thead style="background-color:#0056b3; color:white;"><tr>
                        <th style="padding:8px;">ID</th><th style="padding:8px;">Fecha</th><th style="padding:8px;">Usuario</th>
                        <th style="padding:8px;">Clasificación</th><th style="padding:8px;">Asunto</th><th style="padding:8px;">Estado</th>
                    </tr></thead><tbody>';
        foreach ($tickets as $t) {
            $html .= '<tr>'
                . '<td style="padding:8px;">#' . $e($t['id']) . '</td>'
                . '<td style="padding:8px;">' . $e($t['fecha_registro']) . '</td>'
                . '<td style="padding:8px;">' . $e($t['nombre_usuario']) . '</td>'
                . '<td style="padding:8px;">' . $e($t['tipo']) . '</td>'
                . '<td style="padding:8px;">' . $e($t['asunto']) . '</td>'
                . '<td style="padding:8px;">' . $e($t['estado']) . '</td>'
                . '</tr>';
        }
        $html .= '</tbody></table>';

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Reporte_Filtrado.pdf', ['Attachment' => 1]);
        exit();
    }

    private function exportarExcel(array $tickets): void {
        $spreadsheet = new Spreadsheet();
        $hoja = $spreadsheet->getActiveSheet();

        $cabeceras = ['ID Ticket', 'Fecha', 'Usuario', 'Clasificación', 'Asunto', 'Descripción', 'Estado', 'SLA'];
        foreach ($cabeceras as $i => $texto) {
            $hoja->setCellValue(chr(65 + $i) . '1', $texto);
        }
        $hoja->getStyle('A1:H1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0056b3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $fila = 2;
        foreach ($tickets as $t) {
            $valores = [
                $t['id'], $t['fecha_registro'], $t['nombre_usuario'], $t['tipo'],
                $t['asunto'], $t['descripcion'], $t['estado'], $t['tiempo_resolucion'],
            ];
            foreach ($valores as $i => $valor) {
                // Siempre como texto: evita que un "=..." escrito por un usuario se ejecute como fórmula.
                $hoja->setCellValueExplicit(chr(65 + $i) . $fila, (string)$valor, DataType::TYPE_STRING);
            }
            $fila++;
        }
        foreach (range('A', 'H') as $columna) {
            $hoja->getColumnDimension($columna)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Filtrado.xlsx"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit();
    }
}
