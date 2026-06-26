<?php
/**
 * Login de Funcionario
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$mensaje = '';
$tipo_mensaje = '';

// Si ya está autenticado, redirigir al dashboard
if (verificar_autenticacion()) {
    header('Location: dashboard.php');
    exit;
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $correo = $_POST['correo'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';
    
    $resultado = autenticar_usuario($conn, $correo, $contraseña);
    
    if ($resultado['success']) {
        // Verificar que sea un funcionario o administrador
        if (in_array($_SESSION['rol'], ['funcionario', 'administrador'])) {
            header('Location: dashboard.php');
            exit;
        } else {
            cerrar_sesion();
            $tipo_mensaje = 'error';
            $mensaje = 'No tienes permiso para acceder al panel de funcionario';
        }
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
    <title>Login - Funcionario | Sistema de Solicitudes</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .login-contenedor {
            max-width: 400px;
            margin: 80px auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .login-contenedor h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .login-contenedor .btn {
            width: 100%;
        }

        .login-pie {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .login-pie a {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Sistema de Solicitudes - Carpetas de Licencias</h1>
            <p>Acceso de Funcionario</p>
        </div>
    </div>

    <div class="login-contenedor">
        <h2>Acceso de Funcionario</h2>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?= $tipo_mensaje ?>">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="formulario">
            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" required placeholder="Ej: usuario@ejemplo.com" autofocus>
            </div>

            <div class="form-group">
                <label for="contraseña">Contraseña</label>
                <input type="password" id="contraseña" name="contraseña" required placeholder="Ingrese su contraseña">
            </div>

            <button type="submit" name="login" class="btn btn-primary">
                Iniciar Sesión
            </button>

            <div class="login-pie">
                <p><a href="../index.php">Volver al inicio</a></p>
            </div>
        </form>
    </div>

    <div class="footer">
        <p>&copy; 2024 Sistema de Solicitudes - Municipalidad de Los Lagos</p>
    </div>
</body>
</html>
