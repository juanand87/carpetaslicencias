<?php
/**
 * Archivo de variables útiles y constantes
 * Puedes extender este archivo según necesites
 */

// Estados de solicitudes
define('SOLICITUD_ESTADOS', [
    'Pendiente',
    'Cargada',
    'Cargada con observaciones',
    'No encontrada',
    'Rechazada'
]);

// Tipos de plantillas
define('PLANTILLA_TIPOS', [
    'nueva_solicitud',
    'estado_cambio',
    'solicitud_cargada',
    'solicitud_rechazada'
]);

// Roles del sistema
define('ROLES', [
    'funcionario' => 'Funcionario',
    'administrador' => 'Administrador'
]);

// Mensaje de las solicitudes
function mensaje_status_solicitud($estado) {
    $mensajes = [
        'Pendiente' => 'La solicitud está en espera de ser procesada',
        'Cargada' => 'La carpeta fue cargada exitosamente',
        'Cargada con observaciones' => 'La carpeta fue cargada pero con observaciones',
        'No encontrada' => 'La carpeta no fue encontrada',
        'Rechazada' => 'La solicitud fue rechazada'
    ];
    
    return $mensajes[$estado] ?? 'Estado desconocido';
}

?>
