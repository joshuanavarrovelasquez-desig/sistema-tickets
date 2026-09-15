<?php
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
            
            // 1. Recibir los datos de la vista
            $usuarioSolicitante = $_POST['usuario'];
            $tipoTicket = $_POST['tipo']; // Puede ser "Incidente" o "Requerimiento"
            
            // Este campo dinámico será la Urgencia (para incidentes) o la Aprobación (para requerimientos)
            $detalleEspecifico = $_POST['detalle']; 
            
            $nuevoTicket = null;

            // 2. POLIMORFISMO Y ABSTRACCIÓN EN ACCIÓN
            // Dependiendo de lo que eligió el usuario en la vista, instanciamos una clase distinta
            if ($tipoTicket === 'Incidente') {
                $nuevoTicket = new Incidente($usuarioSolicitante, $detalleEspecifico);
            } else if ($tipoTicket === 'Requerimiento') {
                // Convertimos el string que viene del formulario a un valor booleano (true/false)
                $requiereAprobacion = ($detalleEspecifico === 'Si') ? true : false;
                $nuevoTicket = new Requerimiento($usuarioSolicitante, $requiereAprobacion);
            }

            // 3. Guardar en la base de datos
            if ($nuevoTicket !== null) {
                $gestor = new GestorDeTickets();
                
                // Le pasamos el OBJETO COMPLETO al gestor
                $guardadoExitoso = $gestor->guardarTicket($nuevoTicket, $tipoTicket);
                
                if ($guardadoExitoso) {
                    // Si todo salió bien, redirigimos al usuario a la vista de la tabla
                    header("Location: ../views/listar_tickets.php?mensaje=exito");
                    exit();
                } else {
                    echo "Hubo un error de comunicación con XAMPP.";
                }
            }
        }
    }
}
?>