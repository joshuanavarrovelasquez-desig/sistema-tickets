<?php
require_once __DIR__ . '/Ticket.php';

// HERENCIA: Incidente hereda de Ticket
class Incidente extends Ticket {
    
    // Propiedad específica solo para incidentes
    private string $nivelImpacto; 

    public function __construct(string $usuarioSolicitante, string $nivelImpacto) {
        // Llamamos al constructor de la clase padre (Ticket)
        parent::__construct($usuarioSolicitante);
        $this->nivelImpacto = $nivelImpacto;
    }

    // POLIMORFISMO: Implementamos el método abstracto a la manera del Incidente
    public function calcularTiempoResolucion(): string {
        if ($this->nivelImpacto === 'Alta') {
            return "2 horas (SLA Crítico)";
        }
        return "24 horas (SLA Normal)";
    }
}
?>