<?php
// Copia este archivo como config.local.php y completa tus datos reales.
return [
    'url_base' => 'http://localhost/sistema_tickets',
    'correo' => [
        'activo'           => false,
        'host'             => 'smtp.gmail.com',
        'puerto'           => 587,
        'usuario'          => 'tucorreo@gmail.com',
        'clave'            => 'CLAVE_DE_APLICACION',
        'remitente'        => 'tucorreo@gmail.com',
        'nombre_remitente' => 'Sistema de Tickets',
    ],
];