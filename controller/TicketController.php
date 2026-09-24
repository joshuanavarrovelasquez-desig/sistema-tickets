<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/Ticket.php';
require_once __DIR__ . '/../model/Incidente.php';
require_once __DIR__ . '/../model/Requerimiento.php';
require_once __DIR__ . '/../model/GestorDeTickets.php';

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
}
?>