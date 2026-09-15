<?php
require_once 'Ticket.php';

// HERENCIA
class Requerimiento extends Ticket {
    
    private bool $requiereAprobacion;

    public function __construct(string $usuarioSolicitante, bool $requiereAprobacion) {
        parent::__construct($usuarioSolicitante);
        $this->requiereAprobacion = $requiereAprobacion;
    }

    // POLIMORFISMO: El mismo método, pero con una respuesta totalmente diferente
    public function calcularTiempoResolucion(): string {
        if ($this->requiereAprobacion) {
            return "5 días hábiles (Requiere visto bueno de gerencia)";
        }
        return "3 días hábiles";
    }
}
?>