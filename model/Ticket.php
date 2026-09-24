<?php
abstract class Ticket {
    protected int $idUsuario;
    protected string $correo;
    protected string $asunto;
    protected string $descripcion;
    protected ?string $codigoBarras;
    protected ?string $archivoAdjunto;
    protected string $estadoActual = 'Abierto';

    public function __construct(int $idUsuario, string $correo, string $asunto, string $descripcion, ?string $codigoBarras, ?string $archivoAdjunto) {
        $this->idUsuario = $idUsuario;
        $this->correo = $correo;
        $this->asunto = $asunto;
        $this->descripcion = $descripcion;
        $this->codigoBarras = $codigoBarras;
        $this->archivoAdjunto = $archivoAdjunto;
    }

    public function getIdUsuario(): int { return $this->idUsuario; }
    public function getCorreo(): string { return $this->correo; }
    public function getAsunto(): string { return $this->asunto; }
    public function getDescripcion(): string { return $this->descripcion; }
    public function getCodigoBarras(): ?string { return $this->codigoBarras; }
    public function getArchivoAdjunto(): ?string { return $this->archivoAdjunto; }
    public function getEstadoActual(): string { return $this->estadoActual; }

    abstract public function calcularTiempoResolucion(): string;
}
?>