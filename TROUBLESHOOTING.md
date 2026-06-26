# Guía de Resolución de Problemas

## Problemas Comunes y Soluciones

### 1. Error de Conexión a la Base de Datos

**Síntoma**: "Error de conexión" cuando abres la página

**Causas Posibles**:
- MySQL no está corriendo
- Credenciales incorrectas en config/database.php
- Base de datos no creada

**Solución**:
```
1. Verifica que XAMPP esté corriendo
   - Abre XAMPP Control Panel
   - Asegúrate que Apache y MySQL estén "running"

2. Verifica la configuración BD:
   - Abre config/database.php
   - Comprueba:
     * DB_HOST: localhost ✓
     * DB_USER: root ✓
     * DB_PASS: [tu contraseña o vacío]
     * DB_NAME: carpetas_licencias ✓

3. Crea la base de datos:
   - Accede a phpMyAdmin: http://localhost/phpmyadmin
   - Busca database.sql en la carpeta
   - Ejecuta el script SQL
```

### 2. No se Envían Correos

**Síntoma**: Las solicitudes se crean pero no llegan emails

**Causas Posibles**:
- SMTP no configurado
- Credenciales SMTP incorrectas
- Problemas de firewall

**Solución**:
```bash
1. Verifica configuración SMTP:
   - Admin > Configuración SMTP
   - Todos los campos deben estar llenos
   - Usuario y contraseña deben ser correctos

2. Si usas Gmail:
   - No uses contraseña de Gmail directa
   - Genera contraseña de aplicación:
     https://myaccount.google.com/apppasswords
   - Usa la contraseña de 16 caracteres

3. Prueba la conexión:
   - Verifica que puerto 587 o 465 esté abierto
   - Si no funciona, prueba otro proveedor de email
   - Algunos ISPs bloquean puerto 25

4. Revisa logs de PHP:
   - C:\xampp\apache\logs\error.log
   - Busca mensajes de error de email
```

### 3. "Invalid RUN" para RUN válidos

**Síntoma**: El validador rechaza un RUN que debería ser válido

**Causas Posibles**:
- Formato incorrecto del RUN
- Error en el dígito verificador
- RUN realmente inválido

**Solución**:
```
1. Verifica formato:
   ✓ Correcto: 12.345.678-9
   ✗ Incorrecto: 12345678-9 (sin puntos)
   ✗ Incorrecto: 12.345.6789 (sin guión)

2. Calcula dígito verificador manualmente:
   - Ejemplo RUN: 12.345.678-9
   - Número: 12345678
   - Algoritmo: Múltiplos de 2-7
   - Resultado: 9 (válido)

3. Si el RUN realmente es válido:
   - Prueba en: https://run-rut.cl/validador-rut
   - Si dice es válido pero nuestro sistema lo rechaza,
     reporta error en sistema
```

### 4. Contraseña del Admin Olvidada

**Síntoma**: No puedo acceder a admin@carpetaslicencias.cl

**Solución**:
```
1. Accede a phpMyAdmin:
   - http://localhost/phpmyadmin
   - Usuario: root
   - Sin contraseña (o tu contraseña MySQL)

2. Busca la tabla 'usuarios':
   - Abre base de datos: carpetas_licencias
   - Busca tabla: usuarios

3. Edita el usuario admin:
   - Haz clic en el ícono de edición
   - Busca el campo 'contraseña'
   - Genera una contraseña con bcrypt:
     • Online: https://bcrypt-generator.com/
     • Genera hash para "admin123"
   - Actualiza el registro

4. Inicia sesión:
   - Correo: admin@carpetaslicencias.cl
   - Contraseña: admin123 (o la nueva que creaste)
```

### 5. Página en Blanco o Error 500

**Síntoma**: La página no carga, solo ve blanco o error 500

**Causas Posibles**:
- Error en PHP
- Permisos de carpetas incorrectos
- Extensiones PHP faltantes

**Solución**:
```
1. Activa visualización de errores:
   - Abre config/database.php
   - Agrega al inicio:
     error_reporting(E_ALL);
     ini_set('display_errors', 1);

2. Revisa logs de errores:
   - C:\xampp\apache\logs\error.log
   - C:\xampp\php\logs\php_errors.log
   - Busca mensajes recientes

3. Verifica permisos:
   - Carpeta carpetaslicencias debe tener permisos 755
   - En Windows, click derecho > Propiedades > Seguridad
   - Asegúrate que IIS_IUSRS tenga permisos

4. Verifica extensiones PHP:
   - php.ini debe tener habilitadas:
     * extension=mysqli
     * extension=PDO
```

### 6. Tabla de Solicitudes Vacía en Panel

**Síntoma**: El funcionario no ve las solicitudes creadas

**Causas Posibles**:
- Las solicitudes están en otro estado
- Problema de base de datos
- No hay solicitudes aún

