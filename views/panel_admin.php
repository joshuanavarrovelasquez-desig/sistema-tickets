<?php
session_start();
// SEGURIDAD: Solo el admin puede entrar a esta pantalla
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
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f9; }
        .cabecera { display: flex; justify-content: space-between; align-items: center; background: #343a40; color: white; padding: 15px; border-radius: 5px; }
        .cabecera h2 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ccc; padding: 12px; text-align: left; }
        th { background-color: #0056b3; color: white; }
        .btn-rojo { background: #dc3545; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; }
        .btn-rojo:hover { background: #c82333; }
        select { padding: 5px; }
        .btn-ok { padding: 5px 10px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="cabecera">
        <h2>Panel de Control - Administrador de Tickets</h2>
        <a href="../index.php?accion=logout" class="btn-rojo">Cerrar Sesión</a>
    </div>
    
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'actualizado'): ?>
        <p style='color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin-top:20px;'>Estado del ticket actualizado en el sistema.</p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th> <!-- Vemos quién lo pidió -->
                <th>Clasificación</th>
                <th>Estado</th>
                <th>SLA</th>
                <th>Cambiar Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $gestor = new GestorDeTickets();
            // El admin usa un método especial para ver TODOS los tickets
            $todosLosTickets = $gestor->obtenerTodosLosTickets();

            if (count($todosLosTickets) > 0) {
                foreach ($todosLosTickets as $ticket) {
                    echo "<tr>";
                    echo "<td>#" . htmlspecialchars($ticket['id']) . "</td>";
                    
                    // Mostramos el nombre del usuario (cruzado desde la base de datos)
                    echo "<td><strong>" . htmlspecialchars($ticket['nombre_usuario']) . "</strong></td>";
                    
                    echo "<td>" . htmlspecialchars($ticket['tipo']) . "</td>";
                    
                    $colorEstado = $ticket['estado'] === 'Finalizado' ? 'green' : ($ticket['estado'] === 'En proceso' ? 'orange' : 'black');
                    echo "<td style='color: $colorEstado; font-weight: bold;'>" . htmlspecialchars($ticket['estado']) . "</td>";
                    
                    echo "<td>" . htmlspecialchars($ticket['tiempo_resolucion']) . "</td>";
                    
                    // Formulario de edición de estado
                    echo "<td>
                            <form action='../index.php?accion=actualizar_estado' method='POST' style='display: flex; gap: 5px;'>
                                <input type='hidden' name='id_ticket' value='" . $ticket['id'] . "'>
                                <select name='nuevo_estado'>
                                    <option value='Abierto' " . ($ticket['estado'] == 'Abierto' ? 'selected' : '') . ">Abierto</option>
                                    <option value='En proceso' " . ($ticket['estado'] == 'En proceso' ? 'selected' : '') . ">En proceso</option>
                                    <option value='Finalizado' " . ($ticket['estado'] == 'Finalizado' ? 'selected' : '') . ">Finalizado</option>
                                </select>
                                <button type='submit' class='btn-ok'>Ok</button>
                            </form>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align: center;'>No hay tickets en el sistema.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>