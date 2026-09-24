<?php
session_start();
// Seguridad para que no entren si no están logueados
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}
// Seguridad adicional: Si el Admin intenta entrar aquí por error, lo mandamos a su panel
if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    header("Location: panel_admin.php");
    exit();
}
require_once '../model/GestorDeTickets.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Historial - Mesa de Ayuda</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f9; }
        .cabecera { display: flex; justify-content: space-between; align-items: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { border: 1px solid #ccc; padding: 12px; text-align: left; }
        th { background-color: #0056b3; color: white; }
        .btn { display: inline-block; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-top: 15px;}
        .btn-verde { background: #28a745; color: white; }
        .btn-rojo { background: #dc3545; color: white; float: right;}
    </style>
</head>
<body>
    <div class="cabecera">
        <h2>Historial de: <?php echo htmlspecialchars($_SESSION['nombre']); ?></h2>
        <a href="../index.php?accion=logout" class="btn btn-rojo">Cerrar Sesión</a>
    </div>
    
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'exito'): ?>
        <p style='color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px;'>¡Ticket registrado exitosamente!</p>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th>Clasificación ITIL</th>
                <th>Estado del Ticket</th>
                <th>SLA Prometido</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $gestor = new GestorDeTickets();
            // Ejecutamos la consulta usando el ID del usuario logueado
            $historial = $gestor->obtenerHistorialPorUsuario($_SESSION['id_usuario']);

            // Si encontró tickets, los dibuja uno por uno
            if (count($historial) > 0) {
                foreach ($historial as $ticket) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($ticket['tipo']) . "</td>";
                    
                    // Colores dinámicos para los estados que asigne el Administrador
                    $colorEstado = $ticket['estado'] === 'Finalizado' ? 'green' : ($ticket['estado'] === 'En proceso' ? 'orange' : 'black');
                    echo "<td style='color: $colorEstado; font-weight: bold;'>" . htmlspecialchars($ticket['estado']) . "</td>";
                    
                    echo "<td>" . htmlspecialchars($ticket['tiempo_resolucion']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3' style='text-align: center;'>Aún no tienes tickets registrados.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    
    <a href="crear_ticket.php" class="btn btn-verde">+ Crear Nuevo Ticket</a>
</body>
</html>