**Solución**:
```
1. Verifica que existan solicitudes:
   - Admin > Gestionar Solicitudes
   - Busca por estado "Pendiente"
   - Si ves solicitudes, filtra por estado

2. Limpia filtros:
   - Haz clic en botón "Limpiar"
   - Deberías ver todas las solicitudes

3. Verifica en phpMyAdmin:
   - http://localhost/phpmyadmin
   - Tabla: solicitudes
   - SELECT * FROM solicitudes;
   - Si está vacía, no hay solicitudes aún

4. Prueba crear solicitud:
   - Abre index.php
   - Crea una solicitud de prueba
   - Recarga la página de funcionario
```

### 7. Correos de Prueba no Llegan

**Síntoma**: Envío local funciona pero correos no llegan

**Causas Posibles**:
- El email es enviado a la papelera spam
- Configuración de SPF/DKIM incompleta
- El proveedor bloquea correos

**Solución**:
```
1. Revisa carpeta de spam:
   - Si es Gmail, mira carpeta "Spam" o "Todos"
   - Marca como "No es spam" para futuras referencias

2. Configura SPF/DKIM:
   - Si es dominios propios, necesitas:
     * Registro SPF en DNS
     * Registro DKIM en DNS
   - Consulta documentación de tu proveedor email

3. Prueba con otro proveedor:
   - Si SMTP de Gmail no funciona
   - Prueba con: Outlook, Sendgrid, mailgun
   - Verifica que credenciales sean correctas

4. Revisa headers del email:
   - En algunos clientes hay opción "Ver código original"
   - Busca encabezados de SPF, DKIM
   - Si faltan, agrega registros DNS
```

### 8. Sesión Se Cierra Constantemente

**Síntoma**: Me desconecta del sistema frecuentemente

**Causas Posibles**:
- PHP sessions no funciona
- Carpeta temp llena
- Configuración de timeout muy baja

**Solución**:
```
1. Verifica carpeta de sesiones:
   - C:\xampp\tmp debe existir
   - Si no existe, crearla
   - Permisos: 777

2. Limpia sesiones antiguas:
   - Borra archivos en C:\xampp\tmp
   - Especialmente si son muy antiguos

3. Extender timeout de sesión:
   - Abre php.ini
   - Busca: session.gc_maxlifetime
   - Cambia a: 3600 (1 hora)
   - Reinicia Apache

4. Verifica cookies:
   - Asegúrate que cookies estén habilitadas
   - En navegador: Configuración > Privacidad > Cookies
```

### 9. Archivos CSS no Cargan (Página sin estilos)

**Síntoma**: La página se ve sin formato, solo texto

**Causas Posibles**:
- Ruta de CSS incorrecta
- Problema de permisos
- Navegador cache

**Solución**:
```
1. Limpia cache del navegador:
   - Chrome: Ctrl+Shift+Del
   - Firefox: Ctrl+Shift+Del
   - Edge: Ctrl+Shift+Del
   - Elimina "Archivos en caché"

2. Verifica existencia de archivo:
   - El archivo debe estar en: public/css/style.css
   - En explorador: C:\xampp\htdocs\carpetaslicencias\public\css\style.css

3. Verifica HTML:
   - Abre el navegador y visualiza código fuente (F12)
   - Busca: <link rel="stylesheet"
   - Verifica que la ruta sea correcta

4. Fuerza recarga:
   - Presiona Ctrl+F5 (recarga forzada)
   - Cierra navegador completamente
   - Reabre la página
```

### 10. Error de Permisos al Crear Archivos

**Síntoma**: "Permission denied" o error similar

**Causas Posibles**:
- Permisos de carpeta insuficientes
- Ejecutando como usuario limitado
- Antivirus bloqueando

**Solución**:
```
1. Verifica permisos de carpetas:
   - Click derecho > Propiedades > Seguridad
   - Editar permisos
   - Asegurar "Control Total" para IUSR

2. Ejecuta XAMPP como administrador:
   - Click derecho XAMPP > "Ejecutar como administrador"
   - Esto generalmente resuelve problemas

3. Desactiva antivirus temporalmente:
   - Algunos antivirus bloquean escritura en carpetas
   - Agrega carpeta a excepciones:
     C:\xampp\htdocs\carpetaslicencias
```

## Comandos Útiles para Troubleshooting

```bash
# Ver si MySQL está corriendo
netstat -an | findstr 3306

# Reiniciar MySQL
net stop MySQL80
net start MySQL80

# Ver logs de Apache
type C:\xampp\apache\logs\error.log

# Ver logs de PHP
type C:\xampp\php\logs\php_errors.log

# Limpiar sesiones
del C:\xampp\tmp\sess_*

# Verificar conexión SQL
mysql -u root -p carpetas_licencias
SELECT COUNT(*) FROM solicitudes;
```

## Contacto de Soporte

Si ninguna solución funciona:

1. Verifica el archivo error.log más reciente
2. Copia el mensaje de error exacto
3. Incluye información:
   - Versión XAMPP
   - Sistema operativo
   - Paso donde ocurre el error
4. Contacta con soporte

---

**Última actualización**: Abril 2024
**Versión**: 1.0

