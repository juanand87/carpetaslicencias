<?php
/** Envío de correos y notificaciones mediante PHPMailer. */

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/smtp.php';

function crear_cliente_smtp($conn) {
    $config = obtenerConfigSMTP($conn);
    if (empty($config['host']) || empty($config['puerto']) || empty($config['from_email'])) {
        throw new RuntimeException('La configuración SMTP está incompleta.');
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->Port = (int) $config['puerto'];
    $mail->SMTPAuth = !empty($config['usuario']);
    $mail->Username = $config['usuario'] ?? '';
    $mail->Password = $config['contraseña'] ?? '';
    $mail->CharSet = PHPMailer::CHARSET_UTF8;
    $mail->isHTML(true);
    $mail->Timeout = 20;
    if ((int) $config['puerto'] === 465) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->SMTPAutoTLS = true;
    }
    $mail->setFrom($config['from_email'], $config['from_nombre'] ?: 'Carpetas de Licencias');
    return $mail;
}

function enviar_correo($conn, $para, $asunto, $cuerpo, $nombre_destinatario = '') {
    $mail = crear_cliente_smtp($conn);
    $mail->addAddress($para, $nombre_destinatario);
    $mail->Subject = $asunto;
    $mail->Body = $cuerpo;
    $mail->AltBody = trim(html_entity_decode(strip_tags(str_replace(
        ['<br>', '<br/>', '<br />', '</p>'], ["\n", "\n", "\n", "\n"], $cuerpo
    )), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    return $mail->send();
}

function escapar_correo($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function obtener_plantilla_correo($conn, $tipo, $variables) {
    $stmt = $conn->prepare('SELECT asunto, cuerpo FROM plantillas_correo WHERE tipo = ? LIMIT 1');
    $stmt->bind_param('s', $tipo);
    $stmt->execute();
    $plantilla = $stmt->get_result()->fetch_assoc();
    if (!$plantilla) return null;

    $asunto = $plantilla['asunto'];
    $cuerpo = $plantilla['cuerpo'];
    foreach ($variables as $clave => $valor) {
        $asunto = str_replace('{' . $clave . '}', (string) $valor, $asunto);
        $cuerpo = str_replace('{' . $clave . '}', escapar_correo($valor), $cuerpo);
    }
    return ['asunto' => $asunto, 'cuerpo' => $cuerpo];
}
function obtener_datos_solicitud_correo($conn, $solicitud_id) {
    $sql = "SELECT s.*, m.nombre AS municipalidad FROM solicitudes s
            JOIN municipalidades m ON s.municipalidad_id = m.id WHERE s.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $solicitud_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function notificar_nueva_solicitud($conn, $solicitud_id) {
    $solicitud = obtener_datos_solicitud_correo($conn, $solicitud_id);
    if (!$solicitud) return false;

    $nombre = trim($solicitud['nombre_solicitado'] . ' ' . $solicitud['apellido_paterno_solicitado'] . ' ' . $solicitud['apellido_materno_solicitado']);
    $variables = [
        'solicitud_id' => $solicitud_id,
        'nombre' => $solicitud['nombre_solicitado'],
        'apellido_paterno' => $solicitud['apellido_paterno_solicitado'],
        'apellido_materno' => $solicitud['apellido_materno_solicitado'],
        'run' => $solicitud['run_solicitado'],
        'municipalidad' => $solicitud['municipalidad'],
        'correo_solicitante' => $solicitud['correo_solicitante']
    ];
    $plantilla = obtener_plantilla_correo($conn, 'nueva_solicitud', $variables);
    if (!$plantilla) {
        $plantilla = [
            'asunto' => 'Nueva solicitud de carpeta de licencia #' . $solicitud_id,
            'cuerpo' => '<h2>Nueva solicitud recibida</h2><p>Ha ingresado una nueva solicitud para ' . escapar_correo($nombre) . '.</p>'
        ];
    }

    // Cada nueva solicitud se notifica exclusivamente a los funcionarios activos.
    $destinatarios_envio = [];
    $funcionarios = $conn->query("SELECT nombre, apellido_paterno, correo FROM usuarios WHERE rol = 'funcionario' AND activo = 1");
    while ($funcionario = $funcionarios->fetch_assoc()) {
        $clave = strtolower(trim($funcionario['correo']));
        $destinatarios_envio[$clave] = [
            'correo' => $funcionario['correo'],
            'nombre' => trim($funcionario['nombre'] . ' ' . $funcionario['apellido_paterno'])
        ];
    }

    $enviado = false;
    foreach ($destinatarios_envio as $destinatario) {
        try {
            enviar_correo($conn, $destinatario['correo'], $plantilla['asunto'], $plantilla['cuerpo'], $destinatario['nombre']);
            $enviado = true;
        } catch (Throwable $e) {
            error_log('No se pudo notificar la solicitud #' . $solicitud_id . ' a ' . $destinatario['correo'] . ': ' . $e->getMessage());
        }
    }

    $asunto_confirmacion = 'Hemos recibido su solicitud de carpeta de licencia';
    $cuerpo_confirmacion = '<h2>Solicitud recibida</h2><p>Hemos recibido correctamente su solicitud para la carpeta de licencia de '
        . escapar_correo($nombre) . '.</p><p><strong>N.º de solicitud:</strong> ' . (int) $solicitud_id . '</p>'
        . '<p>Cuando la carpeta sea cargada al sistema de licencias, recibirá una nueva notificación en este correo.</p>'
        . '<p>Atentamente,<br>Municipalidad de Los Lagos</p>';
    try {
        enviar_correo($conn, $solicitud['correo_solicitante'], $asunto_confirmacion, $cuerpo_confirmacion);
        $enviado = true;
    } catch (Throwable $e) {
        error_log('No se pudo confirmar la solicitud #' . $solicitud_id . ': ' . $e->getMessage());
    }
    return $enviado;
}
function notificar_carpeta_cargada($conn, $solicitud_id) {
    $solicitud = obtener_datos_solicitud_correo($conn, $solicitud_id);
    if (!$solicitud) return false;

    $nombre = trim($solicitud['nombre_solicitado'] . ' ' . $solicitud['apellido_paterno_solicitado'] . ' ' . $solicitud['apellido_materno_solicitado']);
    $asunto = 'Carpeta de licencia cargada - Solicitud #' . $solicitud_id;
    $cuerpo = '<h2>Su carpeta de licencia ya fue cargada</h2><p>Le informamos que la carpeta de licencia de <strong>'
        . escapar_correo($nombre) . '</strong> ya ha sido cargada al sistema de licencias.</p>'
        . '<p><strong>N.º de solicitud:</strong> ' . (int) $solicitud_id . '<br><strong>RUN:</strong> '
        . escapar_correo($solicitud['run_solicitado']) . '<br><strong>Estado:</strong> ' . escapar_correo($solicitud['estado']) . '</p>';
    if ($solicitud['estado'] === 'Cargada con observaciones' && trim((string) $solicitud['observaciones']) !== '') {
        $cuerpo .= '<p><strong>Observaciones:</strong><br>' . nl2br(escapar_correo($solicitud['observaciones'])) . '</p>';
    }
    $cuerpo .= '<p>Atentamente,<br>Municipalidad de Los Lagos</p>';
    try {
        return enviar_correo($conn, $solicitud['correo_solicitante'], $asunto, $cuerpo);
    } catch (Throwable $e) {
        error_log('No se pudo avisar la carga de la solicitud #' . $solicitud_id . ': ' . $e->getMessage());
        return false;
    }
}
