<?php
/**
 * Funciones de Solicitudes
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/email.php';

/**
 * Crear nueva solicitud
 */
function crear_solicitud($conn, $datos) {
    // Validar datos
    $nombre = trim($datos['nombre'] ?? '');
    $apellido_paterno = trim($datos['apellido_paterno'] ?? '');
    $apellido_materno = trim($datos['apellido_materno'] ?? '');
    $run = trim($datos['run'] ?? '');
    $correo_solicitante = filter_var($datos['correo_solicitante'] ?? '', FILTER_SANITIZE_EMAIL);
    $municipalidad_id = intval($datos['municipalidad_id'] ?? 0);
    
    // Validación de campos obligatorios
    if (empty($nombre) || empty($apellido_paterno) || empty($run) || empty($correo_solicitante) || $municipalidad_id === 0) {
        return ['success' => false, 'mensaje' => 'Por favor complete todos los campos obligatorios (*)'];
    }
    
    // Validar formato de RUN
    if (!validar_run($run)) {
        return ['success' => false, 'mensaje' => 'El formato del RUN no es válido'];
    }
    
    // Validar correo
    if (!filter_var($correo_solicitante, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'mensaje' => 'El correo electrónico no es válido'];
    }
    
    // Verificar que la municipalidad existe
    $sql = "SELECT id FROM municipalidades WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $municipalidad_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        return ['success' => false, 'mensaje' => 'Municipalidad no válida'];
    }
    
    // Insertar solicitud
    $sql = "INSERT INTO solicitudes 
            (nombre_solicitado, apellido_paterno_solicitado, apellido_materno_solicitado, 
             run_solicitado, correo_solicitante, municipalidad_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $nombre, $apellido_paterno, $apellido_materno, $run, 
                      $correo_solicitante, $municipalidad_id);
    
    if ($stmt->execute()) {
        $solicitud_id = $conn->insert_id;
        
        // Notificar nueva solicitud
        notificar_nueva_solicitud($conn, $solicitud_id);
        
        return ['success' => true, 'mensaje' => 'Hemos recibido su solicitud exitosamente. Cuando se cargue la carpeta al sistema, recibirá una notificación.', 'id' => $solicitud_id];
    }
    
    return ['success' => false, 'mensaje' => 'Error al crear la solicitud'];
}

/**
 * Obtener todas las solicitudes
 */
function obtener_solicitudes($conn, $filtros = []) {
    $sql = "SELECT s.*, m.nombre as municipalidad, u.nombre as usuario_nombre, u.apellido_paterno 
            FROM solicitudes s 
            JOIN municipalidades m ON s.municipalidad_id = m.id 
            LEFT JOIN usuarios u ON s.usuario_id = u.id 
            WHERE 1=1";
    
    $params = [];
    $tipos = '';
    
    // Filtro por estado
    if (!empty($filtros['estado'])) {
        $sql .= " AND s.estado = ?";
        $params[] = $filtros['estado'];
        $tipos .= 's';
    }
    
    // Filtro por municipalidad
    if (!empty($filtros['municipalidad_id'])) {
        $sql .= " AND s.municipalidad_id = ?";
        $params[] = intval($filtros['municipalidad_id']);
        $tipos .= 'i';
    }
    
    // Búsqueda por RUN o nombre
    if (!empty($filtros['busqueda'])) {
        $busqueda = '%' . $filtros['busqueda'] . '%';
        $sql .= " AND (s.run_solicitado LIKE ? OR s.nombre_solicitado LIKE ?)";
        $params[] = $busqueda;
        $params[] = $busqueda;
        $tipos .= 'ss';
    }
    
    $sql .= " ORDER BY s.fecha_creacion DESC";
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($tipos, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $solicitudes = [];
    while ($row = $result->fetch_assoc()) {
        $solicitudes[] = $row;
    }
    
    return $solicitudes;
}

/**
 * Obtener solicitud por ID
 */
function obtener_solicitud($conn, $solicitud_id) {
    $sql = "SELECT s.*, m.nombre as municipalidad 
            FROM solicitudes s 
            JOIN municipalidades m ON s.municipalidad_id = m.id 
            WHERE s.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $solicitud_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    return $result->fetch_assoc();
}

/**
 * Cambiar estado de solicitud
 */
function cambiar_estado_solicitud($conn, $solicitud_id, $nuevo_estado, $observaciones = '', $usuario_id = null) {
    // Validar estado
    $estados_validos = ['Pendiente', 'Cargada', 'Cargada con observaciones', 'No encontrada', 'Rechazada'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        return ['success' => false, 'mensaje' => 'Estado no válido'];
    }
    
    // Obtener solicitud actual
    $solicitud = obtener_solicitud($conn, $solicitud_id);
    if (!$solicitud) {
        return ['success' => false, 'mensaje' => 'Solicitud no encontrada'];
    }
    
    $estado_anterior = $solicitud['estado'];
    
    // Actualizar solicitud
    $sql = "UPDATE solicitudes 
            SET estado = ?, observaciones = ?, usuario_id = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $nuevo_estado, $observaciones, $usuario_id, $solicitud_id);
    
    if (!$stmt->execute()) {
        return ['success' => false, 'mensaje' => 'Error al actualizar la solicitud'];
    }
    
    // Registrar cambio en bitácora
    $sql = "INSERT INTO bitacora_cambios 
            (solicitud_id, usuario_id, estado_anterior, estado_nuevo, observaciones) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $solicitud_id, $usuario_id, $estado_anterior, $nuevo_estado, $observaciones);
    $stmt->execute();
    
    // Avisar únicamente cuando la carpeta pasa a un estado de carga.
    $estados_cargados = ['Cargada', 'Cargada con observaciones'];
    if ($estado_anterior !== $nuevo_estado && in_array($nuevo_estado, $estados_cargados, true)) {
        notificar_carpeta_cargada($conn, $solicitud_id);
    }
    
    return ['success' => true, 'mensaje' => 'Estado actualizado exitosamente'];
}

/**
 * Obtener bitácora de cambios de una solicitud
 */
function obtener_bitacora_solicitud($conn, $solicitud_id) {
    $sql = "SELECT bc.*, u.nombre, u.apellido_paterno 
            FROM bitacora_cambios bc 
            LEFT JOIN usuarios u ON bc.usuario_id = u.id 
            WHERE bc.solicitud_id = ? 
            ORDER BY bc.fecha_cambio DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $solicitud_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cambios = [];
    while ($row = $result->fetch_assoc()) {
        $cambios[] = $row;
    }
    
    return $cambios;
}

/**
 * Validar RUN chileno
 */
function validar_run($run) {
    // Limpiar el RUN
    $run = strtoupper(str_replace(['.', '-', ' '], '', $run));
    
    // Verificar formato básico
    if (!preg_match('/^([0-9]{1,2})[0-9]{3}([0-9]{3})(K|[0-9])$/', $run)) {
        return false;
    }
    
    // Extraer número y dígito verificador
    $num_run = substr($run, 0, -1);
    $digito = substr($run, -1);
    
    // Calcular dígito verificador
    $s = 0;
    $m = 2;
    
    for ($i = strlen($num_run) - 1; $i >= 0; $i--) {
        $s += intval($num_run[$i]) * $m;
        $m++;
        if ($m > 7) {
            $m = 2;
        }
    }
    
    $dv = 11 - ($s % 11);
    
    if ($dv == 11) {
        $dv = 0;
    } elseif ($dv == 10) {
        $dv = 'K';
    }
    
    return (string)$dv === (string)$digito;
}

?>
