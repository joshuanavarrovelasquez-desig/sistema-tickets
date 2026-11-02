<?php if (!defined('APP_INICIADA')) { http_response_code(403); exit('Acceso no permitido.'); } ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - Mesa de Ayuda TI</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f9; }
        .cabecera { display: flex; justify-content: space-between; align-items: center; background: #343a40; color: white; padding: 15px; border-radius: 5px; }
        .cabecera h2 { margin: 0; }
        .formulario { max-width: 500px; padding: 20px; background: white; border: 1px solid #ccc; border-radius: 8px; margin: 20px auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input, button { width: 100%; margin-bottom: 15px; padding: 10px; box-sizing: border-box; font-family: Arial, sans-serif; }
        button { background-color: #0056b3; color: white; border: none; cursor: pointer; font-weight: bold; font-size: 16px; border-radius: 4px; }
        button:hover { background-color: #004494; }
        .btn-volver { display: inline-block; padding: 8px 12px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 15px; }
        .opcional { color: #666; font-size: 0.8em; }
    </style>
</head>
<body>
    <?php require __DIR__ . '/_mensajes.php'; ?>
    <div class="cabecera">
        <h2>Mi Perfil / Ajustes de Cuenta</h2>
        <a href="index.php?accion=logout" style="background: #dc3545; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px;">Cerrar Sesión</a>
    </div>

    <div class="formulario">
                <a href="index.php?accion=<?= htmlspecialchars($volverAccion) ?>" class="btn-volver">← Volver</a>

        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'actualizado'): ?>
            <p style='color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>¡Perfil actualizado con éxito!</p>
        <?php endif; ?>

        <form action="index.php?accion=actualizar_perfil" method="POST">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <label for="nombre">Nombre Completo:</label>
            <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($usuario->getNombre()); ?>" required>

            <label for="correo">Correo Electrónico:</label>
            <input type="email" name="correo" id="correo" value="<?php echo htmlspecialchars($usuario->getCorreo()); ?>" required>

            <label for="password">Nueva Contraseña <span class="opcional">(Déjalo en blanco si no deseas cambiarla)</span>:</label>
            <input type="password" name="password" id="password" placeholder="Escribe nueva contraseña solo si deseas cambiarla">

            <button type="submit">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>