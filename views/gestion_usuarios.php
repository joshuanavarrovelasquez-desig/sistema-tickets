<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once '../model/GestorDeUsuarios.php';
$gestor = new GestorDeUsuarios();
$usuarios = $gestor->obtenerTodosLosUsuarios();
?>
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
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); font-size: 14px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #0056b3; color: white; }
        .card-formulario { background: white; padding: 20px; margin-top: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input, select, button { padding: 8px; margin-top: 5px; box-sizing: border-box; }
        button { background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .badge { background: #e9ecef; padding: 3px 6px; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="cabecera">
        <h2>Gestión de Usuarios</h2>
        <div>
            <a href="panel_admin.php" class="btn-azul">← Volver al Panel de Tickets</a>
            <a href="../index.php?accion=logout" class="btn-rojo">Cerrar Sesión</a>
        </div>
    </div>

    <!-- FORMULARIO PARA CREAR NUEVO USUARIO -->
    <div class="card-formulario">
        <h3 style="margin-top: 0;">Registrar Nuevo Usuario</h3>
        <form action="../index.php?accion=crear_usuario" method="POST" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
            <div>
                <label><strong>Nombre Completo:</strong></label><br>
                <input type="text" name="nombre" required placeholder="Ej. Juan Pérez">
            </div>
            <div>
                <label><strong>Correo Electrónico:</strong></label><br>
                <input type="email" name="correo" required placeholder="correo@sistema.com">
            </div>
            <div>
                <label><strong>Contraseña:</strong></label><br>
                <input type="password" name="password" required placeholder="******">
            </div>
            <div>
                <label><strong>Rol:</strong></label><br>
                <select name="rol" required>
                    <option value="cliente">Cliente / Usuario</option>
                    <option value="tecnico">Técnico de Soporte</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <div>
                <button type="submit">+ Guardar Usuario</button>
            </div>
        </form>
    </div>

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'creado'): ?>
        <p style='color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin-top:15px;'>Usuario registrado con éxito.</p>
    <?php endif; ?>

    <!-- TABLA DE USUARIOS REGISTRADOS -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo Electrónico</th>
                <th>Rol</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usr): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($usr['id_usuario']); ?></td>
                    <td><strong><?php echo htmlspecialchars($usr['nombre']); ?></strong></td>
                    <td><?php echo htmlspecialchars($usr['correo']); ?></td>
                    <td><span class="badge"><?php echo htmlspecialchars($usr['rol']); ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>