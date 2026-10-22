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
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); font-size: 13px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #0056b3; color: white; }
        .btn-rojo { background: #dc3545; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px; }
        .btn-rojo:hover { background: #c82333; }
        select, input { padding: 4px; font-size: 12px; }
        .btn-ok { padding: 5px 8px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; }
        .btn-info { padding: 5px 8px; background: #17a2b8; color: white; border: none; border-radius: 3px; cursor: pointer; }
        .badge { background: #e9ecef; padding: 3px 6px; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="cabecera">
        <h2>Panel de Control - Administrador (ITIL)</h2>
        <div>
            <a href="gestion_usuarios.php" style="background: #17a2b8; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px; margin-right: 10px;">👥 Gestionar Usuarios</a>
            <a href="perfil.php" style="background: #ffc107; color: #212529; padding: 8px 12px; text-decoration: none; border-radius: 5px; margin-right: 10px; font-weight: bold;">⚙️ Mi Perfil</a>
            <a href="../index.php?accion=logout" class="btn-rojo">Cerrar Sesión</a>
        </div>
    </div>
    
    <!-- CUADRO DE FILTROS PARA EXPORTACIÓN -->
    <div style="background: white; padding: 20px; margin-top: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <h3 style="margin-top: 0;">Generar Reporte Personalizado</h3>
        <form action="../index.php" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
            <input type="hidden" name="accion" value="exportar">
            
            <div>
                <label><strong>Usuario:</strong></label><br>
                <input type="text" name="usuario" placeholder="Ej. Prueba" style="padding: 6px; width: 150px;">
            </div>
            <div>
                <label><strong>Estado:</strong></label><br>
                <select name="estado" style="padding: 6px; width: 120px;">
                    <option value="">Todos</option>
                    <option value="Abierto">Abierto</option>
                    <option value="En proceso">En proceso</option>
                    <option value="Finalizado">Finalizado</option>
                </select>
            </div>
            <div>
                <label><strong>Desde:</strong></label><br>
                <input type="date" name="fecha_inicio" style="padding: 6px;">
            </div>
            <div>
                <label><strong>Hasta:</strong></label><br>
                <input type="date" name="fecha_fin" style="padding: 6px;">
            </div>
            <div>
                <button type="submit" name="formato" value="pdf" style="background: #17a2b8; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;">📄 Descargar PDF</button>
                <button type="submit" name="formato" value="excel" style="background: #28a745; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer;">📊 Descargar Excel</button>
            </div>
        </form>
    </div>

    <!-- MENSAJES DE ÉXITO O ERROR -->
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'actualizado'): ?>
        <p style='color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin-top:15px;'>Estado o clasificación del ticket actualizada con éxito.</p>
    <?php endif; ?>
    
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'asignado'): ?>
        <p style='color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin-top:15px;'>Técnico asignado correctamente al ticket.</p>
    <?php endif; ?>

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'vacio'): ?>
        <p style='color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin-top:15px;'>No se encontraron tickets con esos filtros para exportar.</p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha y Hora</th>
                <th>Solicitante</th>
                <th>Clasificación ITIL & Asunto</th>
                <th>Descripción & Código</th>
                <th>Estado & SLA / Prioridad</th>
                <th>Asignar Técnico</th>
                <th>Clasificar & Estado</th>
                <th>Chat (Auditoría)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $gestor = new GestorDeTickets();
            $tecnicos = $gestor->obtenerTecnicos();
            $todosLosTickets = $gestor->obtenerTodosLosTickets();

            if (count($todosLosTickets) > 0) {
                foreach ($todosLosTickets as $ticket) {
                    echo "<tr>";
                    echo "<td>#" . htmlspecialchars($ticket['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($ticket['fecha_registro']) . "</td>";
                    
                    // Solicitante y correo
                    echo "<td><strong>" . htmlspecialchars($ticket['nombre_usuario']) . "</strong><br><small style='color:blue;'>" . htmlspecialchars($ticket['correo']) . "</small></td>";
                    
                    // Clasificación y Asunto
                    $badgeColor = ($ticket['tipo'] === 'Incidente') ? '#dc3545' : (($ticket['tipo'] === 'Requerimiento') ? '#ffc107' : '#6c757d');
                    echo "<td><span class='badge' style='background: $badgeColor; color: " . ($ticket['tipo'] === 'Requerimiento' ? '#212529' : 'white') . ";'>" . htmlspecialchars($ticket['tipo']) . "</span><br><strong>" . htmlspecialchars($ticket['asunto']) . "</strong></td>";
                    
                    // Descripción y Código de barras
                    echo "<td>" . nl2br(htmlspecialchars($ticket['descripcion'])) . "<br><br><small><strong>Cod. Barras:</strong> " . ($ticket['codigo_barras'] ? htmlspecialchars($ticket['codigo_barras']) : 'N/A') . "</small></td>";
                    
                    // Estado y SLA / Prioridad
                    $colorEstado = $ticket['estado'] === 'Finalizado' ? 'green' : ($ticket['estado'] === 'En proceso' ? 'orange' : 'black');
                    echo "<td><span style='color: $colorEstado; font-weight: bold;'>" . htmlspecialchars($ticket['estado']) . "</span><br><small>SLA: " . htmlspecialchars($ticket['tiempo_resolucion']) . "</small></td>";
                    
                    // FORMULARIO PARA ASIGNAR TÉCNICO
                    echo "<td>
                            <form action='../index.php?accion=asignar_tecnico' method='POST' style='display: flex; flex-direction: column; gap: 5px;'>
                                <input type='hidden' name='id_ticket' value='" . $ticket['id'] . "'>
                                <select name='id_tecnico' style='width: 100%;'>
                                    <option value=''>-- Sin Asignar --</option>";
                                    foreach ($tecnicos as $tec) {
                                        $selected = (isset($ticket['id_tecnico']) && $ticket['id_tecnico'] == $tec['id_usuario']) ? 'selected' : '';
                                        echo "<option value='" . $tec['id_usuario'] . "' $selected>" . htmlspecialchars($tec['nombre']) . "</option>";
                                    }
                    echo "      </select>
                                <button type='submit' class='btn-info'>Asignar</button>
                            </form>
                          </td>";

                    // FORMULARIO PARA CLASIFICAR ITIL Y CAMBIAR ESTADO
                    echo "<td>
                            <form action='../index.php?accion=actualizar_estado' method='POST' style='display: flex; flex-direction: column; gap: 5px;'>
                                <input type='hidden' name='id_ticket' value='" . $ticket['id'] . "'>
                                <label style='font-size: 11px; margin:0;'>Tipo ITIL:</label>
                                <select name='nuevo_tipo' required>
                                    <option value='Por Clasificar' " . ($ticket['tipo'] == 'Por Clasificar' ? 'selected' : '') . ">-- Seleccionar --</option>
                                    <option value='Incidente' " . ($ticket['tipo'] == 'Incidente' ? 'selected' : '') . ">Incidente</option>
                                    <option value='Requerimiento' " . ($ticket['tipo'] == 'Requerimiento' ? 'selected' : '') . ">Requerimiento</option>
                                </select>
                                <label style='font-size: 11px; margin:0;'>Estado:</label>
                                <select name='nuevo_estado' required>
                                    <option value='Abierto' " . ($ticket['estado'] == 'Abierto' ? 'selected' : '') . ">Abierto</option>
                                    <option value='En proceso' " . ($ticket['estado'] == 'En proceso' ? 'selected' : '') . ">En proceso</option>
                                    <option value='Finalizado' " . ($ticket['estado'] == 'Finalizado' ? 'selected' : '') . ">Finalizado</option>
                                </select>
                                <button type='submit' class='btn-ok'>Actualizar</button>
                            </form>
                          </td>";

                    // BOTÓN DE ACCESO AL CHAT PARA EL ADMINISTRADOR (MODO OBSERVACIÓN)
                    echo "<td style='text-align: center;'>
                            <a href='ver_ticket.php?id=" . $ticket['id'] . "' style='background: #17a2b8; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;'>🔍 Ver Chat</a>
                          </td>";

                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9' style='text-align: center;'>No hay tickets registrados en el sistema.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>