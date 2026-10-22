<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

require_once '../model/GestorDeTickets.php';
$idTicket = $_GET['id'] ?? null;

if (!$idTicket) {
    // Redirección inteligente según el rol si falta el ID
    if ($_SESSION['rol'] === 'admin') {
        header("Location: panel_admin.php");
    } elseif ($_SESSION['rol'] === 'tecnico') {
        header("Location: panel_tecnico.php");
    } else {
        header("Location: listar_tickets.php");
    }
    exit();
}

$gestor = new GestorDeTickets();
$comentarios = $gestor->obtenerComentariosPorTicket($idTicket);

// Obtenemos los datos del ticket de manera segura según el rol
$ticketActual = null;
if ($_SESSION['rol'] === 'admin' || $_SESSION['rol'] === 'tecnico') {
    $todos = $gestor->obtenerTodosLosTickets();
    foreach ($todos as $t) {
        if ($t['id'] == $idTicket) {
            $ticketActual = $t;
            break;
        }
    }
} else {
    // Si es cliente, buscamos en su propio historial
    $historial = $gestor->obtenerHistorialPorUsuario($_SESSION['id_usuario']);
    foreach ($historial as $t) {
        if ($t['id'] == $idTicket) {
            // Re-mapeamos los datos básicos para estandarizar
            $ticketActual = $t;
            $ticketActual['nombre_usuario'] = $_SESSION['nombre'];
            break;
        }
    }
}

if (!$ticketActual) {
    echo "<h3 style='color:red; text-align:center; margin-top:50px;'>Ticket no encontrado o no tienes permisos para visualizarlo.</h3>";
    echo "<div style='text-align:center;'><a href='listar_tickets.php'>Regresar al historial</a></div>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle y Conversación - Ticket #<?php echo $idTicket; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f4f4f9; }
        .cabecera { background: #343a40; color: white; padding: 15px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; }
        .contenedor-chat { background: white; padding: 20px; margin-top: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .mensaje-box { padding: 12px; margin-bottom: 12px; border-radius: 6px; border: 1px solid #ddd; background: #fafafa; }
        .mensaje-box.admin { background: #e2f0d9; border-color: #c8e6c9; }
        .mensaje-box.tecnico { background: #d1ecf1; border-color: #bee5eb; }
        textarea { width: 100%; padding: 10px; margin-top: 10px; box-sizing: border-box; border-radius: 4px; border: 1px solid #ccc; resize: vertical; }
        button { background-color: #0056b3; color: white; border: none; padding: 10px 15px; cursor: pointer; font-weight: bold; border-radius: 4px; margin-top: 10px; }
        button:hover { background-color: #004494; }
        .btn-volver { display: inline-block; padding: 8px 12px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="cabecera">
        <h2>Detalle del Ticket #<?php echo $ticketActual['id']; ?> - <?php echo htmlspecialchars($ticketActual['asunto'] ?? 'Consulta'); ?></h2>
        <a href="../index.php?accion=logout" style="background: #dc3545; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px;">Cerrar Sesión</a>
    </div>

    <div class="contenedor-chat">
        <?php 
        // URL de retorno exacta según el rol del usuario logueado
        if ($_SESSION['rol'] === 'admin') {
            $volverUrl = 'panel_admin.php';
        } elseif ($_SESSION['rol'] === 'tecnico') {
            $volverUrl = 'panel_tecnico.php';
        } else {
            $volverUrl = 'listar_tickets.php';
        }
        ?>
        <a href="<?php echo $volverUrl; ?>" class="btn-volver">← Volver</a>

        <p><strong>Clasificación:</strong> <?php echo htmlspecialchars($ticketActual['tipo']); ?> | <strong>Estado:</strong> <?php echo htmlspecialchars($ticketActual['estado']); ?></p>
        <p><strong>Descripción inicial:</strong><br><?php echo nl2br(htmlspecialchars($ticketActual['descripcion'])); ?></p>
        
        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ccc;">

        <h3>Conversación y Notas del Caso</h3>

        <div style="max-height: 400px; overflow-y: auto; margin-bottom: 20px;">
            <?php if (count($comentarios) > 0): ?>
                <?php foreach ($comentarios as $com): ?>
                    <?php 
                        $claseRol = '';
                        if ($com['rol'] === 'admin') $claseRol = 'admin';
                        elseif ($com['rol'] === 'tecnico') $claseRol = 'tecnico';
                    ?>
                    <div class="mensaje-box <?php echo $claseRol; ?>">
                        <small style="color: #555;"><strong><?php echo htmlspecialchars($com['nombre_usuario']); ?></strong> (<em><?php echo ucfirst($com['rol']); ?></em>) - <?php echo $com['fecha_registro']; ?></small>
                        <p style="margin: 5px 0 0 0;"><?php echo nl2br(htmlspecialchars($com['mensaje'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #666; font-style: italic;">Aún no hay mensajes en este ticket. Escribe el primero abajo.</p>
            <?php endif; ?>
        </div>

        <!-- Formulario para enviar mensaje (Visible solo para Clientes y Técnicos) -->
        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] !== 'admin'): ?>
            <form action="../index.php?accion=agregar_comentario" method="POST">
                <input type="hidden" name="id_ticket" value="<?php echo $idTicket; ?>">
                <label><strong>Escribe tu respuesta o mensaje:</strong></label>
                <textarea name="mensaje" rows="3" required placeholder="Escribe detalles, avances o preguntas sobre el ticket..."></textarea>
                <button type="submit">Enviar Mensaje</button>
            </form>
        <?php else: ?>
            <!-- Aviso visual para el Administrador -->
            <p style="background: #e9ecef; padding: 12px; border-radius: 4px; color: #495057; text-align: center; border: 1px dashed #ced4da; margin-top: 20px;">
                <em>🔍 <strong>Modo de Observación (Administrador):</strong> Puedes supervisar el historial y la conversación entre el cliente y el técnico, pero no tienes permitido enviar mensajes.</em>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>