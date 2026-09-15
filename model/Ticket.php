<?php

// ABSTRACCIÓN: Plantilla general en UpperCamelCase
abstract class Ticket {
    
    // ENCAPSULAMIENTO: Propiedades en lowerCamelCase
    protected int $idTicket;
    protected string $usuarioSolicitante;
    private string $estadoActual;

    public function __construct(string $usuarioSolicitante) {
        $this->usuarioSolicitante = $usuarioSolicitante;
        // Todo ticket nuevo nace con estado "Abierto"
        $this->estadoActual = "Abierto"; 
    }

    // Método controlado para leer el dato privado
    public function getEstadoActual(): string {
        return $this->estadoActual;
    }

    // Método abstracto que obligará al polimorfismo más adelante
    abstract public function calcularTiempoResolucion(): string;
}
?>