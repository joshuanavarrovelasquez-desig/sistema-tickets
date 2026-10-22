<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'tecnico') {
    header("Location: login.php");
    exit();
}
require_once '../model/GestorDeTickets.php';
$gestor = new GestorDeTickets();
$misTicketsTecnico = $gestor->obtenerTicketsPorTecnico($_SESSION['id_usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Técnico - Mesa de Ayuda</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f9; }
        .cabecera { display: flex; justify-content: space-between; align-items: center; background: #343a40; color: white; padding: 15px; border-radius: 5px; }
        .cabecera h2 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); font-size: 14px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; vertical-align: top; }
        th { background-color: #0056b3; color: white; }
        .btn-rojo { background: #dc3545; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px; }
        .btn-ok { padding: 6px 12px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; font-weight: bold; }
        .badge { background: #e9ecef; padding: 3px 6px; border-radius: 4px; font-weight: bold; }
        .btn-chat { background: #17a2b8; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block; }
        .alerta-nuevo { background: #28a745; color: white; padding: 3px 7px; border-radius: 12px; font-size: 11px; font-weight: bold; display: block; margin-bottom: 5px; text-align: center; }
    </style>
</head>
<body>
    <div class="cabecera">
        <h2>Panel del Técnico - Asignaciones Prioritarias</h2>
        <div>
            <a href="perfil.php" style="background: #ffc107; color: #212529; padding: 8px 12px; text-decoration: none; border-radius: 5px; margin-right: 10px; font-weight: bold;">⚙️ Mi Perfil</a>
            <a href="../index.php?accion=logout" class="btn-rojo">Cerrar Sesión</a>
        </div>
    </div>

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'actualizado'): ?>
        <p style='color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin-top:15px;'>Estado del ticket actualizado correctamente.</p>
    <?php endif; ?>

    <h3 style="margin-top: 30px;">⚡ Mis Tickets Asignados (Ordenados por Prioridad / SLA)</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Solicitante</th>
                <th>Clasificación & Asunto</th>
                <th>Descripción & Asset</th>
                <th>Plazo Límite (SLA)</th>
                <th>Estado Actual</th>
                <th>Acción / Estado</th>
                <th>Chat</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($misTicketsTecnico) > 0): ?>
                <?php foreach ($misTicketsTecnico as $ticket): ?>
                    <?php 
                        $ultimoMsg = $gestor->obtenerUltimoComentario($ticket['id']);
                        $tieneMensajeNuevo = ($ultimoMsg && $ultimoMsg['id_usuario'] != $_SESSION['id_usuario']);
                        $badgeColor = ($ticket['tipo'] === 'Incidente') ? '#dc3545' : (($ticket['tipo'] === 'Requerimiento') ? '#ffc107' : '#6c757d');
                    ?>
                    <tr>
                        <td><strong>#<?php echo htmlspecialchars($ticket['id']); ?></strong></td>
                        <td>
                            <?php echo htmlspecialchars($ticket['nombre_usuario']); ?><br>
                            <small style="color: blue;"><?php echo htmlspecialchars($ticket['correo']); ?></small>
                        </td>
                        <td>
                            <span class='badge' style='background: <?php echo $badgeColor; ?>; color: <?php echo $ticket['tipo'] === 'Requerimiento' ? '#212529' : 'white'; ?>;'><?php echo htmlspecialchars($ticket['tipo']); ?></span><br>
                            <strong><?php echo htmlspecialchars($ticket['asunto']); ?></strong>
                        </td>
                        <td>
                            <?php echo nl2br(htmlspecialchars($ticket['descripcion'])); ?><br><br>
                            <small><strong>Cod. Barras:</strong> <?php echo $ticket['codigo_barras'] ? htmlspecialchars($ticket['codigo_barras']) : 'N/A'; ?></small>
                        </td>
                        <td>
                            <span style="color: #d9534f; font-weight: bold;">⏳ <?php echo htmlspecialchars($ticket['tiempo_resolucion']); ?></span>
                        </td>
                        <td>
                            <span style="font-weight: bold; color: <?php echo $ticket['estado'] === 'Finalizado' ? 'green' : 'orange'; ?>;">
                                <?php echo htmlspecialchars($ticket['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <form action="../index.php?accion=actualizar_estado" method="POST" style="display: flex; flex-direction: column; gap: 5px;">
                                <input type="hidden" name="id_ticket" value="<?php echo $ticket['id']; ?>">
                                <select name="nuevo_estado" style="padding: 5px;">
                                    <option value="Abierto" <?php echo $ticket['estado'] === 'Abierto' ? 'selected' : ''; ?>>Abierto</option>
                                    <option value="En proceso" <?php echo $ticket['estado'] === 'En proceso' ? 'selected' : ''; ?>>En proceso</option>
                                    <option value="Finalizado" <?php echo $ticket['estado'] === 'Finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                                </select>
                                <button type="submit" class="btn-ok">Actualizar</button>
                            </form>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($tieneMensajeNuevo): ?>
                                <span class="alerta-nuevo">🟢 ¡Respuesta nueva!</span>
                            <?php endif; ?>
                            <a href="ver_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn-chat">💬 Abrir Chat</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" style="text-align: center; color: #666;">No tienes tickets asignados en este momento.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>