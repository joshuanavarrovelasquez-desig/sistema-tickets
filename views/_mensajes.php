<?php
/** Avisos genéricos que se muestran según ?mensaje= (los de éxito propios de cada vista siguen en ella). */
$avisos = [
    'exito'            => ['ok',    'Ticket registrado con éxito.'],
    'error'            => ['error', 'No se pudo completar la operación. Inténtalo de nuevo.'],
    'datos_invalidos'  => ['error', 'Revisa los datos ingresados (correo válido, campos obligatorios y contraseña de mínimo 6 caracteres).'],
    'correo_duplicado' => ['error', 'Ya existe un usuario con ese correo.'],
    'error_propio'     => ['error', 'No puedes eliminar tu propia cuenta ni quitarte el rol de administrador.'],
    'sin_permiso'      => ['error', 'No tienes permiso para realizar esa acción.'],
    'adjunto_invalido' => ['error', 'El archivo adjunto no es válido (máx. 5 MB; jpg, png, gif, pdf, txt, doc, docx, xls, xlsx).'],
];
$clave = $_GET['mensaje'] ?? '';
if (isset($avisos[$clave])):
    [$tipoAviso, $textoAviso] = $avisos[$clave];
    $estilo = $tipoAviso === 'ok'
        ? 'color:#155724; background:#d4edda;'
        : 'color:#721c24; background:#f8d7da;';
?>
    <p style="<?= $estilo ?> padding:10px; border-radius:5px; margin:15px 0;"><?= htmlspecialchars($textoAviso) ?></p>
<?php endif; ?>
