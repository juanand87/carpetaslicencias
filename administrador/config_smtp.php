<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/email.php';

if (!verificar_autenticacion() || !verificar_rol('administrador')) {
    header('Location: ../funcionario/login.php');
    exit;
}
$usuario = obtener_usuario_autenticado($conn);
$mensaje = '';
$tipo_mensaje = '';
$result = $conn->query('SELECT * FROM config_smtp LIMIT 1');
$config_smtp = $result->num_rows > 0 ? $result->fetch_assoc() : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_smtp'])) {
    $host = trim($_POST['host'] ?? '');
    $puerto = (int) ($_POST['puerto'] ?? 0);
    $usuario_smtp = trim($_POST['usuario'] ?? '');
    $nueva_contrasena = $_POST['contraseña'] ?? '';
    $contrasena = $nueva_contrasena !== '' ? $nueva_contrasena : ($config_smtp['contraseña'] ?? '');
    $from_email = filter_var(trim($_POST['from_email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $from_nombre = trim($_POST['from_nombre'] ?? '');

    if ($host === '' || $puerto < 1 || $puerto > 65535 || $usuario_smtp === '' || $contrasena === '' || !filter_var($from_email, FILTER_VALIDATE_EMAIL) || $from_nombre === '') {
        $tipo_mensaje = 'error';
        $mensaje = 'Revise los campos obligatorios y escriba correos válidos.';
    } else {
        if ($config_smtp) {
            $stmt = $conn->prepare('UPDATE config_smtp SET host = ?, puerto = ?, usuario = ?, contraseña = ?, from_email = ?, from_nombre = ? WHERE id = ?');
            $stmt->bind_param('sissssi', $host, $puerto, $usuario_smtp, $contrasena, $from_email, $from_nombre, $config_smtp['id']);
        } else {
            $stmt = $conn->prepare('INSERT INTO config_smtp (host, puerto, usuario, contraseña, from_email, from_nombre) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sissss', $host, $puerto, $usuario_smtp, $contrasena, $from_email, $from_nombre);
        }
        if ($stmt->execute()) {
            $tipo_mensaje = 'exito';
            $mensaje = 'Configuración SMTP guardada correctamente.';
            $config_smtp = $conn->query('SELECT * FROM config_smtp LIMIT 1')->fetch_assoc();
        } else {
            $tipo_mensaje = 'error';
            $mensaje = 'No fue posible guardar la configuración SMTP.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['probar_smtp'])) {
    $correo_prueba = filter_var(trim($_POST['correo_prueba'] ?? ''), FILTER_SANITIZE_EMAIL);
    if (!filter_var($correo_prueba, FILTER_VALIDATE_EMAIL)) {
        $tipo_mensaje = 'error';
        $mensaje = 'Ingrese un correo válido para realizar la prueba.';
    } elseif (!$config_smtp) {
        $tipo_mensaje = 'error';
        $mensaje = 'Primero debe guardar la configuración SMTP.';
    } else {
        try {
            enviar_correo($conn, $correo_prueba, 'Prueba SMTP - Carpetas de Licencias',
                '<h2>Configuración SMTP correcta</h2><p>Este mensaje confirma que PHPMailer pudo conectarse y enviar correo mediante el servidor configurado.</p><p>Municipalidad de Los Lagos</p>');
            $tipo_mensaje = 'exito';
            $mensaje = 'Correo de prueba enviado correctamente a ' . $correo_prueba . '.';
        } catch (Throwable $e) {
            $tipo_mensaje = 'error';
            $mensaje = 'No se pudo enviar el correo de prueba: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Configuración SMTP | Administración</title><link rel="stylesheet" href="../public/css/style.css?v=20260730-3"><style>
.dashboard-container{display:grid;grid-template-columns:250px 1fr;gap:20px;margin-bottom:20px}.admin-menu,.admin-content{background:#fff;padding:20px;border-radius:4px;box-shadow:0 2px 10px rgba(0,0,0,.1)}.admin-content{padding:30px}.admin-menu nav a{display:block;padding:12px 15px;margin-bottom:5px;border-radius:4px;color:#333;text-decoration:none}.admin-menu nav a:hover,.admin-menu nav a.active{background:#0b2f5b;color:#fff}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}.form-grid-full{grid-column:1/-1}.info-box{background:#e7f3ff;border-left:4px solid #2196f3;padding:15px;margin:20px 0}.test-box{margin-top:35px;padding-top:25px;border-top:1px solid #ddd}@media(max-width:1024px){.dashboard-container,.form-grid{grid-template-columns:1fr}}
</style></head><body>
<div class="header"><div class="container"><h1>Configuración SMTP</h1><p><?= htmlspecialchars($usuario['nombre'].' '.$usuario['apellido_paterno']) ?> | Administrador</p></div></div>
<div class="container"><div class="dashboard-container"><div class="admin-menu"><h3>Menú</h3><nav>
<a href="dashboard.php">📊 Dashboard</a><a href="plantillas_correo.php">📧 Plantillas de Correo</a><a href="config_smtp.php" class="active">⚙️ Configuración SMTP</a><a href="usuarios.php">👥 Gestionar Funcionarios</a><a href="../funcionario/dashboard.php">📋 Ver Solicitudes</a><a href="../funcionario/logout.php">🚪 Cerrar Sesión</a>
</nav></div><div class="admin-content"><h2>Servidor de correo</h2><p>Los mensajes se envían mediante PHPMailer usando esta configuración.</p>
<div class="info-box"><strong>Seguridad automática:</strong> puerto 465 usa SSL; los demás puertos (por ejemplo 587) usan STARTTLS. Si utiliza Gmail, debe ingresar una contraseña de aplicación.</div>
<?php if ($mensaje): ?><div class="alert alert-<?= $tipo_mensaje ?>"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
<form method="POST" class="formulario"><div class="form-grid">
<div class="form-group"><label for="host">Host SMTP *</label><input id="host" name="host" required value="<?= htmlspecialchars($config_smtp['host'] ?? 'smtp.gmail.com') ?>"></div>
<div class="form-group"><label for="puerto">Puerto *</label><input type="number" min="1" max="65535" id="puerto" name="puerto" required value="<?= (int)($config_smtp['puerto'] ?? 587) ?>"></div>
<div class="form-group form-grid-full"><label for="usuario">Usuario SMTP *</label><input id="usuario" name="usuario" required value="<?= htmlspecialchars($config_smtp['usuario'] ?? '') ?>"></div>
<div class="form-group form-grid-full"><label for="contraseña">Contraseña SMTP <?= $config_smtp ? '(deje en blanco para conservar la actual)' : '*' ?></label><input type="password" id="contraseña" name="contraseña" <?= $config_smtp ? '' : 'required' ?> autocomplete="new-password"></div>
<div class="form-group"><label for="from_email">Correo remitente *</label><input type="email" id="from_email" name="from_email" required value="<?= htmlspecialchars($config_smtp['from_email'] ?? '') ?>"></div>
<div class="form-group"><label for="from_nombre">Nombre remitente *</label><input id="from_nombre" name="from_nombre" required value="<?= htmlspecialchars($config_smtp['from_nombre'] ?? 'Carpetas de Licencias') ?>"></div>
</div><button type="submit" name="guardar_smtp" class="btn btn-success">Guardar configuración</button></form>
<div class="test-box"><h2>Probar envío</h2><p>Guarde la configuración y envíe un mensaje real para comprobar la conexión.</p><form method="POST" class="formulario" data-espera-mensaje="Enviando el correo de prueba…"><div class="form-group"><label for="correo_prueba">Enviar prueba a *</label><input type="email" id="correo_prueba" name="correo_prueba" required value="<?= htmlspecialchars($usuario['correo'] ?? '') ?>"></div><button type="submit" name="probar_smtp" class="btn btn-primary">Enviar correo de prueba</button></form></div>
</div></div></div><script src="../public/js/espera.js?v=20260730-1" defer></script><div class="footer"><p>&copy; <?= date('Y') ?> Sistema de Solicitudes - Municipalidad de Los Lagos</p></div></body></html>
