<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login.php");
    exit();
}
require_once '../model/GestorDeTickets.php';

$gestor = new GestorDeTickets();

// Filtros para el cliente
$estadoFiltro = $_GET['estado'] ?? '';
$fechaInicioFiltro = $_GET['fecha_inicio'] ?? '';
$fechaFinFiltro = $_GET['fecha_fin'] ?? '';

$misTickets = $gestor->obtenerHistorialPorUsuarioFiltrado($_SESSION['id_usuario'], $estadoFiltro, $fechaInicioFiltro, $fechaFinFiltro);

// Clasificación de tickets en 3 bloques
$ticketsEnEspera = [];
$ticketsEnProceso = [];
$ticketsFinalizados = [];

foreach ($misTickets as $t) {
    if ($t['estado'] === 'Finalizado') {
        $ticketsFinalizados[] = $t;
    } elseif ($t['estado'] === 'En proceso' || !empty($t['id_tecnico'])) {
        $ticketsEnProceso[] = $t;
    } else {
        $ticketsEnEspera[] = $t;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Tickets - Mesa de Ayuda</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f9; }
        .cabecera { display: flex; justify-content: space-between; align-items: center; background: #343a40; color: white; padding: 15px; border-radius: 5px; }
        .cabecera h2 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); font-size: 14px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; vertical-align: top; }
        th { background-color: #0056b3; color: white; }
        .btn-rojo { background: #dc3545; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px; }
        .btn-crear { background: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; margin-top: 20px; }
        .btn-chat { background: #17a2b8; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block; }
        .badge { background: #e9ecef; padding: 3px 6px; border-radius: 4px; font-weight: bold; }
        .badge-tec { background: #d1ecf1; color: #0c5460; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; display: inline-block; margin-top: 5px; }
        .seccion-titulo { margin-top: 30px; padding: 10px; background: #e9ecef; border-left: 5px solid #0056b3; border-radius: 3px; }
        .seccion-finalizados { border-left-color: #28a745; }
        .seccion-espera { border-left-color: #ffc107; }
        .alerta-nuevo { background: #28a745; color: white; padding: 3px 7px; border-radius: 12px; font-size: 11px; font-weight: bold; display: block; margin-bottom: 5px; text-align: center; }
    </style>
</head>
<body>
    <div class="cabecera">
        <h2>Panel de Usuario - Mis Tickets</h2>
        <div>
            <a href="perfil.php" style="background: #ffc107; color: #212529; padding: 8px 12px; text-decoration: none; border-radius: 5px; margin-right: 10px; font-weight: bold;">⚙️ Mi Perfil</a>
            <a href="../index.php?accion=logout" class="btn-rojo">Cerrar Sesión</a>
        </div>
    </div>

    <main>
        <a href="crear_ticket.php" class="btn-crear">➕ Crear Nuevo Ticket</a>

        <!-- CUADRO DE BÚSQUEDA PARA EL CLIENTE -->
        <div style="background: white; padding: 20px; margin-top: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0;">Filtrar Mis Tickets</h3>
            <form action="listar_tickets.php" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
                <div>
                    <label><strong>Estado:</strong></label><br>
                    <select name="estado" style="padding: 6px; width: 140px;">
                        <option value="">Todos</option>
                        <option value="Abierto" <?php echo $estadoFiltro === 'Abierto' ? 'selected' : ''; ?>>Abierto</option>
                        <option value="En proceso" <?php echo $estadoFiltro === 'En proceso' ? 'selected' : ''; ?>>En proceso</option>
                        <option value="Finalizado" <?php echo $estadoFiltro === 'Finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                    </select>
                </div>
                <div>
                    <label><strong>Desde:</strong></label><br>
                    <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($fechaInicioFiltro); ?>" style="padding: 6px;">
                </div>
                <div>
                    <label><strong>Hasta:</strong></label><br>
                    <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($fechaFinFiltro); ?>" style="padding: 6px;">
                </div>
                <div>
                    <button type="submit" style="background: #007bff; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">🔍 Buscar</button>
                    <a href="listar_tickets.php" style="background: #6c757d; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px; font-size: 12px; display: inline-block;">Limpiar</a>
                </div>
            </form>
        </div>

        <!-- BLOQUE 1: TICKETS EN ATENCIÓN / EN PROCESO -->
        <h3 class="seccion-titulo">🎧 1. Tickets en Atención (En Proceso)</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Clasificación & Asunto</th>
                    <th>Técnico Especialista</th>
                    <th>Estado & SLA</th>
                    <th>Acción / Chat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($ticketsEnProceso) > 0): ?>
                    <?php foreach ($ticketsEnProceso as $ticket): ?>
                        <?php 
                            $ultimoMsg = $gestor->obtenerUltimoComentario($ticket['id']);
                            $tieneMensajeNuevo = ($ultimoMsg && $ultimoMsg['id_usuario'] != $_SESSION['id_usuario']);
                        ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($ticket['id']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['fecha_registro']); ?></td>
                            <td>
                                <span class='badge'><?php echo htmlspecialchars($ticket['tipo']); ?></span><br>
                                <strong><?php echo htmlspecialchars($ticket['asunto']); ?></strong>
                            </td>
                            <td>
                                <span class="badge-tec">👤 <?php echo htmlspecialchars($ticket['nombre_tecnico'] ?? 'Asignando...'); ?></span>
                            </td>
                            <td>
                                <span style='color: orange; font-weight: bold;'><?php echo htmlspecialchars($ticket['estado']); ?></span>
                            </td>
                            <td style='text-align: center;'>
                                <?php if ($tieneMensajeNuevo): ?>
                                    <span class="alerta-nuevo">🟢 ¡Respuesta nueva!</span>
                                <?php endif; ?>
                                <a href='ver_ticket.php?id=<?php echo $ticket['id']; ?>' class='btn-chat'>💬 Abrir Chat</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan='6' style='text-align: center; color: #666;'>No hay tickets en proceso con estos filtros.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- BLOQUE 2: TICKETS PENDIENTES / EN ESPERA DE ASIGNACIÓN -->
        <h3 class="seccion-titulo seccion-espera">⏳ 2. Tickets en Espera de Asignación</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Clasificación & Asunto</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($ticketsEnEspera) > 0): ?>
                    <?php foreach ($ticketsEnEspera as $ticket): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($ticket['id']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['fecha_registro']); ?></td>
                            <td>
                                <span class='badge'><?php echo htmlspecialchars($ticket['tipo']); ?></span><br>
                                <strong><?php echo htmlspecialchars($ticket['asunto']); ?></strong>
                            </td>
                            <td><?php echo nl2br(htmlspecialchars($ticket['descripcion'])); ?></td>
                            <td><span style='color: #6c757d; font-weight: bold;'><?php echo htmlspecialchars($ticket['estado']); ?></span></td>
                            <td style='text-align: center;'>
                                <a href='ver_ticket.php?id=<?php echo $ticket['id']; ?>' class='btn-chat' style='background: #6c757d;'>📄 Ver Detalle</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan='6' style='text-align: center; color: #666;'>No hay tickets pendientes con estos filtros.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- BLOQUE 3: TICKETS FINALIZADOS -->
        <h3 class="seccion-titulo seccion-finalizados">✅ 3. Historial de Tickets Finalizados</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Clasificación & Asunto</th>
                    <th>Técnico que Atendió</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($ticketsFinalizados) > 0): ?>
                    <?php foreach ($ticketsFinalizados as $ticket): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($ticket['id']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['fecha_registro']); ?></td>
                            <td>
                                <span class='badge'><?php echo htmlspecialchars($ticket['tipo']); ?></span><br>
                                <strong><?php echo htmlspecialchars($ticket['asunto']); ?></strong>
                            </td>
                            <td>
                                <span class="badge-tec" style="background: #e2e3e5; color: #383d41;">👤 <?php echo htmlspecialchars($ticket['nombre_tecnico'] ?? 'N/A'); ?></span>
                            </td>
                            <td><span style='color: green; font-weight: bold;'><?php echo htmlspecialchars($ticket['estado']); ?></span></td>
                            <td style='text-align: center;'>
                                <a href='ver_ticket.php?id=<?php echo $ticket['id']; ?>' class='btn-chat' style='background: #28a745;'>📜 Ver Historial / Chat</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan='6' style='text-align: center; color: #666;'>No hay tickets finalizados con estos filtros.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>