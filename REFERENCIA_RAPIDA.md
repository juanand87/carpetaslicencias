# Referencia Rápida - Sistema de Solicitudes

## Accesos Directos

### Para Solicitantes (Público)
```
http://localhost/carpetaslicencias/
```
- Crear nuevas solicitudes
- Sin registración requerida

### Para Funcionarios
```
http://localhost/carpetaslicencias/funcionario/login.php
```
- Correo y contraseña
- Gestionar solicitudes
- Ver estado y bitácora

### Para Administradores
```
http://localhost/carpetaslicencias/administrador/dashboard.php
```
- Acceso directo al panel
- (Iniciar sesión como funcionario primero si es necesario)

### Información del Sistema
```
http://localhost/carpetaslicencias/info.php
```
- Estado del sistema
- Accesos rápidos
- Documentación

## Usuarios por Defecto

| Rol | Correo | Contraseña | Estado |
|-----|--------|-----------|--------|
| Admin | admin@carpetaslicencias.cl | admin123 | Cambiar después |
| Funcionario | Crear desde admin | Crear desde admin | Personalizado |

## Estados de Solicitudes

| Estado | Descripción |
|--------|-------------|
| 🟡 Pendiente | Esperando ser procesada |
| 🟢 Cargada | Completada exitosamente |
| 🔵 Con Observaciones | Cargada pero con notas |
| 🔴 No Encontrada | Carpeta no existe |
| ⚫ Rechazada | Solicitud rechazada |

## Archivos Importantes

```
carpetaslicencias/
├── config/
│   ├── database.php ............ EDITAR: Conexión BD
│   └── smtp.php ............... EDITAR: Email (opcional)
├── sql/
│   └── database.sql ........... SQL para crear BD
├── includes/
│   ├── auth.php ............... Login/Autenticación
│   ├── email.php .............. Envío de correos
│   ├── solicitudes.php ........ Gestión de solicitudes
│   └── constants.php .......... Constantes del sistema
├── public/css/
│   └── style.css .............. Estilos del sitio
├── README.md .................. Documentación principal
├── INSTALACION.md ............. Guía de instalación
├── API_EJEMPLOS.md ............ Ejemplos de código
└── info.php ................... Página de información
```

## Funciones Principales - Código

### Crear Solicitud
```php
require_once 'includes/solicitudes.php';
$resultado = crear_solicitud($conn, [
    'nombre' => 'Juan',
    'apellido_paterno' => 'Pérez',
    'run' => '12.345.678-9',
    'correo_solicitante' => 'juan@test.com',
    'municipalidad_id' => 1
]);
```

### Cambiar Estado
```php
$resultado = cambiar_estado_solicitud(
    $conn,           // Conexión BD
    1,               // ID Solicitud
    'Cargada',       // Nuevo estado
    'Observaciones', // Observaciones
    1                // ID Usuario
);
```

### Obtener Solicitudes
```php
$solicitudes = obtener_solicitudes($conn, [
    'estado' => 'Pendiente',
    'busqueda' => '12345678'
]);
```

### Validar RUN
```php
if (validar_run('12.345.678-9')) {
    echo "RUN válido";
}
```

## Pase de Trabajo Recomendado

### Primer Día
1. [ ] Leer INSTALACION.md
2. [ ] Instalar BD
3. [ ] Configurar email
4. [ ] Crear funcionarios

### Primer Salto Día
1. [ ] Probar formulario de solicitud
2. [ ] Recibir email de confirmación
3. [ ] Login como funcionario
4. [ ] Actualizar estado de solicitud
5. [ ] Verificar email de notificación

### Personalizaciones
1. [ ] Editar plantillas de correo
2. [ ] Cambiar logo y colores (CSS)
3. [ ] Agregar más municipios si es necesario
4. [ ] Crear más funcionarios

## Atajos de Teclado

| Acción | Atajo |
|--------|-------|
| Volver | Alt + ← |
| Crear | Ctrl + N |
| Guardar | Ctrl + S |
| Buscar | Ctrl + F |

## Validaciones

### RUN Chileno
Formato válido: `12.345.678-9`
- Primeros 8 dígitos: número
- Último: dígito o K
- Valida algoritmo chileno

### Email
Debe ser válido (ej: usuario@ejemplo.com)

### Contraseña
- Mínimo 6 caracteres
- Se almacena con hash BCrypt

## Límites del Sistema

- No hay límite de solicitudes
- No hay límite de funcionarios
- Máximo de email: 1000 caracteres
- Máximo observaciones: 65535 caracteres

## Problemas Comunes

### ❌ Error: "Error de conexión"
**Solución**: Verifica config/database.php y que MySQL esté corriendo

### ❌ Correos no llegan
**Solución**: Configura SMTP en Administración > Configuración SMTP

### ❌ RUN inválido
**Solución**: Usa formato 12.345.678-9, el sistema valida el dígito verificador

### ❌ No puedo loguearme
**Solución**: Verifica correo y contraseña, verifica que no esté desactivado

### ❌ La página se ve rota
**Solución**: Limpia caché (Ctrl+Shift+Del), verifica que CSS cargue

## Mentenimiento

### Backup de Base de Datos
```bash
mysqldump -u root carpetas_licencias > backup.sql
```

### Restaurar Backup
```bash
mysql -u root < backup.sql
```

### Ver Logs de Errores
- PHP: C:\xampp\apache\logs\error.log
- MySQL: C:\xampp\mysql\data\*.err

## Soporte

- 📧 Email: soporte@municipio.cl
- 📞 Teléfono: +56 (contacto local)
- 💬 Chat: (si está disponible)
- 📚 Documentación: README.md

---

**Última actualización**: Abril 2024
**Versión**: 1.0
**Desarrollador**: Sistema Municipal

