<?php
/** Entidad abstracta Ticket. Cada tipo define su propio SLA (regla de negocio). */
abstract class Ticket {
    public const ESTADOS = ['Abierto', 'En proceso', 'Finalizado'];
    public const TIPOS   = ['Por Clasificar', 'Incidente', 'Requerimiento'];

    protected string $estadoActual = 'Abierto';

    public function __construct(
        protected int $idUsuario,
        protected string $correo,
        protected string $asunto,
        protected string $descripcion,
        protected ?string $codigoBarras,
        protected ?string $archivoAdjunto
    ) {}

    public function getIdUsuario(): int { return $this->idUsuario; }
    public function getCorreo(): string { return $this->correo; }
    public function getAsunto(): string { return $this->asunto; }
    public function getDescripcion(): string { return $this->descripcion; }
    public function getCodigoBarras(): ?string { return $this->codigoBarras; }
    public function getArchivoAdjunto(): ?string { return $this->archivoAdjunto; }
    public function getEstadoActual(): string { return $this->estadoActual; }

    abstract public function getTipo(): string;
    abstract public function calcularTiempoResolucion(): string;
}
