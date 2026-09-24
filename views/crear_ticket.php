<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
?>
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
    <h2 style="text-align: center;">Mesa de Ayuda TI - Instituto Superior Técnico Oxapampa</h2>
    <div class="formulario">
        
        <!-- IMPORTANTE: enctype permite el envío de archivos (fotos, pdfs) -->
        <form action="../index.php?accion=crear" method="POST" enctype="multipart/form-data">
            
            <label for="correo">Correo Electrónico de Contacto:</label>
            <input type="email" name="correo" id="correo" placeholder="ejemplo@correo.com" required>

            <label for="tipo">Clasificación ITIL:</label>
            <select name="tipo" id="tipo" required>
                <option value="Incidente">Incidente (Falla técnica en equipo/software)</option>
                <option value="Requerimiento">Requerimiento (Solicitud de acceso/nuevo equipo)</option>
            </select>

            <label for="asunto">Asunto / Título corto:</label>
            <input type="text" name="asunto" id="asunto" placeholder="Ej. Impresora no enciende" required>

            <label for="descripcion">Descripción Detallada:</label>
            <textarea name="descripcion" id="descripcion" rows="4" placeholder="Describe el problema o tu solicitud con el mayor detalle posible..." required></textarea>

            <label for="codigo_barras">Código de Barras del Equipo <span class="opcional">(Opcional)</span>:</label>
            <input type="text" name="codigo_barras" id="codigo_barras" placeholder="Ej. PC-00123">

            <label for="archivo">Adjuntar Captura o Foto <span class="opcional">(Opcional)</span>:</label>
            <!-- accept limitará a que el usuario solo pueda elegir imágenes -->
            <input type="file" name="archivo" id="archivo" accept="image/*">

            <label for="detalle">Detalle de Urgencia / Aprobación:</label>
            <input type="text" name="detalle" id="detalle" placeholder="Escribe 'Alta' (Incidente) o 'Si' (Requerimiento)" required>

            <button type="submit">Registrar Nuevo Ticket</button>
        </form>
        <div style="text-align: center; margin-top: 10px;">
            <a href="listar_tickets.php" style="color: #666; text-decoration: none;">← Cancelar y volver al historial</a>
        </div>
    </div>
</body>
</html>