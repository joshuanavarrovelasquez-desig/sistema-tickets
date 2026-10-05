<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Aquí llamamos a Composer y al nuevo modelo
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../model/GestorDeReportes.php';

use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteController {
    
    // Método principal que recibe la solicitud
    public function procesarExportacion(): void {
        // Recoger los filtros enviados por el formulario (método GET)
        $filtros = [
            'usuario'      => $_GET['usuario'] ?? '',
            'estado'       => $_GET['estado'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
            'fecha_fin'    => $_GET['fecha_fin'] ?? ''
        ];

        // Saber si presionó el botón de PDF o el de Excel
        $formato = $_GET['formato'] ?? 'pdf'; 

        $gestor = new GestorDeReportes();
        $datosFiltrados = $gestor->obtenerTicketsFiltrados($filtros);

        // Si no hay datos, lo devolvemos con un mensaje
        if (empty($datosFiltrados)) {
            header("Location: /sistema_tickets/views/panel_admin.php?mensaje=vacio");
            exit();
        }

        // Decidimos a qué función interna llamar
        if ($formato === 'excel') {
            $this->exportarExcel($datosFiltrados);
        } else {
            $this->exportarPDF($datosFiltrados);
        }
    }

    // --- FUNCIÓN PRIVADA PARA PDF ---
    private function exportarPDF(array $tickets): void {
        $dompdf = new Dompdf();
        $html = '<h1 style="text-align:center; font-family:sans-serif;">Reporte Filtrado de Tickets ITIL</h1>';
        $html .= '<table border="1" style="width: 100%; border-collapse: collapse; font-family:sans-serif; font-size:12px; text-align:left;">
                    <thead style="background-color: #0056b3; color: white;">
                        <tr>
                            <th style="padding: 8px;">ID</th><th style="padding: 8px;">Fecha</th><th style="padding: 8px;">Usuario</th>
                            <th style="padding: 8px;">Clasificación</th><th style="padding: 8px;">Asunto</th><th style="padding: 8px;">Estado</th>
                        </tr>
                    </thead><tbody>';

        foreach ($tickets as $ticket) {
            $html .= '<tr>
                        <td style="padding: 8px;">#' . $ticket['id'] . '</td>
                        <td style="padding: 8px;">' . $ticket['fecha_registro'] . '</td>
                        <td style="padding: 8px;">' . $ticket['nombre_usuario'] . '</td>
                        <td style="padding: 8px;">' . $ticket['tipo'] . '</td>
                        <td style="padding: 8px;">' . $ticket['asunto'] . '</td>
                        <td style="padding: 8px;">' . $ticket['estado'] . '</td>
                      </tr>';
        }
        $html .= '</tbody></table>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("Reporte_Filtrado.pdf", array("Attachment" => 1));
        exit();
    }

    // --- FUNCIÓN PRIVADA PARA EXCEL ---
    private function exportarExcel(array $tickets): void {
        $spreadsheet = new Spreadsheet();
        $hoja = $spreadsheet->getActiveSheet();

        $hoja->setCellValue('A1', 'ID Ticket')->setCellValue('B1', 'Fecha')->setCellValue('C1', 'Usuario')->setCellValue('D1', 'Clasificación')
             ->setCellValue('E1', 'Asunto')->setCellValue('F1', 'Descripción')->setCellValue('G1', 'Estado')->setCellValue('H1', 'SLA');

        // Estilos
        $hoja->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0056b3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);

        $fila = 2; 
        foreach ($tickets as $ticket) {
            $hoja->setCellValue('A'.$fila, $ticket['id'])->setCellValue('B'.$fila, $ticket['fecha_registro'])
                 ->setCellValue('C'.$fila, $ticket['nombre_usuario'])->setCellValue('D'.$fila, $ticket['tipo'])
                 ->setCellValue('E'.$fila, $ticket['asunto'])->setCellValue('F'.$fila, $ticket['descripcion'])
                 ->setCellValue('G'.$fila, $ticket['estado'])->setCellValue('H'.$fila, $ticket['tiempo_resolucion']);
            $fila++;
        }

        foreach (range('A', 'H') as $columna) { $hoja->getColumnDimension($columna)->setAutoSize(true); }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Filtrado.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
}
?>