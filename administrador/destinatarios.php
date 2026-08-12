<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/email.php';

if (!verificar_autenticacion() || !verificar_rol('administrador')) {
    header('Location: ../funcionario/login.php');
    exit;
}

// Los destinatarios se administran como usuarios con rol funcionario.
header('Location: usuarios.php');
exit;

$usuario = obtener_usuario_autenticado($conn);
$mensaje = '';
$tipo_mensaje = '';
asegurar_tabla_destinatarios($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_destinatario'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $correo = filter_var(trim($_POST['correo'] ?? ''), FILTER_SANITIZE_EMAIL);
    $cargo = trim($_POST['cargo'] ?? '');

    if ($nombre === '' || $apellido === '' || $cargo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $tipo_mensaje = 'error';
        $mensaje = 'Complete nombre, apellido, correo válido y cargo.';
    } else {
        if ($id > 0) {
            $stmt = $conn->prepare('UPDATE destinatarios_notificacion SET nombre = ?, apellido = ?, correo = ?, cargo = ? WHERE id = ?');
            $stmt->bind_param('ssssi', $nombre, $apellido, $correo, $cargo, $id);
        } else {
            $stmt = $conn->prepare('INSERT INTO destinatarios_notificacion (nombre, apellido, correo, cargo) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $nombre, $apellido, $correo, $cargo);
        }
        if ($stmt->execute()) {
            $tipo_mensaje = 'exito';
            $mensaje = $id > 0 ? 'Destinatario actualizado correctamente.' : 'Destinatario agregado correctamente.';
        } else {
            $tipo_mensaje = 'error';
            $mensaje = $stmt->errno === 1062 ? 'Ese correo ya está registrado.' : 'No fue posible guardar el destinatario.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $activo = (int) ($_POST['activo'] ?? 0) === 1 ? 1 : 0;
    $stmt = $conn->prepare('UPDATE destinatarios_notificacion SET activo = ? WHERE id = ?');
    $stmt->bind_param('ii', $activo, $id);
    if ($stmt->execute()) {
        $tipo_mensaje = 'exito';
        $mensaje = $activo ? 'Destinatario activado.' : 'Destinatario desactivado.';
    }
}

$editar = null;
if (isset($_GET['editar'])) {
    $id_editar = (int) $_GET['editar'];
    $stmt = $conn->prepare('SELECT * FROM destinatarios_notificacion WHERE id = ?');
    $stmt->bind_param('i', $id_editar);
    $stmt->execute();
    $editar = $stmt->get_result()->fetch_assoc();
}
$destinatarios = $conn->query('SELECT * FROM destinatarios_notificacion ORDER BY activo DESC, nombre, apellido')->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinatarios de notificaciones | Administración</title>
    <link rel="stylesheet" href="../public/css/style.css?v=20260730-3">
    <style>
        .dashboard-container{display:grid;grid-template-columns:250px 1fr;gap:20px;margin-bottom:20px}.admin-menu,.admin-content{background:#fff;padding:20px;border-radius:4px;box-shadow:0 2px 10px rgba(0,0,0,.1)}.admin-content{padding:30px}.admin-menu nav a{display:block;padding:12px 15px;margin-bottom:5px;border-radius:4px;color:#333;text-decoration:none}.admin-menu nav a:hover,.admin-menu nav a.active{background:#667eea;color:#fff}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}.acciones{display:flex;gap:6px;flex-wrap:wrap}.acciones form{margin:0}@media(max-width:1024px){.dashboard-container,.form-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="header"><div class="container"><h1>Destinatarios de notificaciones</h1><p><?= htmlspecialchars($usuario['nombre'].' '.$usuario['apellido_paterno']) ?> | Administrador</p></div></div>
<div class="container"><div class="dashboard-container">
    <div class="admin-menu"><h3>Menú</h3><nav>
        <a href="dashboard.php">📊 Dashboard</a><a href="destinatarios.php" class="active">🔔 Destinatarios</a>
        <a href="plantillas_correo.php">📧 Plantillas de Correo</a><a href="config_smtp.php">⚙️ Configuración SMTP</a>
        <a href="usuarios.php">👥 Gestionar Funcionarios</a><a href="../funcionario/dashboard.php">📋 Ver Solicitudes</a><a href="../funcionario/logout.php">🚪 Cerrar Sesión</a>
    </nav></div>
    <div class="admin-content">
        <h2><?= $editar ? 'Editar destinatario' : 'Agregar destinatario' ?></h2>
        <p>Estas personas recibirán un correo cada vez que ingrese una nueva solicitud.</p>
        <?php if ($mensaje): ?><div class="alert alert-<?= $tipo_mensaje ?>"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
        <form method="POST" class="formulario">
            <input type="hidden" name="id" value="<?= (int)($editar['id'] ?? 0) ?>">
            <div class="form-grid">
                <div class="form-group"><label>Nombre *</label><input name="nombre" required value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>"></div>
                <div class="form-group"><label>Apellido *</label><input name="apellido" required value="<?= htmlspecialchars($editar['apellido'] ?? '') ?>"></div>
                <div class="form-group"><label>Correo electrónico *</label><input type="email" name="correo" required value="<?= htmlspecialchars($editar['correo'] ?? '') ?>"></div>
                <div class="form-group"><label>Cargo *</label><input name="cargo" required value="<?= htmlspecialchars($editar['cargo'] ?? '') ?>" placeholder="Ej: Encargado de Licencias"></div>
            </div>
            <button class="btn btn-success" name="guardar_destinatario" type="submit"><?= $editar ? 'Guardar cambios' : 'Agregar destinatario' ?></button>
            <?php if ($editar): ?><a class="btn btn-secondary" style="width:auto" href="destinatarios.php">Cancelar</a><?php endif; ?>
        </form>
        <h2 style="margin-top:35px">Personas registradas</h2>
        <?php if (!$destinatarios): ?><div class="alert alert-info">Aún no hay destinatarios configurados.</div><?php else: ?>
        <table class="tabla"><thead><tr><th>Nombre</th><th>Correo</th><th>Cargo</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
        <?php foreach ($destinatarios as $d): ?><tr>
            <td><?= htmlspecialchars($d['nombre'].' '.$d['apellido']) ?></td><td><?= htmlspecialchars($d['correo']) ?></td><td><?= htmlspecialchars($d['cargo']) ?></td><td><?= $d['activo'] ? 'Activo' : 'Inactivo' ?></td>
            <td><div class="acciones"><a class="btn btn-info btn-sm" style="width:auto;margin:0" href="?editar=<?= (int)$d['id'] ?>">Editar</a>
            <form method="POST"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>"><input type="hidden" name="activo" value="<?= $d['activo'] ? 0 : 1 ?>"><button class="btn <?= $d['activo'] ? 'btn-danger' : 'btn-success' ?> btn-sm" name="cambiar_estado" type="submit"><?= $d['activo'] ? 'Desactivar' : 'Activar' ?></button></form></div></td>
        </tr><?php endforeach; ?></tbody></table><?php endif; ?>
    </div>
</div></div>
<div class="footer"><p>&copy; <?= date('Y') ?> Sistema de Solicitudes - Municipalidad de Los Lagos</p></div>
</body></html>

