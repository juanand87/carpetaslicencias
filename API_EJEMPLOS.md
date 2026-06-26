<?php
/**
 * Ejemplos de Uso y Referencia de API
 * Este archivo contiene ejemplos de cómo usar las funciones del sistema
 */

// ============================================
// EJEMPLOS DE USO DEL SISTEMA
// ============================================

// 1. CREAR UNA SOLICITUD PROGRAMÁTICAMENTE
// $datos = [
//     'nombre' => 'Juan',
//     'apellido_paterno' => 'Pérez',
//     'apellido_materno' => 'García',
//     'run' => '12.345.678-9',
//     'correo_solicitante' => 'juan@ejemplo.com',
//     'municipalidad_id' => 1
// ];
// $resultado = crear_solicitud($conn, $datos);
// if ($resultado['success']) {
//     echo "Solicitud creada: " . $resultado['id'];
// }

// 2. OBTENER TODAS LAS SOLICITUDES
// $solicitudes = obtener_solicitudes($conn);
// foreach ($solicitudes as $sol) {
//     echo $sol['nombre_solicitado'] . " - " . $sol['estado'];
// }

// 3. FILTRAR SOLICITUDES
// $filtros = [
//     'estado' => 'Pendiente',
//     'busqueda' => '12345678'
// ];
// $solicitudes = obtener_solicitudes($conn, $filtros);

// 4. CAMBIAR ESTADO DE SOLICITUD
// $resultado = cambiar_estado_solicitud(
//     $conn,
//     1, // ID de solicitud
//     'Cargada',
//     'Proceso completado',
//     1  // ID del usuario
// );

// 5. OBTENER BITÁCORA
// $bitacora = obtener_bitacora_solicitud($conn, 1);

// 6. VALIDAR RUN
// if (validar_run('12.345.678-9')) {
//     echo "RUN válido";
// }

// 7. AUTENTICAR USUARIO
// $resultado = autenticar_usuario($conn, 'correo@ejemplo.com', 'password');
// if ($resultado['success']) {
//     // Usuario autenticado
// }

// 8. OBTENER USUARIO AUTENTICADO
// $usuario = obtener_usuario_autenticado($conn);
// echo $usuario['nombre'];

// 9. ENVIAR CORREO
// enviar_correo('destino@ejemplo.com', 'Asunto', 'Cuerpo del mensaje');

// 10. OBTENER PLANTILLA DE CORREO
// $variables = [
//     'nombre' => 'Juan',
//     'run' => '12345678-9',
//     'estado' => 'Cargada'
// ];
// $plantilla = obtener_plantilla_correo($conn, 'estado_cambio', $variables);

// ============================================
// ESTRUCTURA DE DATOS
// ============================================

/*
TABLA: solicitudes
- id: int
- nombre_solicitado: varchar(100)
- apellido_paterno_solicitado: varchar(100)
- apellido_materno_solicitado: varchar(100)
- run_solicitado: varchar(12)
- correo_solicitante: varchar(100)
- municipalidad_id: int
- estado: enum('Pendiente', 'Cargada', 'Cargada con observaciones', 'No encontrada', 'Rechazada')
- observaciones: text
- usuario_id: int (quien atiende)
- fecha_creacion: timestamp
- fecha_actualizacion: timestamp

TABLA: usuarios
- id: int
- nombre: varchar(100)
- apellido_paterno: varchar(100)
- apellido_materno: varchar(100)
- correo: varchar(100)
- contraseña: varchar(255)
- rol: enum('administrador', 'funcionario')
- activo: tinyint(1)
- fecha_creacion: timestamp

TABLA: bitacora_cambios
- id: int
- solicitud_id: int
- usuario_id: int
- estado_anterior: enum
- estado_nuevo: enum
- observaciones: text
- fecha_cambio: timestamp

TABLA: plantillas_correo
- id: int
- tipo: enum('nueva_solicitud', 'estado_cambio', 'solicitud_cargada', 'solicitud_rechazada')
- asunto: varchar(200)
- cuerpo: longtext
- fecha_actualizacion: timestamp

TABLA: config_smtp
- id: int
- host: varchar(100)
- puerto: int
- usuario: varchar(100)
- contraseña: varchar(255)
- from_email: varchar(100)
- from_nombre: varchar(100)
- fecha_actualizacion: timestamp
*/

// ============================================
// RESPUESTAS DE FUNCIONES
// ============================================

/*
crear_solicitud() retorna:
[
    'success' => true/false,
    'mensaje' => 'Mensaje descriptivo',
    'id' => 123  // Solo si success = true
]

cambiar_estado_solicitud() retorna:
[
    'success' => true/false,
    'mensaje' => 'Mensaje descriptivo'
]

autenticar_usuario() retorna:
[
    'success' => true/false,
    'mensaje' => 'Mensaje descriptivo',
    'usuario' => [...] // Solo si success = true
]
*/

// ============================================
// QUERYS SQL ÚTILES
// ============================================

/*
-- Obtener solicitudes por funcionario
SELECT s.*, COUNT(bc.id) as cambios
FROM solicitudes s
LEFT JOIN bitacora_cambios bc ON s.id = bc.solicitud_id
WHERE s.usuario_id = 1
GROUP BY s.id;

-- Solicitudes pendientes hace más de 7 días
SELECT * FROM solicitudes
WHERE estado = 'Pendiente'
AND DATE_ADD(fecha_creacion, INTERVAL 7 DAY) < NOW();

-- Estadísticas por estado
SELECT estado, COUNT(*) as total
FROM solicitudes
GROUP BY estado;

-- Actividad de un funcionario
SELECT bc.*, s.run_solicitado
FROM bitacora_cambios bc
JOIN solicitudes s ON bc.solicitud_id = s.id
WHERE bc.usuario_id = 1
ORDER BY bc.fecha_cambio DESC;
*/

// ============================================
// TABLA DE MUNICIPALIDADES
// ============================================

/*
El sistema incluye las 346 comunas de Chile organizadas por región:
- Arica y Parinacota
- Tarapacá
- Antofagasta
- Atacama
- Coquimbo
- Valparaíso
- Metropolitana
- Maule
- Ñuble
- Biobío
- Araucanía
- Los Ríos
- Los Lagos
- Aysén
- Magallanes

Obtener municipalidades:
SELECT * FROM municipalidades ORDER BY region, nombre;
*/

// ============================================
// EXTENSIONES FUTURAS
// ============================================

/*
Posibles mejoras:
1. API REST para terceros
2. Descarga de reportes (PDF, Excel)
3. Gráficos de estadísticas
4. SMS notifications
5. Integración con SII (Servicio de Impuestos Internos)
6. QR en correos
7. Validación de datos con seremis
8. Integración con otros municipios
9. Sistema de prioridades/urgencias
10. Notificaciones en tiempo real
*/

?>
