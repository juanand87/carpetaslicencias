# Sistema de Solicitudes de Carpetas de Licencias de Conducir

Sistema integral para gestionar solicitudes de carpetas de licencias desde diferentes municipios De Chile, desarrollado en PHP vanilla con MySQL.

## Características

### 🟢 Perfil Solicitante
- Formulario sin registro ni autenticación
- Solicitud por: Nombre, Apellido Paterno, Apellido Materno, RUN, Email, Municipalidad
- Confirmación por email al completar la solicitud
- Notificación de cambios de estado

### 🔵 Perfil Funcionario
- Acceso con correo y contraseña
- Vista de todas las solicitudes
- Filtrado por estado o búsqueda
- Actualización de estados de solicitudes
- Visualización de bitácora de cambios
- Estados disponibles:
  - Pendiente
  - Cargada
  - Cargada con observaciones
  - No encontrada
  - Rechazada

### 🟣 Perfil Administrador
- Todas las funciones del Funcionario + administración
- Configuración de plantillas de correo (con variables dinámicas)
- Configuración SMTP
- Crear y gestionar funcionarios
- Cambiar roles de usuarios

## Requisitos

- XAMPP (Apache + MySQL + PHP)
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Navegador moderno

## Instalación

### 1. Descargar la base de datos

1. Abre phpMyAdmin (http://localhost/phpmyadmin)
2. Crea una nueva base de datos (puede usar el botón "Nuevo" o importar el SQL)
3. Ejecuta el script SQL proporciona:
   - Ve a SQL y copia el contenido de `sql/database.sql`
   - Ejecuta en phpMyAdmin o crea un nuevo archivo SQL

Alternativamente, ejecuta en la terminal:
```bash
mysql -u root < sql/database.sql
```

### 2. Configurar la conexión a.base de datos

Edita `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Tu contraseña de MySQL si la tiene
define('DB_NAME', 'carpetas_licencias');
```

### 3. Acceder al sistema

- **Solicitante**: http://localhost/carpetaslicencias/
- **Funcionario**: http://localhost/carpetaslicencias/funcionario/login.php
- **Administrador**: http://localhost/carpetaslicencias/administrador/dashboard.php

## Usuarios por Defecto

### Administrador
- **Correo**: admin@carpetaslicencias.cl
- **Contraseña**: admin123

## Configuración SMTP (Importante)

Para que el sistema envíe correos:

1. Accede al panel de administración
2. Ve a "Configuración SMTP"
3. Configura los datos del servidor SMTP (Gmail, Outlook, etc.)

### Ejemplo para Gmail:
- **Host**: smtp.gmail.com
- **Puerto**: 587
- **Usuario**: tu_correo@gmail.com
- **Contraseña**: Tu contraseña de aplicación (genérala aquí: https://myaccount.google.com/apppasswords)
- **Email del Remitente**: tu_correo@gmail.com
- **Nombre**: Sistema Carpetas Licencias

## Estructura de Carpetas

```
carpetaslicencias/
├── config/              # Configuración (BD, SMTP)
├── includes/            # Funciones comunes (auth, email, solicitudes)
├── sql/                 # Scripts de base de datos
├── public/
│   ├── css/            # Estilos
│   └── js/             # JavaScript
├── funcionario/        # Panel de funcionario
│   ├── login.php
│   ├── dashboard.php
│   ├── ver_solicitud.php
│   └── actualizar_solicitud.php
├── administrador/      # Panel de administrador
│   ├── dashboard.php
│   ├── plantillas_correo.php
│   ├── config_smtp.php
│   └── usuarios.php
└── index.php          # Formulario de solicitante
```

## Funcionalidades Principales

### 1. Crear Solicitud (Solicitante)
- Formulario sencillo sin login
- Validación de RUN chileno
- Envío de confirmación por email

### 2. Actualizar Estados (Funcionario)
- Listado de solicitudes pendientes
- Cambio de estado con observaciones
- Registro automático de bitácora
- Notificación al solicitante

### 3. Gestión Administrativa (Administrador)
- CRUD de funcionarios
- Edición de plantillas de correo con variables dinámicas
- Configuración SMTP
- Auditoría de cambios

## Variables Disponibles en Plantillas

- `{nombre}` - Nombre del solicitado
- `{apellido_paterno}` - Apellido paterno
- `{apellido_materno}` - Apellido materno
- `{run}` - RUN del solicitado
- `{municipalidad}` - Municipalidad
- `{estado}` - Estado de la solicitud
- `{observaciones}` - Observaciones
- `{correo_solicitante}` - Email del solicitante

## Seguridad

- Autenticación con sesiones seguras
- Contraseñas hasheadas con BCrypt
- Validación de entradas
- Protección contra SQL Injection (prepared statements)
- Validación de RUN chileno

## Soporte Email

Las siguientes acciones generan notificaciones:

1. **Nueva solicitud** → Se notifica al solicitante y a todos los funcionarios
2. **Cambio de estado** → Se notifica al solicitante y a los funcionarios
3. Notificaciones personalizadas según el estado

## Troubleshooting

### No se envían correos
1. Verifica la configuración SMTP en administración
2. Asegúrate de usar una contraseña de aplicación (no la contraseña de Gmail)
3. Verifica que el puerto sea correcto (587 para TLS, 465 para SSL)

### Error de conexión a BD
1. Verifica que MySQL esté funcionando
2. Comprueba los datos en `config/database.php`
3. Asegúrate de haber creado la base de datos

### RUN inválido
El sistema valida RUN según el algoritmo chileno. Formato: XX.XXX.XXX-X

## Licencia

Este sistema es de código abierto para uso municipal.

## Soporte

Para reportar problemas o sugerencias, contacta al equipo de desarrollo.
