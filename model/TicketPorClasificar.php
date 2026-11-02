<?php
/** Ticket recién creado: el administrador todavía no lo clasifica (Incidente / Requerimiento). */
class TicketPorClasificar extends Ticket {
    public function getTipo(): string { return 'Por Clasificar'; }
    public function calcularTiempoResolucion(): string { return 'Pendiente de clasificación'; }
}
