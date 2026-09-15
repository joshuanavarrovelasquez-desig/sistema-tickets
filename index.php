<?php
// Requerimos el controlador para poder usarlo
require_once 'controller/TicketController.php';

// Capturamos la 'accion' de la URL. Si el usuario recién entra, por defecto es 'inicio'
$accion = $_GET['accion'] ?? 'inicio';

if ($accion === 'crear') {
    // Si la acción es crear, instanciamos el Controlador y ejecutamos el método POST
    $controlador = new TicketController();
    $controlador->registrarNuevoTicket();
} else {
    // Si alguien entra a localhost/sistema_tickets/ lo redirigimos automáticamente a la vista
    header("Location: views/crear_ticket.php");
    exit();
}
?>