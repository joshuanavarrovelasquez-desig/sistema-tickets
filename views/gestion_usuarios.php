<?php if (!defined('APP_INICIADA')) { http_response_code(403); exit('Acceso no permitido.'); } ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - Mesa de Ayuda TI</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f9; }
        .cabecera { display: flex; justify-content: space-between; align-items: center; background: #343a40; color: white; padding: 15px; border-radius: 5px; }
        .cabecera h2 { margin: 0; }
        .btn-rojo { background: #dc3545; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px; }
        .btn-rojo:hover { background: #c82333; }
        .btn-azul { background: #0056b3; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px; }
        .btn-editar { background: #ffc107; color: #212529; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .btn-eliminar { background: #dc3545; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); font-size: 14px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; vertical-align: middle; }
        th { background-color: #0056b3; color: white; }
        .card-formulario { background: white; padding: 20px; margin-top: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input, select, button { padding: 8px; margin-top: 5px; box-sizing: border-box; }
        button { background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .badge { background: #e9ecef; padding: 3px 6px; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/_mensajes.php'; ?>
    <div class="cabecera">
        <h2>Gestión de Usuarios</h2>
        <div>
            <a href="index.php?accion=panel_admin" class="btn-azul">← Volver al Panel de Tickets</a>
            <a href="index.php?accion=logout" class="btn-rojo">Cerrar Sesión</a>
        </div>
    </div>

    <div class="card-formulario">
        <h3 style="margin-top: 0;"><?php echo $usuarioAEditar ? '✏️ Editar Usuario (ID #' . $usuarioAEditar->getId() . ')' : 'Registrar Nuevo Usuario'; ?></h3>
        <form action="index.php?accion=<?php echo $usuarioAEditar ? 'actualizar_usuario' : 'crear_usuario'; ?>" method="POST" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            
            <?php if ($usuarioAEditar): ?>
                <input type="hidden" name="id_usuario" value="<?php echo $usuarioAEditar->getId(); ?>">
            <?php endif; ?>

            <div>
                <label><strong>Nombre Completo:</strong></label><br>
                <input type="text" name="nombre" required value="<?php echo htmlspecialchars($usuarioAEditar?->getNombre() ?? ''); ?>" placeholder="Ej. Juan Pérez">
            </div>
            <div>
                <label><strong>Correo Electrónico:</strong></label><br>
                <input type="email" name="correo" required value="<?php echo htmlspecialchars($usuarioAEditar?->getCorreo() ?? ''); ?>" placeholder="correo@sistema.com">
            </div>
            <div>
                <label><strong>Contraseña:</strong></label><br>
                <input type="password" name="password" <?php echo $usuarioAEditar ? '' : 'required'; ?> placeholder="<?php echo $usuarioAEditar ? 'Dejar en blanco para no cambiar' : '******'; ?>">
            </div>
            <div>
                <label><strong>Rol:</strong></label><br>
                <select name="rol" required>
                    <option value="cliente" <?php echo ($usuarioAEditar?->getRol() === 'cliente') ? 'selected' : ''; ?>>Cliente / Usuario</option>
                    <option value="tecnico" <?php echo ($usuarioAEditar?->getRol() === 'tecnico') ? 'selected' : ''; ?>>Técnico de Soporte</option>
                    <option value="admin" <?php echo ($usuarioAEditar?->getRol() === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                </select>
            </div>
            <div>
                <button type="submit" style="background: <?php echo $usuarioAEditar ? '#ffc107; color: #212529;' : '#28a745;'; ?>"><?php echo $usuarioAEditar ? '💾 Actualizar Usuario' : '+ Guardar Usuario'; ?></button>
                <?php if ($usuarioAEditar): ?>
                    <a href="index.php?accion=gestion_usuarios" style="background: #6c757d; color: white; padding: 8px 12px; text-decoration: none; border-radius: 4px; font-size: 13px; display: inline-block; margin-left: 5px;">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'creado'): ?>
        <p style='color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin-top:15px;'>Usuario registrado con éxito.</p>
    <?php endif; ?>
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'actualizado'): ?>
        <p style='color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin-top:15px;'>Usuario actualizado con éxito.</p>
    <?php endif; ?>
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'eliminado'): ?>
        <p style='color: #721c24; background: #f8d7da; padding: 10px; border-radius: 5px; margin-top:15px;'>Usuario eliminado correctamente.</p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo Electrónico</th>
                <th>Rol</th>
                <th style="text-align: center;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usr): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($usr->getId()); ?></td>
                    <td><strong><?php echo htmlspecialchars($usr->getNombre()); ?></strong></td>
                    <td><?php echo htmlspecialchars($usr->getCorreo()); ?></td>
                    <td><span class="badge"><?php echo htmlspecialchars($usr->getRol()); ?></span></td>
                    <td style="text-align: center; white-space: nowrap;">
                        <a href="index.php?accion=gestion_usuarios&editar=<?php echo $usr->getId(); ?>" class="btn-editar">✏️ Editar</a>
                        <form action="index.php?accion=eliminar_usuario" method="POST" style="display:inline;" onsubmit="return confirm(<?= htmlspecialchars(json_encode('¿Estás seguro de eliminar al usuario ' . $usr->getNombre() . '?'), ENT_QUOTES) ?>);">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                            <input type="hidden" name="id_usuario" value="<?= (int)$usr->getId() ?>">
                            <button type="submit" class="btn-eliminar" style="border:none; cursor:pointer; margin-top:0;">❌ Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>