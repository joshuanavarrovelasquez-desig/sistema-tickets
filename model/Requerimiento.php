<?php
require_once __DIR__ . '/Ticket.php';

// HERENCIA
class Requerimiento extends Ticket {
    
    private bool $requiereAprobacion;

    // CAMBIO: Ahora recibe int $idUsuario en lugar de string $usuarioSolicitante
    public function __construct(int $idUsuario, bool $requiereAprobacion) {
        parent::__construct($idUsuario);
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