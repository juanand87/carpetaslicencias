<?php
/**
 * Configuración SMTP
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

// Obtener configuración SMTP actual
$sql = "SELECT * FROM config_smtp LIMIT 1";
$result = $conn->query($sql);
$config_smtp = $result->num_rows > 0 ? $result->fetch_assoc() : null;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_smtp'])) {
    $host = $_POST['host'] ?? '';
    $puerto = intval($_POST['puerto'] ?? 0);
    $usuario_smtp = $_POST['usuario'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';
    $from_email = $_POST['from_email'] ?? '';
    $from_nombre = $_POST['from_nombre'] ?? '';

    // Validación
    if (empty($host) || $puerto === 0 || empty($usuario_smtp) || empty($from_email) || empty($from_nombre)) {
        $tipo_mensaje = 'error';
        $mensaje = 'Por favor complete todos los campos';
    } else {
        if ($config_smtp) {
            // Actualizar
            $sql = "UPDATE config_smtp SET host = ?, puerto = ?, usuario = ?, contraseña = ?, 
                    from_email = ?, from_nombre = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sissssi", $host, $puerto, $usuario_smtp, $contraseña, $from_email, $from_nombre, $config_smtp['id']);
        } else {
            // Insertar
            $sql = "INSERT INTO config_smtp (host, puerto, usuario, contraseña, from_email, from_nombre) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisss", $host, $puerto, $usuario_smtp, $contraseña, $from_email, $from_nombre);
        }

        if ($stmt->execute()) {
            $tipo_mensaje = 'exito';
            $mensaje = 'Configuración SMTP guardada correctamente';
            // Recargar
            $result = $conn->query("SELECT * FROM config_smtp LIMIT 1");
            $config_smtp = $result->fetch_assoc();
        } else {
            $tipo_mensaje = 'error';
            $mensaje = 'Error al guardar la configuración';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración SMTP | Administración</title>
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

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
            line-height: 1.6;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-grid-full {
            grid-column: 1 / -1;
        }

        @media (max-width: 1024px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Configuración - SMTP</h1>
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
                    <a href="plantillas_correo.php">📧 Plantillas de Correo</a>
                    <a href="config_smtp.php" class="active">⚙️ Configuración SMTP</a>
                    <a href="usuarios.php">👥 Gestionar Funcionarios</a>
                    <a href="../funcionario/dashboard.php">📋 Ver Solicitudes</a>
                    <a href="../funcionario/logout.php">🚪 Cerrar Sesión</a>
                </nav>
            </div>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="admin-content">
                <h2>Configuración del Servidor SMTP</h2>
                <p>Configure los parámetros del servidor SMTP para enviar correos electrónicos.</p>

                <div class="info-box">
                    <strong>ℹ️ Información:</strong><br>
                    Si usa Gmail, debe generar una contraseña de aplicación. 
                    Para más información, visite: https://support.google.com/accounts/answer/185833
                </div>

                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-<?= $tipo_mensaje ?>">
                        <?= htmlspecialchars($mensaje) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="formulario">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="host">Host SMTP <span class="requerido">*</span></label>
                            <input type="text" id="host" name="host" required 
                                   value="<?= htmlspecialchars($config_smtp['host'] ?? 'smtp.gmail.com') ?>"
                                   placeholder="Ej: smtp.gmail.com">
                        </div>

                        <div class="form-group">
                            <label for="puerto">Puerto <span class="requerido">*</span></label>
                            <input type="number" id="puerto" name="puerto" required 
                                   value="<?= htmlspecialchars($config_smtp['puerto'] ?? '587') ?>"
                                   placeholder="Ej: 587">
                        </div>

                        <div class="form-group form-grid-full">
                            <label for="usuario">Usuario SMTP <span class="requerido">*</span></label>
                            <input type="email" id="usuario" name="usuario" required 
                                   value="<?= htmlspecialchars($config_smtp['usuario'] ?? '') ?>"
                                   placeholder="Ej: tu_correo@gmail.com">
                        </div>

                        <div class="form-group form-grid-full">
                            <label for="contraseña">Contraseña SMTP <span class="requerido">*</span></label>
                            <input type="password" id="contraseña" name="contraseña" required 
                                   placeholder="Ingrese la contraseña (no se muestra por seguridad)">
                        </div>

                        <div class="form-group form-grid-full">
                            <label for="from_email">Email del Remitente <span class="requerido">*</span></label>
                            <input type="email" id="from_email" name="from_email" required 
                                   value="<?= htmlspecialchars($config_smtp['from_email'] ?? '') ?>"
                                   placeholder="Ej: noreply@municipio.cl">
                        </div>

                        <div class="form-group form-grid-full">
                            <label for="from_nombre">Nombre del Remitente <span class="requerido">*</span></label>
                            <input type="text" id="from_nombre" name="from_nombre" required 
                                   value="<?= htmlspecialchars($config_smtp['from_nombre'] ?? 'Sistema Carpetas Licencias') ?>"
                                   placeholder="Ej: Sistema Carpetas Licencias">
                        </div>
                    </div>

                    <button type="submit" name="guardar_smtp" class="btn btn-success" style="margin-top: 20px;">
                        ✓ Guardar Configuración
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2024 Sistema de Solicitudes - Municipalidad de Los Lagos</p>
    </div>
</body>
</html>
