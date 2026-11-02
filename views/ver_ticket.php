<?php if (!defined('APP_INICIADA')) { http_response_code(403); exit('Acceso no permitido.'); } ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle y Conversación - Ticket #<?= (int)$idTicket ?></title>
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
    <?php require __DIR__ . '/_mensajes.php'; ?>
    <div class="cabecera">
        <h2>Detalle del Ticket #<?= (int)$ticketActual['id'] ?> - <?php echo htmlspecialchars($ticketActual['asunto'] ?? 'Consulta'); ?></h2>
        <a href="index.php?accion=logout" style="background: #dc3545; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px;">Cerrar Sesión</a>
    </div>

    <div class="contenedor-chat">
        <a href="index.php?accion=<?= htmlspecialchars($volverAccion) ?>" class="btn-volver">← Volver</a>

        <p><strong>Clasificación:</strong> <?php echo htmlspecialchars($ticketActual['tipo']); ?> | <strong>Estado:</strong> <?php echo htmlspecialchars($ticketActual['estado']); ?></p>
        <p><strong>Descripción inicial:</strong><br><?php echo nl2br(htmlspecialchars($ticketActual['descripcion'])); ?></p>
        <?php if (!empty($ticketActual['archivo_adjunto'])): ?>
            <p><strong>Adjunto:</strong> <a href="uploads/<?= rawurlencode($ticketActual['archivo_adjunto']) ?>" target="_blank" rel="noopener">📎 <?= htmlspecialchars($ticketActual['archivo_adjunto']) ?></a></p>
        <?php endif; ?>
        
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

        <?php if (isset($sesionRol) && $sesionRol !== 'admin'): ?>
            <form action="index.php?accion=agregar_comentario" method="POST">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="id_ticket" value="<?= (int)$idTicket ?>">
                <label><strong>Escribe tu respuesta o mensaje:</strong></label>
                <textarea name="mensaje" rows="3" required placeholder="Escribe detalles, avances o preguntas sobre el ticket..."></textarea>
                <button type="submit">Enviar Mensaje</button>
            </form>
        <?php else: ?>
            <p style="background: #e9ecef; padding: 12px; border-radius: 4px; color: #495057; text-align: center; border: 1px dashed #ced4da; margin-top: 20px;">
                <em>🔍 <strong>Modo de Observación (Administrador):</strong> Puedes supervisar el historial y la conversación entre el cliente y el técnico, pero no tienes permitido enviar mensajes.</em>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>