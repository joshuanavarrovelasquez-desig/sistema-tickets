<?php

// ABSTRACCIÓN: Plantilla general en UpperCamelCase
abstract class Ticket {
    
    // ENCAPSULAMIENTO: Propiedades en lowerCamelCase
    protected int $idTicket;
    
    // CAMBIO 1: Reemplazamos $usuarioSolicitante por $idUsuario
    protected int $idUsuario; 
    private string $estadoActual;

    // CAMBIO 2: El constructor ahora recibe un número entero (int)
    public function __construct(int $idUsuario) {
        $this->idUsuario = $idUsuario;
        // Todo ticket nuevo nace con estado "Abierto"
        $this->estadoActual = "Abierto"; 
    }

    // Método controlado para leer el dato privado
    public function getEstadoActual(): string {
        return $this->estadoActual;
    }

    // CAMBIO 3: Actualizamos el Getter (Antes era getUsuarioSolicitante)
    public function getIdUsuario(): int {
        return $this->idUsuario;
    }

    // Método abstracto que obligará al polimorfismo más adelante
    abstract public function calcularTiempoResolucion(): string;
}
?>