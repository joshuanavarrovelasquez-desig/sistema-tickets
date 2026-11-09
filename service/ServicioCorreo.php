<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

/** Envía correos por SMTP. Si algo falla, lo registra y devuelve false (nunca rompe la página). */
class ServicioCorreo {
    private array $config;

    public function __construct() {
        $archivo = __DIR__ . '/../config/config.local.php';
        $todo = is_file($archivo) ? require $archivo : [];
        $this->config = $todo['correo'] ?? [];
    }

    public function enviar(string $destinatario, string $asunto, string $mensajeHtml): bool {
        if (empty($this->config['activo']) || !filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $this->config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->config['usuario'];
            $mail->Password   = $this->config['clave'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)$this->config['puerto'];
            $mail->CharSet    = 'UTF-8';
            $mail->Timeout    = 10;

            $mail->setFrom($this->config['remitente'], $this->config['nombre_remitente']);
            $mail->addAddress($destinatario);
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $mensajeHtml;
            $mail->AltBody = strip_tags($mensajeHtml);

            return $mail->send();
        } catch (\Throwable $e) {
            error_log('[CORREO] ' . $e->getMessage());
            return false;
        }
    }
}
