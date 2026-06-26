<?php
/**
 * Configuración de Plantillas de Correo
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Verificar autenticación y rol
if (!verificar_autenticacion() || !verificar_rol('administrador')) {
    header('Location: ../funcionario/login.php');
    exit;
}

$usuario = obtener_usuario_autenticado($conn);
$mensaje = '';
$tipo_mensaje = '';

// Obtener todas las plantillas
$sql = "SELECT * FROM plantillas_correo ORDER BY id ASC";
$result = $conn->query($sql);
$plantillas = [];
while ($row = $result->fetch_assoc()) {
    $plantillas[] = $row;
}

// Procesandor actualización de plantilla
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_plantilla'])) {
    $plantilla_id = intval($_POST['plantilla_id']);
    $asunto = $_POST['asunto'] ?? '';
    $cuerpo = $_POST['cuerpo'] ?? '';

    if (empty($asunto) || empty($cuerpo)) {
        $tipo_mensaje = 'error';
        $mensaje = 'Por favor complete todos los campos';
    } else {
        $sql = "UPDATE plantillas_correo SET asunto = ?, cuerpo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $asunto, $cuerpo, $plantilla_id);

        if ($stmt->execute()) {
            $tipo_mensaje = 'exito';
            $mensaje = 'Plantilla actualizada correctamente';
            // Recargar plantillas
            $result = $conn->query("SELECT * FROM plantillas_correo ORDER BY id ASC");
            $plantillas = [];
            while ($row = $result->fetch_assoc()) {
                $plantillas[] = $row;
            }
        } else {
            $tipo_mensaje = 'error';
            $mensaje = 'Error al actualizar la plantilla';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plantillas de Correo | Administración</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .dashboard-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .admin-menu {
            background: white;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .admin-menu nav a {
            display: block;
            padding: 12px 15px;
            margin-bottom: 5px;
            border-radius: 4px;
            color: #333;
            text-decoration: none;
        }

        .admin-menu nav a:hover,
        .admin-menu nav a.active {
            background-color: #667eea;
            color: white;
        }

        .admin-content {
            background: white;
            padding: 30px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .plantilla-card {
            background: #f9f9f9;
            padding: 20px;
            border-left: 4px solid #667eea;
            border-radius: 4px;
            margin-bottom: 20px;
            cursor: pointer;
        }

        .plantilla-card.active {
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .plantilla-card h4 {
            color: #333;
            margin-bottom: 5px;
        }

        .plantilla-card p {
            color: #666;
            font-size: 12px;
            margin: 0;
        }

        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
        }

        .variables-ayuda {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
            font-size: 12px;
            line-height: 1.8;
        }

        .variables-ayuda strong {
            color: #0066cc;
        }

        @media (max-width: 1024px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Configuración - Plantillas de Correo</h1>
            <p><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido_paterno']) ?> | Administrador</p>
        </div>
    </div>

    <div class="container">
        <div class="dashboard-container">
            <!-- MENU LATERAL -->
            <div class="admin-menu">
                <h3 style="margin-bottom: 15px; font-size: 16px; color: #333;">Menú</h3>
                <nav>
                    <a href="dashboard.php">📊 Dashboard</a>
                    <a href="plantillas_correo.php" class="active">📧 Plantillas de Correo</a>
                    <a href="config_smtp.php">⚙️ Configuración SMTP</a>
                    <a href="usuarios.php">👥 Gestionar Funcionarios</a>
                    <a href="../funcionario/dashboard.php">📋 Ver Solicitudes</a>
                    <a href="../funcionario/logout.php">🚪 Cerrar Sesión</a>
                </nav>
            </div>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="admin-content">
                <h2>Plantillas de Correo</h2>
                <p>Personaliza los correos electrónicos que se envían en el sistema.</p>

                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-<?= $tipo_mensaje ?>">
                        <?= htmlspecialchars($mensaje) ?>
                    </div>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 250px 1fr; gap: 20px; margin-top: 20px;">
                    <!-- LISTA DE PLANTILLAS -->
                    <div>
                        <h3 style="font-size: 14px; margin-bottom: 10px; color: #333;">Plantillas Disponibles</h3>
                        <?php foreach ($plantillas as $index => $plantilla): ?>
                            <div class="plantilla-card" onclick="mostrarPlantilla(<?= $plantilla['id'] ?>)">
                                <h4><?= htmlspecialchars($plantilla['tipo']) ?></h4>
                                <p><?= substr(htmlspecialchars($plantilla['asunto']), 0, 50) ?>...</p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- FORMULARIO DE EDICIÓN -->
                    <div>
                        <?php foreach ($plantillas as $plantilla): ?>
                            <div id="plantilla-<?= $plantilla['id'] ?>" style="display: <?= ($plantilla['id'] === 1) ? 'block' : 'none' ?>;">
                                <h3><?= htmlspecialchars($plantilla['tipo']) ?></h3>

                                <form method="POST" class="formulario">
                                    <input type="hidden" name="plantilla_id" value="<?= $plantilla['id'] ?>">

                                    <div class="form-group">
                                        <label for="asunto">Asunto</label>
                                        <input type="text" id="asunto" name="asunto" value="<?= htmlspecialchars($plantilla['asunto']) ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="cuerpo">Cuerpo del Correo</label>
                                        <textarea id="cuerpo" name="cuerpo" rows="10" required><?= htmlspecialchars($plantilla['cuerpo']) ?></textarea>
                                    </div>

                                    <div class="variables-ayuda">
                                        <strong>Variables disponibles:</strong><br>
                                        {nombre} - Nombre del solicitado<br>
                                        {apellido_paterno} - Apellido paterno<br>
                                        {apellido_materno} - Apellido materno<br>
                                        {run} - RUN del solicitado<br>
                                        {municipalidad} - Municipalidad<br>
                                        {estado} - Estado de la solicitud<br>
                                        {observaciones} - Observaciones<br>
                                        {correo_solicitante} - Email del solicitante
                                    </div>

                                    <button type="submit" name="actualizar_plantilla" class="btn btn-success" style="margin-top: 15px;">
                                        ✓ Guardar Cambios
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function mostrarPlantilla(id) {
            // Ocultar todas las plantillas
            const plantillas = document.querySelectorAll('[id^="plantilla-"]');
            plantillas.forEach(p => p.style.display = 'none');

            // Mostrar plantilla seleccionada
            document.getElementById('plantilla-' + id).style.display = 'block';

            // Actualizar estilos de cards
            const cards = document.querySelectorAll('.plantilla-card');
            cards.forEach(card => card.classList.remove('active'));
            event.target.closest('.plantilla-card').classList.add('active');
        }
    </script>

    <div class="footer">
        <p>&copy; 2024 Sistema de Solicitudes - Municipalidad de Los Lagos</p>
    </div>
</body>
</html>
