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
                    <label id="etiqueta_municipalidad">Municipalidad <span class="requerido">*</span></label>
                    <div class="selector-comuna" id="selector_comuna">
                        <button
                            type="button"
                            class="selector-comuna-boton"
                            id="selector_comuna_boton"
                            aria-labelledby="etiqueta_municipalidad selector_comuna_texto"
                            aria-haspopup="listbox"
                            aria-expanded="false"
                        >
                            <span id="selector_comuna_texto">Seleccione una municipalidad...</span>
                            <span class="selector-comuna-flecha" aria-hidden="true"></span>
                        </button>
                        <div class="selector-comuna-panel" id="selector_comuna_panel" hidden>
                            <div class="selector-comuna-busqueda">
                                <span aria-hidden="true">⌕</span>
                                <input
                                    type="search"
                                    id="buscador_municipalidad"
                                    placeholder="Buscar comuna..."
                                    autocomplete="off"
                                    aria-label="Buscar comuna"
                                >
                            </div>
                            <ul class="selector-comuna-lista" id="lista_municipalidades" role="listbox"></ul>
                        </div>
                    </div>
                    <select id="municipalidad_id" name="municipalidad_id" class="selector-comuna-native" tabindex="-1" aria-hidden="true">
                        <option value="">Seleccione una municipalidad...</option>
                        <?php foreach ($municipalidades as $municipalidad): ?>
                            <option
                                value="<?= (int) $municipalidad['id'] ?>"
                                data-region="<?= htmlspecialchars($municipalidad['region']) ?>"
                            ><?= htmlspecialchars($municipalidad['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div id="region_municipalidad" class="region-municipalidad" aria-live="polite" hidden>
                        <strong>Región:</strong> <span></span>
                    </div>
                </div>

                <p class="nota"><strong>Nota:</strong> Se enviará un correo de confirmación al email registrado.</p>

                <button type="submit" name="enviar_solicitud" class="btn btn-primary">
                    Enviar Solicitud
                </button>
            </form>
        </div>

    </div>

    <div class="footer">
        <p>&copy; 2024 Sistema de Solicitudes - Municipalidad de Los Lagos</p>
        <div class="acceso-funcionario">
            <a href="funcionario/login.php">Acceso Funcionario</a>
        </div>
    </div>
    <script src="public/js/buscador-municipalidades.js?v=20260812-3" defer></script>
    <script src="public/js/espera.js?v=20260730-1" defer></script>
</body>
</html>
