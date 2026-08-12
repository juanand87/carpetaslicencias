<?php
/**
 * Página de Test y Demostración
 * (Opcional - para probar funcionalidades)
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/solicitudes.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Información del Sistema | Carpetas de Licencias</title>
    <link rel="stylesheet" href="public/css/style.css?v=20260730-3">
    <style>
        .info-section {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .info-section h2 {
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .feature-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .feature-card h3 {
            margin-top: 10px;
        }

        .links-section {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }

        .link-btn {
            display: block;
            padding: 20px;
            text-align: center;
            background: #f9f9f9;
            border: 2px solid #ddd;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
        }

        .link-btn:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }

        .link-btn strong {
            display: block;
            font-size: 18px;
            margin-bottom: 5px;
            color: #667eea;
        }

        .status-box {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .status-ok {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .links-section {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Sistema de Solicitudes de Carpetas de Licencias</h1>
            <p>Información y Acceso Rápido</p>
        </div>
    </div>

    <div class="container">
        <!-- ESTADO DEL SISTEMA -->
        <div class="info-section">
            <h2>✅ Estado del Sistema</h2>

            <?php
            // Verificar conexión a BD
            if ($conn->connect_error) {
                echo '<div class="status-box status-error">❌ El Sistema de Base de Datos <strong>NO</strong> está disponible</div>';
            } else {
                echo '<div class="status-box status-ok">✅ Conexión a Base de Datos: <strong>CORRECTA</strong></div>';
                
                // Contar solicitudes
                $sql = "SELECT COUNT(*) as total FROM solicitudes";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                
                echo '<div class="status-box status-ok">✅ Total de Solicitudes en Sistema: <strong>' . $row['total'] . '</strong></div>';
            }
            ?>
        </div>

        <!-- ACCESO RÁPIDO -->
        <div class="info-section">
            <h2>🚀 Acceso Rápido</h2>
            <div class="links-section">
                <a href="index.php" class="link-btn">
                    <strong>📋</strong>
                    Crear Solicitud
                </a>
                <a href="funcionario/login.php" class="link-btn">
                    <strong>🔐</strong>
                    Acceso Funcionario
                </a>
                <a href="administrador/dashboard.php" class="link-btn">
                    <strong>⚙️</strong>
                    Administración
                </a>
            </div>
        </div>

        <!-- CARACTERÍSTICAS -->
        <div class="info-section">
            <h2>🎯 Características Principales</h2>

            <div class="feature-grid">
                <div class="feature-card">
                    <div>📝</div>
                    <h3>Solicitud Pública</h3>
                    <p>Formulario sin login</p>
                </div>

                <div class="feature-card">
                    <div>👨‍💼</div>
                    <h3>Gestión Funcionario</h3>
                    <p>Panel de solicitudes</p>
                </div>

                <div class="feature-card">
                    <div>⚙️</div>
                    <h3>Administración</h3>
                    <p>Configuración completa</p>
                </div>

                <div class="feature-card">
                    <div>📧</div>
                    <h3>Notificaciones Email</h3>
                    <p>Automáticas por cambios</p>
                </div>

                <div class="feature-card">
                    <div>📊</div>
                    <h3>Bitácora de Cambios</h3>
                    <p>Registro completo</p>
                </div>

                <div class="feature-card">
                    <div>🗺️</div>
                    <h3>Todas las Comunas</h3>
                    <p>346 municipios de Chile</p>
                </div>
            </div>
        </div>

        <!-- DOCUMENTACIÓN -->
        <div class="info-section">
            <h2>📖 Documentación</h2>

            <h3>Archivos Importantes:</h3>
            <ul>
                <li><strong>README.md</strong> - Documentación completa del sistema</li>
                <li><strong>INSTALACION.md</strong> - Guía paso a paso de instalación</li>
                <li><strong>sql/database.sql</strong> - Script de base de datos</li>
                <li><strong>config/database.php</strong> - Configuración de BD</li>
                <li><strong>config/smtp.php</strong> - Configuración de correos</li>
            </ul>

            <h3>Datos de Prueba:</h3>
            <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto;">
Administrador:
- Correo: admin@carpetaslicencias.cl
- Contraseña: admin123

Funcionario: Crear desde administración
            </pre>

            <p>
                <strong>Nota:</strong> Cambiar la contraseña del admin la primera vez que inicia sesión es altamente recomendado.
            </p>
        </div>

        <!-- PRÓXIMOS PASOS -->
        <div class="info-section">
            <h2>➡️ Próximos Pasos</h2>

            <ol style="line-height: 2;">
                <li>Lee la guía <strong>INSTALACION.md</strong></li>
                <li>Configura la conexión a Base de Datos en <strong>config/database.php</strong></li>
                <li>Accede a administración y configura SMTP</li>
                <li>Crea los funcionarios que necesites</li>
                <li>Personaliza las plantillas de correo</li>
                <li>¡Comienza a recibir solicitudes!</li>
            </ol>
        </div>

        <!-- SOPORTE -->
        <div class="info-section" style="background: #f9f9f9; border-left: 4px solid #667eea;">
            <h2>❓ ¿Necesitas Ayuda?</h2>

            <p><strong>Problemas Comunes:</strong></p>
            <ul>
                <li>Error de conexión → Verifica BD y config/database.php</li>
                <li>Correos no llegan → Configura SMTP en administración</li>
                <li>Error de RUN → Usa formato: 12.345.678-9</li>
            </ul>

            <p>
                Consulta <strong>README.md</strong> para más información sobre troubleshooting.
            </p>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2024 Sistema de Solicitudes - Municipalidad de Los Lagos</p>
    </div>
</body>
</html>
