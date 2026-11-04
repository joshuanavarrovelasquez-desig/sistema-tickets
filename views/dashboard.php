<?php if (!defined('APP_INICIADA')) { http_response_code(403); exit('Acceso no permitido.'); } ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Sistema de Tickets</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f9; }
        .cabecera { display: flex; justify-content: space-between; align-items: center; background: #343a40; color: white; padding: 15px; border-radius: 5px; }
        .cabecera h2 { margin: 0; }
        .btn-rojo { background: #dc3545; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px; }
        .btn-azul { background: #0056b3; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px; margin-right: 10px; }
        .tarjetas { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 20px; }
        .tarjeta { flex: 1; min-width: 150px; background: white; padding: 18px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; border-top: 5px solid #0056b3; }
        .tarjeta .numero { font-size: 34px; font-weight: bold; margin: 5px 0; }
        .tarjeta small { color: #666; }
        .t-rojo { border-top-color: #dc3545; } .t-amarillo { border-top-color: #ffc107; }
        .t-verde { border-top-color: #28a745; } .t-gris { border-top-color: #6c757d; }
        .graficos { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 20px; }
        .grafico { flex: 1; min-width: 300px; background: white; padding: 18px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .grafico h3 { margin-top: 0; }
    </style>
</head>
<body>
    <div class="cabecera">
        <h2>📊 Dashboard de Tickets</h2>
        <div>
            <a href="index.php?accion=panel_admin" class="btn-azul">← Volver al Panel de Tickets</a>
            <a href="index.php?accion=logout" class="btn-rojo">Cerrar Sesión</a>
        </div>
    </div>

    <div class="tarjetas">
        <div class="tarjeta"><small>Total de tickets</small><div class="numero"><?= (int)$totalTickets ?></div></div>
        <div class="tarjeta t-gris"><small>Por clasificar</small><div class="numero"><?= (int)$porClasificar ?></div></div>
        <div class="tarjeta t-amarillo"><small>Abiertos</small><div class="numero"><?= (int)$abiertos ?></div></div>
        <div class="tarjeta t-rojo"><small>Críticos abiertos (SLA 2 h)</small><div class="numero"><?= (int)$criticos ?></div></div>
        <div class="tarjeta t-verde"><small>Finalizados</small><div class="numero"><?= (int)$finalizados ?></div></div>
    </div>

    <div class="graficos">
        <div class="grafico"><h3>Tickets por estado</h3><canvas id="graficoEstado"></canvas></div>
        <div class="grafico"><h3>Tickets por tipo</h3><canvas id="graficoTipo"></canvas></div>
        <div class="grafico"><h3>Carga por técnico (sin finalizados)</h3><canvas id="graficoTecnico"></canvas></div>
    </div>

    <script src="assets/js/chart.umd.min.js"></script>
    <script>
        const porEstado  = <?= json_encode($porEstado, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
        const porTipo    = <?= json_encode($porTipo, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
        const porTecnico = <?= json_encode($porTecnico, JSON_HEX_TAG | JSON_HEX_AMP) ?>;

        function dibujar(id, tipo, datos, colores) {
            new Chart(document.getElementById(id), {
                type: tipo,
                data: {
                    labels: Object.keys(datos),
                    datasets: [{ data: Object.values(datos), backgroundColor: colores }]
                },
                options: {
                    plugins: { legend: { display: tipo === 'doughnut' } },
                    scales: tipo === 'bar' ? { y: { beginAtZero: true, ticks: { precision: 0 } } } : {}
                }
            });
        }

        dibujar('graficoEstado',  'doughnut', porEstado,  ['#ffc107', '#fd7e14', '#28a745']);
        dibujar('graficoTipo',    'doughnut', porTipo,    ['#6c757d', '#dc3545', '#17a2b8']);
        dibujar('graficoTecnico', 'bar',      porTecnico, '#0056b3');
    </script>
</body>
</html>