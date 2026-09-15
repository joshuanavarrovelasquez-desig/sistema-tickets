<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tickets Registrados</title>
</head>
<body style="font-family: Arial, sans-serif; margin: 40px;">
    <h2>Panel de Gestión</h2>
    
    <?php
        // Verificamos si la URL trae el mensaje de éxito desde el Controlador
        if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'exito') {
            echo "<p style='color: green; font-weight: bold;'>¡Ticket registrado exitosamente en XAMPP!</p>";
        }
    ?>
    
    <a href="crear_ticket.php">← Volver para crear otro ticket</a>
</body>
</html>