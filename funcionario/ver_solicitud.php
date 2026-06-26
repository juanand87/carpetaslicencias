<?php
/**
 * Ver detalles de una solicitud
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/solicitudes.php';

// Verificar autenticación
if (!verificar_autenticacion()) {
    header('Location: login.php');
    exit;
}

// Obtener ID de la solicitud
$solicitud_id = intval($_GET['id'] ?? 0);

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

// Obtener bitácora de cambios
$bitacora = obtener_bitacora_solicitud($conn, $solicitud_id);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Solicitud | Sistema de Solicitudes</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .detalle-solicitud {
            background: white;
            padding: 30px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .detalle-fila {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .detalle-label {
            font-weight: 600;
            color: #333;
        }

        .detalle-valor {
            color: #666;
        }

        .bitacora-item {
            background: #f9f9f9;
            padding: 15px;
            border-left: 3px solid #667eea;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .bitacora-header {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .bitacora-fecha {
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Detalles de Solicitud #<?= $solicitud['id'] ?></h1>
            <p><a href="dashboard.php" style="color: white;">← Volver a solicitudes</a></p>
        </div>
    </div>

    <div class="container">
        <div class="detalle-solicitud">
            <h2>Información de la Solicitud</h2>

            <div class="detalle-fila">
                <div class="detalle-label">ID de Solicitud:</div>
                <div class="detalle-valor">#<?= $solicitud['id'] ?></div>
            </div>

            <div class="detalle-fila">
                <div class="detalle-label">Named Solicitado:</div>
                <div class="detalle-valor"><?= htmlspecialchars($solicitud['nombre_solicitado'] . ' ' . $solicitud['apellido_paterno_solicitado'] . ' ' . ($solicitud['apellido_materno_solicitado'] ?? '')) ?></div>
            </div>

            <div class="detalle-fila">
                <div class="detalle-label">RUN:</div>
                <div class="detalle-valor"><?= htmlspecialchars($solicitud['run_solicitado']) ?></div>
            </div>

            <div class="detalle-fila">
                <div class="detalle-label">Correo del Solicitante:</div>
                <div class="detalle-valor"><?= htmlspecialchars($solicitud['correo_solicitante']) ?></div>
            </div>

            <div class="detalle-fila">
                <div class="detalle-label">Municipalidad:</div>
                <div class="detalle-valor"><?= htmlspecialchars($solicitud['municipalidad']) ?></div>
            </div>

            <div class="detalle-fila">
                <div class="detalle-label">Estado Actual:</div>
                <div class="detalle-valor">
                    <span class="estado-<?= strtolower(str_replace(' con ', '-', str_replace('ó', 'o', $solicitud['estado']))) ?>">
                        <?= htmlspecialchars($solicitud['estado']) ?>
                    </span>
                </div>
            </div>

            <div class="detalle-fila">
                <div class="detalle-label">Observaciones:</div>
                <div class="detalle-valor"><?= htmlspecialchars($solicitud['observaciones'] ?? 'Sin observaciones') ?></div>
            </div>

            <div class="detalle-fila">
                <div class="detalle-label">Atendida por:</div>
                <div class="detalle-valor">
                    <?php
                    if ($solicitud['usuario_id']) {
                        echo htmlspecialchars($solicitud['usuario_nombre'] . ' ' . $solicitud['apellido_paterno']);
                    } else {
                        echo 'Sin asignar';
                    }
                    ?>
                </div>
            </div>

            <div class="detalle-fila">
                <div class="detalle-label">Fecha de Creación:</div>
                <div class="detalle-valor"><?= date('d/m/Y H:i:s', strtotime($solicitud['fecha_creacion'])) ?></div>
            </div>

            <div class="detalle-fila">
                <div class="detalle-label">Última Actualización:</div>
                <div class="detalle-valor"><?= date('d/m/Y H:i:s', strtotime($solicitud['fecha_actualizacion'])) ?></div>
            </div>

            <div style="margin-top: 20px; text-align: center;">
                <a href="actualizar_solicitud.php?id=<?= $solicitud['id'] ?>" class="btn btn-success">✏️ Actualizar Estado</a>
                <a href="dashboard.php" class="btn btn-secondary" style="display: inline-block;">Volver</a>
            </div>
        </div>

        <?php if (!empty($bitacora)): ?>
            <div class="detalle-solicitud">
                <h2>Bitácora de Cambios</h2>

                <?php foreach ($bitacora as $cambio): ?>
                    <div class="bitacora-item">
                        <div class="bitacora-header">
                            <?= htmlspecialchars($cambio['estado_anterior'] ?? 'Inicial') ?> → <?= htmlspecialchars($cambio['estado_nuevo']) ?>
                        </div>
                        <div class="bitacora-fecha">
                            <?= date('d/m/Y H:i:s', strtotime($cambio['fecha_cambio'])) ?>
                            <?php if ($cambio['nombre']): ?>
                                - Por: <?= htmlspecialchars($cambio['nombre'] . ' ' . ($cambio['apellido_paterno'] ?? '')) ?>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($cambio['observaciones'])): ?>
                            <div style="margin-top: 8px; color: #555; font-size: 13px;">
                                <strong>Observaciones:</strong> <?= htmlspecialchars($cambio['observaciones']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>&copy; 2024 Sistema de Solicitudes - Municipalidad de Los Lagos</p>
    </div>
</body>
</html>
