<?php
// Eliminamos la simulación, pero aseguramos que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Requerimos los modelos que el controlador va a utilizar
require_once __DIR__ . '/../model/Incidente.php';
require_once __DIR__ . '/../model/Requerimiento.php';
require_once __DIR__ . '/../model/GestorDeTickets.php';

// Nombramos la clase en UpperCamelCase
class TicketController {
    
    // Nombramos el método en lowerCamelCase
    public function registrarNuevoTicket(): void {
        
        // Verificamos si los datos llegaron a través del formulario (método POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // 1. Recibir los datos de la vista y la sesión real
            $idUsuarioLogueado = $_SESSION['id_usuario'];
            $tipoTicket = $_POST['tipo']; // Puede ser "Incidente" o "Requerimiento"
            
            // Este campo dinámico será la Urgencia (para incidentes) o la Aprobación (para requerimientos)
            $detalleEspecifico = $_POST['detalle']; 
            
            $nuevoTicket = null;

            // 2. POLIMORFISMO Y ABSTRACCIÓN EN ACCIÓN
            // Dependiendo de lo que eligió el usuario en la vista, instanciamos una clase distinta
            if ($tipoTicket === 'Incidente') {
                $nuevoTicket = new Incidente($idUsuarioLogueado, $detalleEspecifico);
            } else if ($tipoTicket === 'Requerimiento') {
                // Convertimos el string que viene del formulario a un valor booleano (true/false)
                $requiereAprobacion = ($detalleEspecifico === 'Si') ? true : false;
                $nuevoTicket = new Requerimiento($idUsuarioLogueado, $requiereAprobacion);
            }

            // 3. Guardar en la base de datos
            if ($nuevoTicket !== null) {
                $gestor = new GestorDeTickets();
                
                // Le pasamos el OBJETO COMPLETO al gestor
                $guardadoExitoso = $gestor->guardarTicket($nuevoTicket, $tipoTicket);
                
                if ($guardadoExitoso) {
                    // Si todo salió bien, redirigimos al usuario a la vista de la tabla
                    header("Location: /sistema_tickets/views/listar_tickets.php?mensaje=exito");
                    exit();
                } else {
                    echo "Hubo un error de comunicación con XAMPP.";
                }
            }
        }
    }

    // ACTUALIZADO: Procesa el cambio de estado con manejo de errores
    public function actualizarEstado(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idTicket = (int)$_POST['id_ticket'];
            $nuevoEstado = $_POST['nuevo_estado'];

            $gestor = new GestorDeTickets();
            $actualizacionExitosa = $gestor->actualizarEstadoTicket($idTicket, $nuevoEstado);

            if ($actualizacionExitosa) {
                // Redirigimos usando rutas relativas seguras
                if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
                    header("Location: views/panel_admin.php?mensaje=actualizado");
                } else {
                    header("Location: views/listar_tickets.php?mensaje=actualizado");
                }
                exit();
            } else {
                // Si la BD falla, ya no te dará pantalla blanca, sino este mensaje:
                echo "<h3 style='color:red;'>Error: No se pudo actualizar el ticket en la base de datos.</h3>";
                echo "<a href='views/panel_admin.php'>Volver al panel</a>";
            }
        } else {
            echo "Error: El acceso debe ser a través del botón del formulario.";
        }
    }
}
?>