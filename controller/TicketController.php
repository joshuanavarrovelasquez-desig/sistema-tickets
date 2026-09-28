<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/Ticket.php';
require_once __DIR__ . '/../model/Incidente.php';
require_once __DIR__ . '/../model/Requerimiento.php';
require_once __DIR__ . '/../model/GestorDeTickets.php';
// Requerimos el autoloader de Composer para cargar las librerías mágicamente
require_once __DIR__ . '/../vendor/autoload.php';

// Importamos las clases de Dompdf y PhpSpreadsheet
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
class TicketController {
    
    public function registrarNuevoTicket(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $idUsuarioLogueado = $_SESSION['id_usuario'];
            $correo = $_POST['correo'] ?? '';
            $tipoTicket = $_POST['tipo'] ?? 'Incidente';
            $asunto = $_POST['asunto'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $codigoBarras = !empty($_POST['codigo_barras']) ? $_POST['codigo_barras'] : null;
            $detalleEspecifico = $_POST['detalle'] ?? '';

            // GESTIÓN DE SUBIDA DE ARCHIVOS
            $nombreArchivoAdjunto = null;
            if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                $archivoTmp = $_FILES['archivo']['tmp_name'];
                $nombreOriginal = basename($_FILES['archivo']['name']);
                // Generamos un nombre único con timestamp para evitar colisiones
                $nombreArchivoAdjunto = time() . "_" . preg_replace("/[^a-zA-Z0-9.\-_]/", "", $nombreOriginal);
                $carpetaDestino = __DIR__ . '/../uploads/';
                
                if (!is_dir($carpetaDestino)) {
                    mkdir($carpetaDestino, 0777, true);
                }
                
                move_uploaded_file($archivoTmp, $carpetaDestino . $nombreArchivoAdjunto);
            }

            $nuevoTicket = null;

            if ($tipoTicket === 'Incidente') {
                $nuevoTicket = new Incidente($idUsuarioLogueado, $correo, $asunto, $descripcion, $codigoBarras, $nombreArchivoAdjunto, $detalleEspecifico);
            } else if ($tipoTicket === 'Requerimiento') {
                $requiereAprobacion = (strcasecmp($detalleEspecifico, 'Si') === 0);
                $nuevoTicket = new Requerimiento($idUsuarioLogueado, $correo, $asunto, $descripcion, $codigoBarras, $nombreArchivoAdjunto, $requiereAprobacion);
            }

            if ($nuevoTicket !== null) {
                $gestor = new GestorDeTickets();
                $guardadoExitoso = $gestor->guardarTicket($nuevoTicket, $tipoTicket);
                
                if ($guardadoExitoso) {
                    header("Location: /sistema_tickets/views/listar_tickets.php?mensaje=exito");
                    exit();
                } else {
                    echo "Hubo un error de comunicación con XAMPP.";
                }
            }
        }
    }

    public function actualizarEstado(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idTicket = (int)$_POST['id_ticket'];
            $nuevoEstado = $_POST['nuevo_estado'];

            $gestor = new GestorDeTickets();
            $actualizacionExitosa = $gestor->actualizarEstadoTicket($idTicket, $nuevoEstado);

            if ($actualizacionExitosa) {
                if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
                    header("Location: views/panel_admin.php?mensaje=actualizado");
                } else {
                    header("Location: views/listar_tickets.php?mensaje=actualizado");
                }
                exit();
            } else {
                echo "<h3 style='color:red;'>Error: No se pudo actualizar el ticket en la base de datos.</h3>";
                echo "<a href='views/panel_admin.php'>Volver al panel</a>";
            }
        }
    }

    // NUEVO MÉTODO: Exportar todos los tickets a PDF CON FECHA
    public function exportarPDF(): void {
        $gestor = new GestorDeTickets();
        $tickets = $gestor->obtenerTodosLosTickets();

        // 1. Instanciamos Dompdf
        $dompdf = new Dompdf();

        // 2. Creamos la estructura HTML de la tabla añadiendo la cabecera de Fecha y Hora
        $html = '<h1 style="text-align:center; font-family:sans-serif;">Reporte General de Tickets ITIL</h1>';
        $html .= '<table border="1" style="width: 100%; border-collapse: collapse; font-family:sans-serif; font-size:12px; text-align:left;">
                    <thead style="background-color: #0056b3; color: white;">
                        <tr>
                            <th style="padding: 8px;">ID</th>
                            <th style="padding: 8px;">Fecha y Hora</th>
                            <th style="padding: 8px;">Usuario</th>
                            <th style="padding: 8px;">Clasificación</th>
                            <th style="padding: 8px;">Asunto</th>
                            <th style="padding: 8px;">Estado</th>
                            <th style="padding: 8px;">SLA Prometido</th>
                        </tr>
                    </thead>
                    <tbody>';

        // 3. Llenamos la tabla con un bucle, incluyendo el dato de la fecha
        foreach ($tickets as $ticket) {
            $html .= '<tr>
                        <td style="padding: 8px;">#' . $ticket['id'] . '</td>
                        <td style="padding: 8px;">' . $ticket['fecha_registro'] . '</td>
                        <td style="padding: 8px;">' . $ticket['nombre_usuario'] . '</td>
                        <td style="padding: 8px;">' . $ticket['tipo'] . '</td>
                        <td style="padding: 8px;">' . $ticket['asunto'] . '</td>
                        <td style="padding: 8px;">' . $ticket['estado'] . '</td>
                        <td style="padding: 8px;">' . $ticket['tiempo_resolucion'] . '</td>
                      </tr>';
        }
        
        $html .= '</tbody></table>';

        // 4. Cargamos el HTML en la librería, ajustamos papel y exportamos
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        // El parámetro 'Attachment' => 1 fuerza la descarga en el navegador
        $dompdf->stream("Reporte_Tickets.pdf", array("Attachment" => 1));
        exit();
    }

    // NUEVO MÉTODO: Exportar a Excel CON DISEÑO Y FECHA
    public function exportarExcel(): void {
        $gestor = new GestorDeTickets();
        $tickets = $gestor->obtenerTodosLosTickets();

        $spreadsheet = new Spreadsheet();
        $hoja = $spreadsheet->getActiveSheet();

        // 1. Títulos de las columnas (ahora hasta la H porque añadimos Fecha)
        $hoja->setCellValue('A1', 'ID Ticket');
        $hoja->setCellValue('B1', 'Fecha y Hora');
        $hoja->setCellValue('C1', 'Usuario Solicitante');
        $hoja->setCellValue('D1', 'Clasificación ITIL');
        $hoja->setCellValue('E1', 'Asunto / Título');
        $hoja->setCellValue('F1', 'Descripción Detallada');
        $hoja->setCellValue('G1', 'Estado Actual');
        $hoja->setCellValue('H1', 'Tiempo de Resolución (SLA)');

        // 2. DARLE ESTILO A LA CABECERA (Fondo Azul, Texto Blanco, Negrita, Centrado)
        $estiloCabecera = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0056b3'], // Azul como en tu sistema
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $hoja->getStyle('A1:H1')->applyFromArray($estiloCabecera);

        // 3. Llenar los datos
        $fila = 2; 
        foreach ($tickets as $ticket) {
            $hoja->setCellValue('A' . $fila, $ticket['id']);
            $hoja->setCellValue('B' . $fila, $ticket['fecha_registro']);
            $hoja->setCellValue('C' . $fila, $ticket['nombre_usuario']);
            $hoja->setCellValue('D' . $fila, $ticket['tipo']);
            $hoja->setCellValue('E' . $fila, $ticket['asunto']);
            $hoja->setCellValue('F' . $fila, $ticket['descripcion']);
            $hoja->setCellValue('G' . $fila, $ticket['estado']);
            $hoja->setCellValue('H' . $fila, $ticket['tiempo_resolucion']);
            $fila++;
        }

        // 4. PONER BORDES A TODA LA TABLA DE DATOS
        $rangoDatos = 'A2:H' . ($fila - 1);
        if ($fila > 2) {
            $hoja->getStyle($rangoDatos)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
        }

        // 5. AJUSTAR EL ANCHO DE LAS COLUMNAS AUTOMÁTICAMENTE
        foreach (range('A', 'H') as $columnaID) {
            $hoja->getColumnDimension($columnaID)->setAutoSize(true);
        }

        // 6. Descargar el archivo
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Reporte_Mesa_De_Ayuda.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
}
?>