<?php
/**
 * Dashboard de Administrador
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Verificar autenticación y rol
if (!verificar_autenticacion() || !verificar_rol('administrador')) {
    header('Location: ../funcionario/login.php');
    exit;
}

$usuario = obtener_usuario_autenticado($conn);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración | Sistema de Solicitudes</title>
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
            transition: all 0.3s;
        }

        .admin-menu nav a:hover {
            background-color: #f5f5f5;
            border-left: 3px solid #667eea;
            padding-left: 12px;
        }

        .admin-menu nav a.active {
            background-color: #667eea;
            color: white;
            border-left: 3px solid #5568d3;
        }

        .admin-content {
            background: white;
            padding: 30px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .opcion-menu {
            padding: 20px;
            background: #f9f9f9;
            border-radius: 4px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
            cursor: pointer;
            transition: all 0.3s;
        }

        .opcion-menu:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            background: white;
        }

        .opcion-menu h3 {
            color: #667eea;
            margin-bottom: 5px;
        }

        .opcion-menu p {
            color: #666;
            font-size: 13px;
            margin: 0;
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
            <h1>Panel de Administración</h1>
            <p><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido_paterno']) ?> | Administrador</p>
        </div>
    </div>

    <div class="container">
        <div class="dashboard-container">
            <!-- MENU LATERAL -->
            <div class="admin-menu">
                <h3 style="margin-bottom: 15px; font-size: 16px; color: #333;">Menú</h3>
                <nav>
                    <a href="dashboard.php" class="active">📊 Dashboard</a>
                    <a href="plantillas_correo.php">📧 Plantillas de Correo</a>
                    <a href="config_smtp.php">⚙️ Configuración SMTP</a>
                    <a href="usuarios.php">👥 Gestionar Funcionarios</a>
                    <a href="../funcionario/dashboard.php">📋 Ver Solicitudes</a>
                    <a href="../funcionario/logout.php">🚪 Cerrar Sesión</a>
                </nav>
            </div>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="admin-content">
                <h2>Administración del Sistema</h2>
                <p>Bienvenido al panel de administración. Desde aquí puede configurar el sistema y gestionar los usuarios.</p>

                <div style="margin-top: 30px;">
                    <h3>¿Qué desea hacer?</h3>

                    <a href="plantillas_correo.php" style="text-decoration: none; color: inherit;">
                        <div class="opcion-menu">
                            <h3>📧 Plantillas de Correo</h3>
                            <p>Personaliza los correos que se envían al crear solicitudes y cambiar estados</p>
                        </div>
                    </a>

                    <a href="config_smtp.php" style="text-decoration: none; color: inherit;">
                        <div class="opcion-menu">
                            <h3>⚙️ Configurar SMTP</h3>
                            <p>Configura el servidor SMTP para el envío de correos</p>
                        </div>
                    </a>

                    <a href="usuarios.php" style="text-decoration: none; color: inherit;">
                        <div class="opcion-menu">
                            <h3>👥 Gestionar Funcionarios</h3>
                            <p>Crea, edita o elimina usuarios funcionarios del sistema</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2024 Sistema de Solicitudes - Municipalidad de Los Lagos</p>
    </div>
</body>
</html>
