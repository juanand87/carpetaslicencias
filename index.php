<?php
/**
 * Página principal - Formulario de solicitud (Solicitante)
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/solicitudes.php';
require_once __DIR__ . '/includes/auth.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario de solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_solicitud'])) {
    $resultado = crear_solicitud($conn, $_POST);
    $tipo_mensaje = $resultado['success'] ? 'exito' : 'error';
    $mensaje = $resultado['mensaje'];
}

// Obtener municipalidades
$municipalidades = [];
$sql = "SELECT * FROM municipalidades ORDER BY nombre ASC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $municipalidades[] = $row;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Solicitudes - Carpetas de Licencias</title>
    <link rel="stylesheet" href="public/css/style.css?v=20260730-3">
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Sistema de Solicitudes de Carpetas de Licencias</h1>
            <p>Los Lagos - Municipalidad</p>
        </div>
    </div>

    <div class="container">
        <div class="formulario-contenedor">
            <h2>Solicitar Carpeta de Licencia</h2>
            <p class="descripcion">Complete el formulario para solicitar la carpeta de licencia de conducir de una persona.</p>

            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?= $tipo_mensaje ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="formulario" id="form-solicitud" data-espera-mensaje="Enviando su solicitud…">
                <div class="form-group">
                    <label for="nombre">Nombre del Solicitado <span class="requerido">*</span></label>
                    <input type="text" id="nombre" name="nombre" required placeholder="Ej: Juan">
                </div>

                <div class="form-group">
                    <label for="apellido_paterno">Apellido Paterno del Solicitado <span class="requerido">*</span></label>
                    <input type="text" id="apellido_paterno" name="apellido_paterno" required placeholder="Ej: Pérez">
                </div>

                <div class="form-group">
                    <label for="apellido_materno">Apellido Materno del Solicitado</label>
                    <input type="text" id="apellido_materno" name="apellido_materno" placeholder="Ej: García">
                </div>

                <div class="form-group">
                    <label for="run">RUN (Rol Único Nacional) <span class="requerido">*</span></label>
                    <input type="text" id="run" name="run" required placeholder="Ej: 12.345.678-9">
                    <small>Formato: XX.XXX.XXX-X</small>
                </div>

                <div class="form-group">
                    <label for="correo_solicitante">Correo Electrónico del Solicitante <span class="requerido">*</span></label>
                    <input type="email" id="correo_solicitante" name="correo_solicitante" required placeholder="Ej: correo@ejemplo.com">
                </div>

                <div class="form-group">
                    <label for="municipalidad_id">Municipalidad <span class="requerido">*</span></label>
                    <select id="municipalidad_id" name="municipalidad_id" required>
                        <option value="">Seleccione una municipalidad...</option>
                        <?php foreach ($municipalidades as $municipalidad): ?>
                            <option value="<?= $municipalidad['id'] ?>">
                                <?= htmlspecialchars($municipalidad['nombre']) ?> - <?= htmlspecialchars($municipalidad['region']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <p class="nota"><strong>Nota:</strong> Se enviará un correo de confirmación al email registrado.</p>

                <button type="submit" name="enviar_solicitud" class="btn btn-primary">
                    Enviar Solicitud
                </button>
            </form>
        </div>

        <div class="enlaces-rapidos">
            <h3>¿Es funcionario?</h3>
            <p>Si trabaja en el municipio de Los Lagos y necesita acceder al panel de gestión de solicitudes:</p>
            <a href="funcionario/login.php" class="btn btn-secondary">Acceso de Funcionario</a>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2024 Sistema de Solicitudes - Municipalidad de Los Lagos</p>
    </div>
    <script src="public/js/espera.js?v=20260730-1" defer></script>
</body>
</html>
