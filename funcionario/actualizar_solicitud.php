<?php
/**
 * Actualizar estado de una solicitud
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/solicitudes.php';

// Verificar autenticación
if (!verificar_autenticacion()) {
    header('Location: login.php');
    exit;
}

// Obtener usuario autenticado
$usuario = obtener_usuario_autenticado($conn);

// Obtener ID de la solicitud
$solicitud_id = intval($_GET['id'] ?? $_POST['solicitud_id'] ?? 0);

if ($solicitud_id === 0) {
    header('Location: dashboard.php');
    exit;
}

// Obtener solicitud
$solicitud = obtener_solicitud($conn, $solicitud_id);

if (!$solicitud) {
    header('Location: dashboard.php');
    exit;
}

$mensaje = '';
$tipo_mensaje = '';

// Procesar actualización del estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_estado'])) {
    $nuevo_estado = $_POST['nuevo_estado'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';
    
    $resultado = cambiar_estado_solicitud(
        $conn,
        $solicitud_id,
        $nuevo_estado,
        $observaciones,
        $usuario['id']
    );
    
    if ($resultado['success']) {
        $tipo_mensaje = 'exito';
        $mensaje = $resultado['mensaje'];
        // Actualizar objeto solicitud
        $solicitud = obtener_solicitud($conn, $solicitud_id);
    } else {
        $tipo_mensaje = 'error';
        $mensaje = $resultado['mensaje'];
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Solicitud | Sistema de Solicitudes</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .formulario-contenedor {
            background: white;
            padding: 30px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Actualizar Estado - Solicitud #<?= $solicitud['id'] ?></h1>
            <p><a href="dashboard.php" style="color: white;">← Volver a solicitudes</a></p>
        </div>
    </div>

    <div class="container">
        <div class="formulario-contenedor">
            <h2>Información de la Solicitud</h2>

            <div style="background: #f5f5f5; padding: 20px; border-radius: 4px; margin-bottom: 30px;">
                <p><strong>Nombre:</strong> <?= htmlspecialchars($solicitud['nombre_solicitado'] . ' ' . $solicitud['apellido_paterno_solicitado']) ?></p>
                <p><strong>RUN:</strong> <?= htmlspecialchars($solicitud['run_solicitado']) ?></p>
                <p><strong>Municipalidad:</strong> <?= htmlspecialchars($solicitud['municipalidad']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($solicitud['correo_solicitante']) ?></p>
                <p><strong>Estado Actual:</strong>
                    <span class="estado-<?= strtolower(str_replace(' con ', '-', str_replace('ó', 'o', $solicitud['estado']))) ?>">
                        <?= htmlspecialchars($solicitud['estado']) ?>
                    </span>
                </p>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?= $tipo_mensaje ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <h2>Actualizar Estado</h2>

            <form method="POST" class="formulario">
                <input type="hidden" name="solicitud_id" value="<?= $solicitud['id'] ?>">

                <div class="form-group">
                    <label for="nuevo_estado">Nuevo Estado <span class="requerido">*</span></label>
                    <select id="nuevo_estado" name="nuevo_estado" required onchange="actualizarPlaceholder()">
                        <option value="">Seleccione un estado...</option>
                        <option value="Pendiente" <?= $solicitud['estado'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="Cargada" <?= $solicitud['estado'] === 'Cargada' ? 'selected' : '' ?>>Cargada</option>
                        <option value="Cargada con observaciones" <?= $solicitud['estado'] === 'Cargada con observaciones' ? 'selected' : '' ?>>Cargada con observaciones</option>
                        <option value="No encontrada" <?= $solicitud['estado'] === 'No encontrada' ? 'selected' : '' ?>>No encontrada</option>
                        <option value="Rechazada" <?= $solicitud['estado'] === 'Rechazada' ? 'selected' : '' ?>>Rechazada</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="5" placeholder="Ingrese observaciones (opcional)..." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: Arial, sans-serif;"><?= htmlspecialchars($solicitud['observaciones'] ?? '') ?></textarea>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" name="actualizar_estado" class="btn btn-success">
                        ✓ Guardar Cambios
                    </button>
                    <a href="ver_solicitud.php?id=<?= $solicitud['id'] ?>" class="btn btn-secondary" style="width: auto;">
                        Cancelar
                    </a>
                    <a href="dashboard.php" class="btn btn-info" style="width: auto;">
                        ← Volver
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function actualizarPlaceholder() {
            const estado = document.getElementById('nuevo_estado').value;
            const textarea = document.getElementById('observaciones');

            if (estado === 'Rechazada') {
                textarea.placeholder = 'Ingrese el motivo del rechazo...';
            } else if (estado === 'Cargada con observaciones') {
                textarea.placeholder = 'Ingrese las observaciones de la carga...';
            } else if (estado === 'No encontrada') {
                textarea.placeholder = 'Ingrese detalles de por qué no fue encontrada...';
            } else {
                textarea.placeholder = 'Ingrese observaciones (opcional)...';
            }
        }
    </script>

    <div class="footer">
        <p>&copy; 2024 Sistema de Solicitudes - Municipalidad de Los Lagos</p>
    </div>
</body>
</html>
