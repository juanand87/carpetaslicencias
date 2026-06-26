# Guía de Instalación Rápida

## Paso 1: Preparar la Base de Datos

### Opción A: Con phpMyAdmin (Recomendado para principiantes)

1. Abre tu navegador y ve a http://localhost/phpmyadmin
2. Haz clic en "Nueva" o "Nueva Base de Datos"
3. Nombre de la base de datos: `carpetas_licencias`
4. Charset: `utf8mb4`
5. Haz clic en "Crear"
6. Abre la pestaña SQL
7. Copia TODO el contenido del archivo `sql/database.sql`
8. Pega en el área SQL de phpMyAdmin
9. Haz clic en "Continuar"

### Opción B: Por línea de comandos

```bash
# Abre el CMD y navega a la carpeta de XAMPP
cd C:\xampp\mysql\bin

# Conecta a MySQL (sin contraseña si es la primera vez)
mysql -u root

# Copia el comando para crear la BD desde el archivo SQL
mysql -u root < "ruta\a\carpetaslicencias\sql\database.sql"
```

## Paso 2: Verificar Conexión a Base de Datos

1. Abre el archivo `config/database.php`
2. Verifica que los datos sean correctos:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Si MySQL tiene contraseña, ponla aquí
   define('DB_NAME', 'carpetas_licencias');
   ```

## Paso 3: Acceder al Programa

### Para Solicitantes (Público)
```
http://localhost/carpetaslicencias/
```

### Para Funcionarios
```
http://localhost/carpetaslicencias/funcionario/login.php
```
- Correo: (Creada por administrador)
- Contraseña: (Creada por administrador)

### Para Administrador
```
http://localhost/carpetaslicencias/administrador/dashboard.php
```
- Correo: admin@carpetaslicencias.cl
- Contraseña: admin123

## Paso 4: Configurar SMTP para Envío de Correos

1. Inicia sesión como Administrador
2. Ve a "Configuración SMTP"
3. Complete los campos según su servidor de correo:

### Si usas Gmail:

1. Generá una contraseña de aplicación:
   - Ve a https://myaccount.google.com/apppasswords
   - Selecciona "Mail" y "Windows"
   - Se generá una contraseña de 16 caracteres
   
2. Completa el formulario:
   - Host SMTP: `smtp.gmail.com`
   - Puerto: `587`
   - Usuario SMTP: `tu_correo@gmail.com`
   - Contraseña SMTP: `La generada en el paso anterior`
   - Email del Remitente: `tu_correo@gmail.com`
   - Nombre del Remitente: `Sistema Carpetas Licencias`

## Paso 5: Crear Funcionarios (Como Administrador)

1. Ve a "Administración" → "Gestionar Funcionarios"
2. Haz clic en "Crear Funcionario"
3. Completa los datos:
   - Nombre
   - Apellido Paterno
   - Correo (será el usuario para login)
   - Contraseña (mínimo 6 caracteres)
   - Rol (Funcionario o Administrador)
4. Haz clic en "Crear Funcionario"

## Paso 6: Personalizar Plantillas de Correo

1. Ve a "Administración" → "Plantillas de Correo"
2. Edita cada plantilla según lo necesite
3. Puede usar variables como {nombre}, {run}, {estado}, etc.
4. Haz clic en "Guardar"

## Prueba del Sistema

### 1. Crear una Solicitud
1. Ve a http://localhost/carpetaslicencias/
2. Completa el formulario con datos de prueba:
   - Nombre: Juan
   - Apellido Paterno: Prueba
   - RUN: 12345678-9
   - Correo: tu_email@test.com
   - Municipalidad: (Cualquiera)
3. Haz clic en "Enviar Solicitud"
4. Deberías recibir un correo de confirmación

### 2. Actualizar Estado (Como Funcionario)
1. Ve a http://localhost/carpetaslicencias/funcionario/login.php
2. Ingresa con las credenciales del funcionario creado
3. Verás la solicitud creada
4. Haz clic en "Actualizar" junto a la solicitud
5. Cambia el estado y agrega observaciones
6. Haz clic en "Guardar Cambios"

## Problema: No se Envían Correos

### Verificar:
1. ¿Está configurado SMTP?
2. ¿El correo del administrador es correcto?
3. ¿Es una contraseña de aplicación (si es Gmail)?
4. ¿El puerto es correcto (587)?

### Solucions:
1. Prueba con un cliente SMTP diferente primero
2. Verifica que PHP esté configurado con soporte para Sendmail o SMTP
3. Revisa los registro de errores de PHP

## Estructura de Carpetas

```
C:\xampp\htdocs\carpetaslicencias\
├── index.php ......................... Formulario de solicitante
├── README.md ......................... Documentación
├── INSTALACION.md .................... Esta guía
├── config/
│   ├── database.php .................. Conexión a BD
│   └── smtp.php ...................... Configuración SMTP
├── includes/
│   ├── auth.php ...................... Autenticación
│   ├── email.php ..................... Envío de correos
│   └── solicitudes.php ............... Gestión de solicitudes
├── sql/
│   └── database.sql .................. Script de BD
├── public/
│   ├── css/
│   │   └── style.css ................. Estilos
│   └── js/
│       └── (Aquí va JavaScript si es necesario)
├── funcionario/
│   ├── login.php ..................... Login de funcionarios
│   ├── dashboard.php ................. Panel principal
│   ├── ver_solicitud.php ............. Ver detalles
│   └── actualizar_solicitud.php ...... Actualizar estado
└── administrador/
    ├── dashboard.php ................. Panel principal
    ├── plantillas_correo.php ......... Editar plantillas
    ├── config_smtp.php ............... Configurar SMTP
    └── usuarios.php .................. Gestionar funcionarios
```

## Preguntas Frecuentes

**P: ¿Cómo cambio la contraseña del admin?**
R: Por ahora, accede a phpMyAdmin y actualiza directamente en la tabla `usuarios`. Usa bcrypt para hash.

**P: ¿Puedo tener múltiples administradores?**
R: Sí, crea nuevos usuarios con rol "administrador" en la sección de gestión de funcionarios.

**P: ¿Qué pasa si olvido la contraseña?**
R: Accede a phpMyAdmin y restablece la contraseña en la tabla `usuarios`.

**P: ¿Cómo hago backup de la BD?**
R: En phpMyAdmin, selecciona la BD y haz clic en "Exportar".

---

¿Necesita ayuda? Revisa el README.md para más información.
