<?php
/**
 * Dashboard de Funcionario
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/solicitudes.php';

// Verificar autenticación
if (!verificar_autenticacion()) {
    header('Location: login.php');
    exit;
}

// Obtener usuario actual
$usuario = obtener_usuario_autenticado($conn);

$mensaje = '';
$tipo_mensaje = '';

// Procesar actualización de estado desde modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_estado_modal'])) {
    $solicitud_id = intval($_POST['solicitud_id'] ?? 0);
    $nuevo_estado = trim($_POST['nuevo_estado'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');

    if ($solicitud_id <= 0 || $nuevo_estado === '') {
        $tipo_mensaje = 'error';
        $mensaje = 'Debe seleccionar una solicitud y un estado válido.';
    } elseif ($observaciones === '') {
        $tipo_mensaje = 'error';
        $mensaje = 'Debe ingresar observaciones o comentarios para continuar.';
    } else {
        $resultado = cambiar_estado_solicitud(
            $conn,
            $solicitud_id,
            $nuevo_estado,
            $observaciones,
            $usuario['id']
        );

        $tipo_mensaje = $resultado['success'] ? 'exito' : 'error';
        $mensaje = $resultado['mensaje'];
    }
}

// Obtener solicitudes con filtros
$filtros = [];

if (!empty($_GET['estado'])) {
    $filtros['estado'] = $_GET['estado'];
}

if (!empty($_GET['busqueda'])) {
    $filtros['busqueda'] = $_GET['busqueda'];
}

$solicitudes = obtener_solicitudes($conn, $filtros);

// Estadísticas
$stats = [
    'total' => 0,
    'pendiente' => 0,
    'cargada' => 0,
    'observaciones' => 0,
    'no_encontrada' => 0,
    'rechazada' => 0
];

foreach ($solicitudes as $solicitud) {
    $stats['total']++;
    switch ($solicitud['estado']) {
        case 'Pendiente':
            $stats['pendiente']++;
            break;
        case 'Cargada':
            $stats['cargada']++;
            break;
        case 'Cargada con observaciones':
            $stats['observaciones']++;
            break;
        case 'No encontrada':
            $stats['no_encontrada']++;
            break;
        case 'Rechazada':
            $stats['rechazada']++;
            break;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Funcionario | Sistema de Solicitudes</title>
    <link rel="stylesheet" href="../public/css/style.css?v=20260730-3">
    <style>
        .dashboard-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .sidebar {
            grid-column: 1;
        }

        .main-content {
            grid-column: 2;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            text-align: center;
        }

        .stat-card h3 {
            color: #667eea;
            font-size: 28px;
            margin: 10px 0;
        }

        .stat-card p {
            color: #666;
            font-size: 13px;
        }

        .filtros {
            background: white;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .filtros-flex {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filtros-flex input,
        .filtros-flex select,
        .filtros-flex button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
        }

        .filtros-flex input {
            flex: 1;
            min-width: 200px;
        }

        .acciones-solicitud {
            display: flex;
            gap: 5px;
        }

        .acciones-solicitud .btn {
            padding: 4px 8px;
            font-size: 11px;
            margin: 0;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            width: 100%;
            max-width: 560px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            background: #667eea;
            color: #fff;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 18px;
        }

        .modal-close {
            border: none;
            background: transparent;
            color: #fff;
            font-size: 22px;
            cursor: pointer;
            line-height: 1;
        }

        .modal-content {
            padding: 20px;
        }

        .estado-botones {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            margin: 12px 0 16px;
        }

        .estado-btn {
            border: 1px solid #d8d8d8;
            background: #f7f7f7;
            border-radius: 6px;
            padding: 10px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .estado-btn:hover {
            background: #eceefe;
            border-color: #667eea;
        }

        .estado-btn.active {
            background: #667eea;
            border-color: #667eea;
            color: #fff;
            font-weight: 600;
        }

        .modal-content textarea {
            width: 100%;
            border: 1px solid #dcdcdc;
            border-radius: 6px;
            padding: 10px;
            font-size: 14px;
            resize: vertical;
            min-height: 120px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 16px;
        }

        @media (max-width: 640px) {
            .estado-botones {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 1024px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }

            .main-content {
                grid-column: 1;
            }

            .sidebar {
                grid-column: 1;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Panel de Funcionario - Solicitudes de Carpetas</h1>
            <p><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido_paterno']) ?></p>
        </div>
    </div>

    <div class="container">
        <div class="dashboard-container">
            <!-- SIDEBAR -->
            <div class="sidebar">
                <div style="background: white; padding: 20px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 15px;">
                    <h3 style="font-size: 16px; margin-bottom: 15px; color: #333;">Menú</h3>
                    <nav>
                        <a href="dashboard.php" style="display: block; padding: 10px; color: #333; margin-bottom: 10px; text-decoration: none; border-left: 3px solid #667eea; padding-left: 15px; border-radius: 4px; background: #f5f5f5;">
                            📋 Solicitudes
                        </a>
                        <?php if ($usuario['rol'] === 'administrador'): ?>
                            <a href="../administrador/dashboard.php" style="display: block; padding: 10px; color: #333; margin-bottom: 10px; text-decoration: none; border-left: 3px solid transparent; padding-left: 15px; border-radius: 4px;">
                                ⚙️ Administración
                            </a>
                        <?php endif; ?>
                        <a href="logout.php" style="display: block; padding: 10px; color: #333; margin-bottom: 10px; text-decoration: none; border-left: 3px solid transparent; padding-left: 15px; border-radius: 4px;">
                            🚪 Cerrar Sesión
                        </a>
                    </nav>
                </div>

                <h3 style="font-size: 14px; margin: 20px 0 10px 0; color: #333;">Estadísticas</h3>
                <div class="stat-card">
                    <p>Total de Solicitudes</p>
                    <h3><?= $stats['total'] ?></h3>
                </div>

                <div class="stat-card" style="border-top: 3px solid #fff3cd;">
                    <p>Pendientes</p>
                    <h3 style="color: #856404;"><?= $stats['pendiente'] ?></h3>
                </div>

                <div class="stat-card" style="border-top: 3px solid #d4edda;">
                    <p>Cargadas</p>
                    <h3 style="color: #155724;"><?= $stats['cargada'] ?></h3>
                </div>

                <div class="stat-card" style="border-top: 3px solid #d1ecf1;">
                    <p>Con Observaciones</p>
                    <h3 style="color: #0c5460;"><?= $stats['observaciones'] ?></h3>
                </div>

                <div class="stat-card" style="border-top: 3px solid #f8d7da;">
                    <p>No Encontradas</p>
                    <h3 style="color: #721c24;"><?= $stats['no_encontrada'] ?></h3>
                </div>

                <div class="stat-card" style="border-top: 3px solid #f5c6cb;">
                    <p>Rechazadas</p>
                    <h3 style="color: #721c24;"><?= $stats['rechazada'] ?></h3>
                </div>
            </div>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="main-content">
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-<?= $tipo_mensaje ?>">
                        <?= htmlspecialchars($mensaje) ?>
                    </div>
                <?php endif; ?>

                <div class="filtros">
                    <h3 style="margin-bottom: 15px; color: #333;">Filtrar Solicitudes</h3>
                    <form method="GET" class="filtros-flex">
                        <input type="text" name="busqueda" placeholder="Buscar por RUN o nombre..." value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                        
                        <select name="estado">
                            <option value="">Todos los estados</option>
                            <option value="Pendiente" <?= ($_GET['estado'] ?? '') === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="Cargada" <?= ($_GET['estado'] ?? '') === 'Cargada' ? 'selected' : '' ?>>Cargada</option>
                            <option value="Cargada con observaciones" <?= ($_GET['estado'] ?? '') === 'Cargada con observaciones' ? 'selected' : '' ?>>Con observaciones</option>
                            <option value="No encontrada" <?= ($_GET['estado'] ?? '') === 'No encontrada' ? 'selected' : '' ?>>No encontrada</option>
                            <option value="Rechazada" <?= ($_GET['estado'] ?? '') === 'Rechazada' ? 'selected' : '' ?>>Rechazada</option>
                        </select>
                        
                        <button type="submit" class="btn btn-info" style="width: auto; margin: 0;">🔍 Buscar</button>
                        <a href="dashboard.php" class="btn btn-secondary" style="width: auto; margin: 0;">Limpiar</a>
                    </form>
                </div>

                <h2 style="margin-bottom: 20px; color: #333;">Listado de Solicitudes</h2>

                <?php if (empty($solicitudes)): ?>
                    <div class="alert alert-info">
                        No hay solicitudes disponibles con los criterios seleccionados.
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>RUN</th>
                                    <th>Nombre</th>
                                    <th>Municipalidad</th>
                                    <th>Estado</th>
                                    <th>Fecha Solicitud</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($solicitudes as $solicitud): ?>
                                    <tr>
                                        <td><?= $solicitud['id'] ?></td>
                                        <td><strong><?= htmlspecialchars($solicitud['run_solicitado']) ?></strong></td>
                                        <td><?= htmlspecialchars($solicitud['nombre_solicitado'] . ' ' . $solicitud['apellido_paterno_solicitado']) ?></td>
                                        <td><?= htmlspecialchars($solicitud['municipalidad']) ?></td>
                                        <td>
                                            <?php
                                            $clase_estado = 'estado-' . strtolower(str_replace(' ', '-', str_replace('ó', 'o', $solicitud['estado'])));
                                            ?>
                                            <span class="estado-<?= strtolower(str_replace(' con ', '-', str_replace('ó', 'o', $solicitud['estado']))) ?>">
                                                <?= htmlspecialchars($solicitud['estado']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($solicitud['fecha_creacion'])) ?></td>
                                        <td class="acciones-solicitud">
                                            <a href="ver_solicitud.php?id=<?= $solicitud['id'] ?>" class="btn btn-info btn-sm" style="margin: 0; width: auto;">Ver</a>
                                            <button
                                                type="button"
                                                class="btn btn-success btn-sm"
                                                style="margin: 0; width: auto;"
                                                onclick="abrirModalActualizar(
                                                    <?= intval($solicitud['id']) ?>,
                                                    '<?= htmlspecialchars($solicitud['nombre_solicitado'] . ' ' . $solicitud['apellido_paterno_solicitado'], ENT_QUOTES, 'UTF-8') ?>',
                                                    '<?= htmlspecialchars($solicitud['run_solicitado'], ENT_QUOTES, 'UTF-8') ?>'
                                                )"
                                            >
                                                Actualizar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="modalActualizar" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modalTitulo">
        <div class="modal-box">
            <div class="modal-header">
                <h3 id="modalTitulo">Actualizar Estado de Solicitud</h3>
                <button type="button" class="modal-close" onclick="cerrarModalActualizar()" aria-label="Cerrar">&times;</button>
            </div>

            <div class="modal-content">
                <p><strong>Solicitud:</strong> <span id="modalSolicitudNombre">-</span></p>
                <p><strong>RUN:</strong> <span id="modalSolicitudRun">-</span></p>

                <form method="POST" id="formActualizarModal">
                    <input type="hidden" name="actualizar_estado_modal" value="1">
                    <input type="hidden" name="solicitud_id" id="modalSolicitudId" value="">
                    <input type="hidden" name="nuevo_estado" id="modalNuevoEstado" value="">

                    <label style="display: block; margin-top: 14px; font-weight: 600;">Seleccione el nuevo estado <span class="requerido">*</span></label>
                    <div class="estado-botones" id="estadoBotones">
                        <button type="button" class="estado-btn" data-estado="Pendiente" onclick="seleccionarEstado('Pendiente', this)">Pendiente</button>
                        <button type="button" class="estado-btn" data-estado="Cargada" onclick="seleccionarEstado('Cargada', this)">Cargada</button>
                        <button type="button" class="estado-btn" data-estado="Cargada con observaciones" onclick="seleccionarEstado('Cargada con observaciones', this)">Cargada con observaciones</button>
                        <button type="button" class="estado-btn" data-estado="No encontrada" onclick="seleccionarEstado('No encontrada', this)">No encontrada</button>
                        <button type="button" class="estado-btn" data-estado="Rechazada" onclick="seleccionarEstado('Rechazada', this)">Rechazada</button>
                    </div>

                    <label for="modalObservaciones" style="display: block; font-weight: 600;">Observaciones / Comentarios <span class="requerido">*</span></label>
                    <textarea id="modalObservaciones" name="observaciones" required placeholder="Ingrese observaciones o comentarios para registrar el cambio de estado..."></textarea>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="cerrarModalActualizar()">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar estado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function abrirModalActualizar(solicitudId, nombreSolicitado, runSolicitado) {
            document.getElementById('modalSolicitudId').value = solicitudId;
            document.getElementById('modalSolicitudNombre').textContent = nombreSolicitado;
            document.getElementById('modalSolicitudRun').textContent = runSolicitado;
            document.getElementById('modalNuevoEstado').value = '';
            document.getElementById('modalObservaciones').value = '';

            document.querySelectorAll('#estadoBotones .estado-btn').forEach(function(btn) {
                btn.classList.remove('active');
            });

            document.getElementById('modalActualizar').classList.add('active');
            document.getElementById('modalObservaciones').focus();
        }

        function cerrarModalActualizar() {
            document.getElementById('modalActualizar').classList.remove('active');
        }

        function seleccionarEstado(estado, boton) {
            document.getElementById('modalNuevoEstado').value = estado;
            document.querySelectorAll('#estadoBotones .estado-btn').forEach(function(btn) {
                btn.classList.remove('active');
            });
            boton.classList.add('active');
        }

        document.getElementById('formActualizarModal').addEventListener('submit', function(e) {
            var estado = document.getElementById('modalNuevoEstado').value.trim();
            var observaciones = document.getElementById('modalObservaciones').value.trim();

            if (!estado) {
                e.preventDefault();
                alert('Debe seleccionar un estado.');
                return;
            }

            if (!observaciones) {
                e.preventDefault();
                alert('Debe ingresar observaciones o comentarios.');
            }
        });

        document.getElementById('modalActualizar').addEventListener('click', function(e) {
            if (e.target.id === 'modalActualizar') {
                cerrarModalActualizar();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarModalActualizar();
            }
        });
    </script>

    <div class="footer">
        <p>&copy; 2024 Sistema de Solicitudes - Municipalidad de Los Lagos</p>
    </div>
</body>
</html>
