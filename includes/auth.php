<?php
/**
 * Funciones de Autenticación
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Iniciar sesión segura
 */
function iniciar_sesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Verificar si el usuario está autenticado
 */
function verificar_autenticacion() {
    iniciar_sesion();
    return isset($_SESSION['usuario_id']) && isset($_SESSION['rol']);
}

/**
 * Obtener el usuario autenticado
 */
function obtener_usuario_autenticado($conn) {
    iniciar_sesion();
    
    if (!isset($_SESSION['usuario_id'])) {
        return null;
    }
    
    $usuario_id = intval($_SESSION['usuario_id']);
    $sql = "SELECT * FROM usuarios WHERE id = ? AND activo = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Verificar si el usuario tiene un rol específico
 */
function verificar_rol($rol_requerido) {
    iniciar_sesion();
    
    if (!isset($_SESSION['rol'])) {
        return false;
    }
    
    if (is_array($rol_requerido)) {
        return in_array($_SESSION['rol'], $rol_requerido);
    }
    
    return $_SESSION['rol'] === $rol_requerido;
}

/**
 * Autenticar usuario con correo y contraseña
 */
function autenticar_usuario($conn, $correo, $contraseña) {
    $correo = filter_var($correo, FILTER_SANITIZE_EMAIL);
    
    $sql = "SELECT * FROM usuarios WHERE correo = ? AND activo = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['success' => false, 'mensaje' => 'Correo o contraseña incorrectos'];
    }
    
    $usuario = $result->fetch_assoc();
    
    if (!password_verify($contraseña, $usuario['contraseña'])) {
        return ['success' => false, 'mensaje' => 'Correo o contraseña incorrectos'];
    }
    
    // Crear sesión
    iniciar_sesion();
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['rol'] = $usuario['rol'];
    $_SESSION['correo'] = $usuario['correo'];
    $_SESSION['nombre'] = $usuario['nombre'];
    
    return ['success' => true, 'usuario' => $usuario];
}

/**
 * Cerrar sesión
 */
function cerrar_sesion() {
    iniciar_sesion();
    session_destroy();
    return true;
}

/**
 * Registrar un nuevo funcionario (solo para administrador)
 */
function registrar_funcionario($conn, $datos) {
    // Validar datos
    $nombre = trim($datos['nombre'] ?? '');
    $apellido_paterno = trim($datos['apellido_paterno'] ?? '');
    $apellido_materno = trim($datos['apellido_materno'] ?? '');
    $correo = filter_var($datos['correo'] ?? '', FILTER_SANITIZE_EMAIL);
    $contraseña = $datos['contraseña'] ?? '';
    
    if (empty($nombre) || empty($apellido_paterno) || empty($correo) || empty($contraseña)) {
        return ['success' => false, 'mensaje' => 'Todos los campos son obligatorios'];
    }
    
    if (strlen($contraseña) < 6) {
        return ['success' => false, 'mensaje' => 'La contraseña debe tener al menos 6 caracteres'];
    }
    
    // Verificar si el correo ya existe
    $sql = "SELECT id FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return ['success' => false, 'mensaje' => 'El correo ya está registrado'];
    }
    
    // Hash de la contraseña
    $contraseña_hash = password_hash($contraseña, PASSWORD_BCRYPT);
    
    // Insertar usuario
    $sql = "INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, correo, contraseña, rol) 
            VALUES (?, ?, ?, ?, ?, 'funcionario')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nombre, $apellido_paterno, $apellido_materno, $correo, $contraseña_hash);
    
    if ($stmt->execute()) {
        return ['success' => true, 'mensaje' => 'Funcionario registrado exitosamente'];
    }
    
    return ['success' => false, 'mensaje' => 'Error al registrar el funcionario'];
}

?>
