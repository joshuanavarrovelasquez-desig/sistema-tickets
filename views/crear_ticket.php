<?php if (!defined('APP_INICIADA')) { http_response_code(403); exit('Acceso no permitido.'); } ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mesa de Ayuda TI - Crear Ticket</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f9;}
        .formulario { max-width: 500px; padding: 20px; background: white; border: 1px solid #ccc; border-radius: 8px; margin: auto; }
        input, select, textarea, button { width: 100%; margin-bottom: 15px; padding: 10px; box-sizing: border-box; font-family: Arial, sans-serif; }
        textarea { resize: vertical; }
        button { background-color: #0056b3; color: white; border: none; cursor: pointer; font-weight: bold; font-size: 16px;}
        button:hover { background-color: #004494; }
        .opcional { color: #666; font-size: 0.8em; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/_mensajes.php'; ?>
    <h2 style="text-align: center;">Mesa de Ayuda TI - Instituto Superior Técnico Oxapampa</h2>
    <div class="formulario">
        
        <form action="index.php?accion=crear" method="POST" enctype="multipart/form-data">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            
            <label for="correo">Correo Electrónico de Contacto:</label>
            <input type="email" name="correo" id="correo" value="<?= htmlspecialchars($correoSugerido ?? '') ?>" placeholder="ejemplo@correo.com" required>

            <label for="asunto">Asunto / Título corto del problema:</label>
            <input type="text" name="asunto" id="asunto" placeholder="Ej. Impresora no enciende / Requiero nuevo mouse" required>

            <label for="descripcion">Descripción Detallada:</label>
            <textarea name="descripcion" id="descripcion" rows="4" placeholder="Describe la falla o lo que solicitas con el mayor detalle posible..." required></textarea>

            <label for="codigo_barras">Código de Barras del Equipo <span class="opcional">(Opcional)</span>:</label>
            <input type="text" name="codigo_barras" id="codigo_barras" placeholder="Ej. PC-00123">

            <label for="archivo">Adjuntar Captura o Foto <span class="opcional">(Opcional)</span>:</label>
            <input type="file" name="archivo" id="archivo" accept="image/*">

            <button type="submit">Registrar Nuevo Ticket</button>
        </form>
        <div style="text-align: center; margin-top: 10px;">
            <a href="index.php?accion=listar_tickets" style="color: #666; text-decoration: none;">← Cancelar y volver al historial</a>
        </div>
    </div>
</body>
</html>