<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once '../model/GestorDeTickets.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - ITIL</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f9; }
        .cabecera { display: flex; justify-content: space-between; align-items: center; background: #343a40; color: white; padding: 15px; border-radius: 5px; }
        .cabecera h2 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); font-size: 14px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; vertical-align: top; }
        th { background-color: #0056b3; color: white; }
        .btn-rojo { background: #dc3545; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px; }
        .btn-rojo:hover { background: #c82333; }
        select { padding: 5px; }
        .btn-ok { padding: 5px 10px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; }
        .badge { background: #e9ecef; padding: 3px 6px; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="cabecera">
        <h2>Panel de Control - Administrador</h2>
        <div style="display: flex; gap: 10px;">
            <a href="../index.php?accion=exportar_pdf" style="background: #17a2b8; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px;">📄 Exportar PDF</a>
            <a href="../index.php?accion=exportar_excel" style="background: #28a745; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px;">📊 Exportar Excel</a>
            <a href="../index.php?accion=logout" class="btn-rojo">Cerrar Sesión</a>
        </div>
    </div>
    
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'actualizado'): ?>
        <p style='color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin-top:15px;'>Estado del ticket actualizado con éxito.</p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha y Hora</th>
                <th>Solicitante / Correo</th>
                <th>Clasificación & Asunto</th>
                <th>Descripción & Código</th>
                <th>Adjunto</th>
                <th>Estado & SLA</th>
                <th>Acción Admin</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $gestor = new GestorDeTickets();
            $todosLosTickets = $gestor->obtenerTodosLosTickets();

            if (count($todosLosTickets) > 0) {
                foreach ($todosLosTickets as $ticket) {
                    echo "<tr>";
                    echo "<td>#" . htmlspecialchars($ticket['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($ticket['fecha_registro']) . "</td>";
                    // Solicitante y correo
                    echo "<td><strong>" . htmlspecialchars($ticket['nombre_usuario']) . "</strong><br><small style='color:blue;'>" . htmlspecialchars($ticket['correo']) . "</small></td>";
                    
                    // Clasificación y Asunto
                    echo "<td><span class='badge'>" . htmlspecialchars($ticket['tipo']) . "</span><br><strong>" . htmlspecialchars($ticket['asunto']) . "</strong></td>";
                    
                    // Descripción y Código de barras
                    echo "<td>" . nl2br(htmlspecialchars($ticket['descripcion'])) . "<br><br><small><strong>Cod. Barras:</strong> " . ($ticket['codigo_barras'] ? htmlspecialchars($ticket['codigo_barras']) : 'N/A') . "</small></td>";
                    
                    // Archivo adjunto
                    echo "<td>";
                    if (!empty($ticket['archivo_adjunto'])) {
                        echo "<a href='../uploads/" . htmlspecialchars($ticket['archivo_adjunto']) . "' target='_blank'>Ver Foto/Archivo</a>";
                    } else {
                        echo "Ninguno";
                    }
                    echo "</td>";

                    // Estado y SLA
                    $colorEstado = $ticket['estado'] === 'Finalizado' ? 'green' : ($ticket['estado'] === 'En proceso' ? 'orange' : 'black');
                    echo "<td><span style='color: $colorEstado; font-weight: bold;'>" . htmlspecialchars($ticket['estado']) . "</span><br><small>" . htmlspecialchars($ticket['tiempo_resolucion']) . "</small></td>";
                    
                    // Formulario para cambiar estado
                    echo "<td>
                            <form action='../index.php?accion=actualizar_estado' method='POST' style='display: flex; flex-direction: column; gap: 5px;'>
                                <input type='hidden' name='id_ticket' value='" . $ticket['id'] . "'>
                                <select name='nuevo_estado'>
                                    <option value='Abierto' " . ($ticket['estado'] == 'Abierto' ? 'selected' : '') . ">Abierto</option>
                                    <option value='En proceso' " . ($ticket['estado'] == 'En proceso' ? 'selected' : '') . ">En proceso</option>
                                    <option value='Finalizado' " . ($ticket['estado'] == 'Finalizado' ? 'selected' : '') . ">Finalizado</option>
                                </select>
                                <button type='submit' class='btn-ok'>Actualizar</button>
                            </form>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' style='text-align: center;'>No hay tickets registrados en el sistema.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>