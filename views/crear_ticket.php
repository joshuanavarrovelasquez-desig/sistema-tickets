<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mesa de Ayuda TI</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f9;}
        .formulario { max-width: 400px; padding: 20px; background: white; border: 1px solid #ccc; border-radius: 8px; }
        input, select, button { width: 100%; margin-bottom: 15px; padding: 10px; box-sizing: border-box; }
        button { background-color: #0056b3; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #004494; }
    </style>
</head>
<body>
    <h2>Mesa de Ayuda TI - Instituto Superior Técnico Oxapampa</h2>
    <div class="formulario">
        <!-- El formulario envía los datos hacia el index.php en la raíz -->
        <form action="../index.php?accion=crear" method="POST">
            
            <label for="usuario">Usuario Solicitante:</label>
            <input type="text" name="usuario" id="usuario" placeholder="Ej. Juan Pérez" required>

            <label for="tipo">Clasificación ITIL:</label>
            <select name="tipo" id="tipo">
                <option value="Incidente">Incidente (Falla técnica)</option>
                <option value="Requerimiento">Requerimiento (Solicitud de equipo/acceso)</option>
            </select>

            <label for="detalle">Detalle de Urgencia / Aprobación:</label>
            <input type="text" name="detalle" id="detalle" placeholder="Escribe 'Alta' (Incidente) o 'Si' (Requerimiento)" required>

            <button type="submit">Registrar Ticket</button>
        </form>
    </div>
</body>
</html>