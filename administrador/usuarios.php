<?php
/**
 * Gestión de Funcionarios
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

// Obtener lista de funcionarios
$sql = "SELECT * FROM usuarios WHERE rol IN ('funcionario', 'administrador') ORDER BY nombre ASC";
$result = $conn->query($sql);
$funcionarios = [];
while ($row = $result->fetch_assoc()) {
    $funcionarios[] = $row;
}

// Procesar creación de nuevo funcionario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_funcionario'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
    $apellido_materno = trim($_POST['apellido_materno'] ?? '');
    $correo = filter_var($_POST['correo'] ?? '', FILTER_SANITIZE_EMAIL);
    $contraseña = $_POST['contraseña'] ?? '';
    $rol = $_POST['rol'] ?? 'funcionario';

    // Validación
    if (empty($nombre) || empty($apellido_paterno) || empty($correo) || empty($contraseña)) {
        $tipo_mensaje = 'error';
        $mensaje = 'Por favor complete todos los campos obligatorios';
    } elseif (strlen($contraseña) < 6) {
        $tipo_mensaje = 'error';
        $mensaje = 'La contraseña debe tener al menos 6 caracteres';
    } else {
        // Verificar que el correo no exista
        $sql_check = "SELECT id FROM usuarios WHERE correo = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $correo);
        $stmt_check->execute();

        if ($stmt_check->get_result()->num_rows > 0) {
            $tipo_mensaje = 'error';
            $mensaje = 'El correo ya está registrado';
        } else {
            // Crear usuario
            $contraseña_hash = password_hash($contraseña, PASSWORD_BCRYPT);
            $sql_insert = "INSERT INTO usuarios 
                          (nombre, apellido_paterno, apellido_materno, correo, contraseña, rol) 
                          VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssssss", $nombre, $apellido_paterno, $apellido_materno, 
                                     $correo, $contraseña_hash, $rol);

            if ($stmt_insert->execute()) {
                $tipo_mensaje = 'exito';
                $mensaje = 'Funcionario creado correctamente';
                // Recargar lista
                $result = $conn->query("SELECT * FROM usuarios WHERE rol IN ('funcionario', 'administrador') ORDER BY nombre ASC");
                $funcionarios = [];
                while ($row = $result->fetch_assoc()) {
                    $funcionarios[] = $row;
                }
            } else {
                $tipo_mensaje = 'error';
                $mensaje = 'Error al crear el funcionario';
            }
        }
    }
}

// Procesar desactivación de usuarios
if (isset($_GET['desactivar'])) {
    $usuario_id = intval($_GET['desactivar']);
    if ($usuario_id !== $usuario['id']) { // No permitir desactivarse a sí mismo
        $sql_update = "UPDATE usuarios SET activo = 0 WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $usuario_id);
        if ($stmt_update->execute()) {
            $tipo_mensaje = 'exito';
            $mensaje = 'Funcionario desactivado';
            // Recargar
            $result = $conn->query("SELECT * FROM usuarios WHERE rol IN ('funcionario', 'administrador') ORDER BY nombre ASC");
            $funcionarios = [];
            while ($row = $result->fetch_assoc()) {
                $funcionarios[] = $row;
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Funcionarios | Administración</title>
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

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-grid-full {
            grid-column: 1 / -1;
        }

        .tab-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .tab-button {
            padding: 12px 24px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab-button.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .usuario-estado {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .estado-activo {
            background-color: #d4edda;
            color: #155724;
        }

        .estado-inactivo {
            background-color: #f8d7da;
            color: #721c24;
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
            <h1>Gestión de Funcionarios</h1>
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
                    <a href="config_smtp.php">⚙️ Configuración SMTP</a>
                    <a href="usuarios.php" class="active">👥 Gestionar Funcionarios</a>
                    <a href="../funcionario/dashboard.php">📋 Ver Solicitudes</a>
                    <a href="../funcionario/logout.php">🚪 Cerrar Sesión</a>
                </nav>
            </div>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="admin-content">
                <h2>Gestión de Funcionarios</h2>
                <p>Crea, edita o desactiva usuarios funcionarios del sistema.</p>

                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-<?= $tipo_mensaje ?>">
                        <?= htmlspecialchars($mensaje) ?>
                    </div>
                <?php endif; ?>

                <!-- TABS -->
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="mostrarTab('crear')">➕ Crear Funcionario</button>
                    <button class="tab-button" onclick="mostrarTab('listar')">📋 Listar Funcionarios</button>
                </div>

                <!-- TAB: CREAR -->
                <div id="crear" class="tab-content active">
                    <h3>Crear Nuevo Funcionario</h3>

                    <form method="POST" class="formulario">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nombre">Nombre <span class="requerido">*</span></label>
                                <input type="text" id="nombre" name="nombre" required placeholder="Juan">
                            </div>

                            <div class="form-group">
                                <label for="apellido_paterno">Apellido Paterno <span class="requerido">*</span></label>
                                <input type="text" id="apellido_paterno" name="apellido_paterno" required placeholder="Pérez">
                            </div>

                            <div class="form-group">
                                <label for="apellido_materno">Apellido Materno</label>
                                <input type="text" id="apellido_materno" name="apellido_materno" placeholder="García">
                            </div>

                            <div class="form-group">
                                <label for="correo">Correo Electrónico <span class="requerido">*</span></label>
                                <input type="email" id="correo" name="correo" required placeholder="usuario@ejemplo.com">
                            </div>

                            <div class="form-group">
                                <label for="contraseña">Contraseña <span class="requerido">*</span></label>
                                <input type="password" id="contraseña" name="contraseña" required placeholder="Mínimo 6 caracteres">
                            </div>

                            <div class="form-group">
                                <label for="rol">Rol <span class="requerido">*</span></label>
                                <select id="rol" name="rol" required>
                                    <option value="funcionario">Funcionario</option>
                                    <option value="administrador">Administrador</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" name="crear_funcionario" class="btn btn-success">
                            ✓ Crear Funcionario
                        </button>
                    </form>
                </div>

                <!-- TAB: LISTAR -->
                <div id="listar" class="tab-content">
                    <h3>Funcionarios Activos</h3>

                    <?php if (empty($funcionarios)): ?>
                        <div class="alert alert-info">
                            No hay funcionarios registrados.
                        </div>
                    <?php else: ?>
                        <table class="tabla">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($funcionarios as $func): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($func['nombre'] . ' ' . $func['apellido_paterno']) ?></td>
                                        <td><?= htmlspecialchars($func['correo']) ?></td>
                                        <td><strong><?= ucfirst($func['rol']) ?></strong></td>
                                        <td>
                                            <span class="usuario-estado <?= $func['activo'] ? 'estado-activo' : 'estado-inactivo' ?>">
                                                <?= $func['activo'] ? 'Activo' : 'Inactivo' ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($func['fecha_creacion'])) ?></td>
                                        <td>
                                            <?php if ($func['activo'] && $func['id'] !== $usuario['id']): ?>
                                                <a href="?desactivar=<?= $func['id'] ?>" 
                                                   onclick="return confirm('¿Desactivar a <?= htmlspecialchars($func['nombre']) ?>?')"
                                                   class="btn btn-danger btn-sm" style="width: auto; margin: 0;">
                                                    Desactivar
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function mostrarTab(tab) {
            // Ocultar todos
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));

            // Mostrar seleccionado
            document.getElementById(tab).classList.add('active');
            event.target.classList.add('active');
        }
    </script>

    <div class="footer">
        <p>&copy; 2024 Sistema de Solicitudes - Municipalidad de Los Lagos</p>
    </div>
</body>
</html>
