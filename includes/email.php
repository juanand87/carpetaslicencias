<?php
/**
 * Funciones de Email y Notificaciones
 */

require_once __DIR__ . '/../config/smtp.php';

/**
 * Enviar correo simple (usando mail() nativo de PHP)
 * Para usar SMTP real, instalar PHPMailer o similar
 */
function enviar_correo($para, $asunto, $cuerpo, $headers = []) {
    // Headers por defecto
    if (empty($headers)) {
        $headers = [
            'From' => FROM_EMAIL,
            'Reply-To' => FROM_EMAIL,
            'Content-Type' => 'text/html; charset=UTF-8'
        ];
    }
    
    // Convertir array de headers a string
    $headers_str = '';
    foreach ($headers as $clave => $valor) {
        $headers_str .= "$clave: $valor\r\n";
    }
    
    return mail($para, $asunto, $cuerpo, $headers_str);
}

/**
 * Obtener plantilla de correo y reemplazar variables
 */
function obtener_plantilla_correo($conn, $tipo, $variables = []) {
    $sql = "SELECT * FROM plantillas_correo WHERE tipo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $tipo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    $plantilla = $result->fetch_assoc();
    
    // Reemplazar variables
    $asunto = $plantilla['asunto'];
    $cuerpo = $plantilla['cuerpo'];
    
    foreach ($variables as $clave => $valor) {
        $placeholder = '{' . $clave . '}';
        $asunto = str_replace($placeholder, $valor, $asunto);
        $cuerpo = str_replace($placeholder, $valor, $cuerpo);
    }
    
    return [
        'asunto' => $asunto,
        'cuerpo' => $cuerpo
    ];
}

/**
 * Notificar nueva solicitud
 */
function notificar_nueva_solicitud($conn, $solicitud_id) {
    // Obtener datos de la solicitud
    $sql = "SELECT s.*, m.nombre as municipalidad FROM solicitudes s 
            JOIN municipalidades m ON s.municipalidad_id = m.id 
            WHERE s.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $solicitud_id);
    $stmt->execute();
    $solicitud = $stmt->get_result()->fetch_assoc();
    
    if (!$solicitud) {
        return false;
    }
    
    // Variables para la plantilla
    $variables = [
        'nombre' => $solicitud['nombre_solicitado'],
        'apellido_paterno' => $solicitud['apellido_paterno_solicitado'],
        'apellido_materno' => $solicitud['apellido_materno_solicitado'],
        'run' => $solicitud['run_solicitado'],
        'municipalidad' => $solicitud['municipalidad'],
        'correo_solicitante' => $solicitud['correo_solicitante']
    ];
    
    // Obtener plantilla
    $plantilla = obtener_plantilla_correo($conn, 'nueva_solicitud', $variables);
    
    if (!$plantilla) {
        return false;
    }
    
    // Enviar correo al solicitante
    enviar_correo($solicitud['correo_solicitante'], $plantilla['asunto'], $plantilla['cuerpo']);
    
    // Enviar correos a todos los funcionarios
    $sql = "SELECT correo FROM usuarios WHERE rol = 'funcionario' AND activo = 1";
    $result = $conn->query($sql);
    
    while ($funcionario = $result->fetch_assoc()) {
        enviar_correo($funcionario['correo'], $plantilla['asunto'], $plantilla['cuerpo']);
    }
    
    return true;
}

/**
 * Notificar cambio de estado de solicitud
 */
function notificar_cambio_estado($conn, $solicitud_id) {
    // Obtener datos de la solicitud
    $sql = "SELECT s.*, m.nombre as municipalidad FROM solicitudes s 
            JOIN municipalidades m ON s.municipalidad_id = m.id 
            WHERE s.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $solicitud_id);
    $stmt->execute();
    $solicitud = $stmt->get_result()->fetch_assoc();
    
    if (!$solicitud) {
        return false;
    }
    
    // Variables para la plantilla
    $variables = [
        'nombre' => $solicitud['nombre_solicitado'],
        'apellido_paterno' => $solicitud['apellido_paterno_solicitado'],
        'apellido_materno' => $solicitud['apellido_materno_solicitado'],
        'run' => $solicitud['run_solicitado'],
        'estado' => $solicitud['estado'],
        'observaciones' => $solicitud['observaciones'] ?? 'Sin observaciones'
    ];
    
    // Obtener plantilla según el estado
    $tipo_plantilla = 'estado_cambio';
    if ($solicitud['estado'] === 'Cargada') {
        $tipo_plantilla = 'solicitud_cargada';
    } elseif ($solicitud['estado'] === 'Rechazada') {
        $tipo_plantilla = 'solicitud_rechazada';
    }
    
    $plantilla = obtener_plantilla_correo($conn, $tipo_plantilla, $variables);
    
    if (!$plantilla) {
        return false;
    }
    
    // Enviar correo al solicitante
    enviar_correo($solicitud['correo_solicitante'], $plantilla['asunto'], $plantilla['cuerpo']);
    
    // Enviar correos a todos los funcionarios
    $sql = "SELECT correo FROM usuarios WHERE rol = 'funcionario' AND activo = 1";
    $result = $conn->query($sql);
    
    while ($funcionario = $result->fetch_assoc()) {
        enviar_correo($funcionario['correo'], $plantilla['asunto'], $plantilla['cuerpo']);
    }
    
    return true;
}

?>
