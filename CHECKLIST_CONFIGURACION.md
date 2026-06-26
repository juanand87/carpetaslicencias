# Checklist de Configuración Inicial

Sigue esta lista para configurar el sistema correctamente.

## 📋 Fase 1: Instalación Base

- [ ] **Carpeta instalada**: `C:\xampp\htdocs\carpetaslicencias`
- [ ] **XAMPP corriendo**: Apache + MySQL en servicios
- [ ] **Archivos descargados**: Todos los archivos están en la carpeta

## 🗄️ Fase 2: Base de Datos

### Crear Base de Datos
- [ ] Abrir phpMyAdmin (http://localhost/phpmyadmin)
- [ ] Crear nueva base de datos: `carpetas_licencias`
- [ ] Charset: `utf8mb4`
- [ ] Ejecutar el script `sql/database.sql`

### Verificar Creación
- [ ] La BD fue creada
- [ ] Tablas creadas correctamente (~9 tablas)
- [ ] Datos iniciales insertados

## ⚙️ Fase 3: Configuración PHP

### Config Base de Datos
- [ ] Abrir: `config/database.php`
- [ ] Verificar:
  ```php
  DB_HOST = localhost
  DB_USER = root
  DB_PASS = [tu_contraseña]
  DB_NAME = carpetas_licencias
  ```

### Config SMTP
- [ ] Abrir: `config/smtp.php`
- [ ] Notas: Configuraremos desde el admin después

## 🌐 Fase 4: Acceso al Sistema

### Iniciar Sesión
- [ ] Ir a: `http://localhost/carpetaslicencias/administrador/dashboard.php`
- [ ] Correo: `admin@carpetaslicencias.cl`
- [ ] Contraseña: `admin123`
- [ ] Cambiar contraseña admin (recomendado)

## 📧 Fase 5: Configuración de Email

### Preparar Email (Gmail)
- [ ] Acceder a: https://myaccount.google.com/apppasswords
- [ ] Seleccionar: Mail + Windows
- [ ] Copiar contraseña de 16 caracteres generada

### Configurar en Sistema
- [ ] Ir a: Administración > Configuración SMTP
- [ ] Llenar formulario:
  - Host: `smtp.gmail.com`
  - Puerto: `587`
  - Usuario: tu_email@gmail.com
  - Contraseña: [la de 16 caracteres]
  - Email Remitente: tu_email@gmail.com
  - Nombre: Sistema Carpetas Licencias
- [ ] Guardar

### Verificar
- [ ] Guardar sin errores
- [ ] Revisar que aparezca "guardado correctamente"

## 👥 Fase 6: Crear Funcionarios

### Crear Primer Funcionario
- [ ] Ir a: Administración > Gestionar Funcionarios
- [ ] Crear:
  - Nombre: Juan
  - Apellido: Funcionario
  - Correo: juan@prueba.com
  - Contraseña: 123456
  - Rol: Funcionario
- [ ] Guardar

### Crear Admin Adicional (Opcional)
- [ ] Repetir proceso con Rol = Administrador

## 📋 Fase 7: Personalizar Plantillas

### Revisar Plantillas
- [ ] Ir a: Administración > Plantillas de Correo
- [ ] Ver las 4 plantillas:
  - Nueva solicitud
  - Estado cambio
  - Solicitud cargada
  - Solicitud rechazada

### Personalizar (Opcional)
- [ ] Editar asuntos según necesidad
- [ ] Cambiar cuerpo de mensajes si lo desea
- [ ] Conservar variables entre {llaves}
- [ ] Guardar cambios

## 🧪 Fase 8: Prueba Completa

### Crear Solicitud
- [ ] Ir a: Página principal (index.php)
- [ ] Llenar formulario:
  - Nombre: TestUser
  - Apellido P: Test
  - RUN: 12345678-9
  - Email: tu_email@test.com
  - Municipalidad: Arica
- [ ] Enviar
- [ ] Verificar mensaje "Solicitud creada"

### Revisar Email
- [ ] Revisar correo (puede tardar 1-2 minutos)
- [ ] Debería recibir email de "Nueva Solicitud"

### Login Funcionario
- [ ] Ir a: Funcionario Login
- [ ] Email: juan@prueba.com
- [ ] Contraseña: 123456
- [ ] Ver la solicitud creada

### Actualizar Estado
- [ ] Haz clic en "Actualizar"
- [ ] Cambiar estado a "Cargada"
- [ ] Escribir observación simple
- [ ] Guardar
- [ ] Ver cambio en bitácora

### Verificar Email Notificación
- [ ] Revisar email (debería llegar notificación)
- [ ] Confirmar que contiene los datos correctos

## 📱 Fase 9: Verificación Final

### Página de Información
- [ ] Ir a: `http://localhost/carpetaslicencias/info.php`
- [ ] Verificar estado del sistema
- [ ] Todos los accesos funcionan
- [ ] BD conectada

### Formulario Público
- [ ] Puede crear solicitudes
- [ ] Validación de RUN funciona
- [ ] Email se envía correctamente

### Panel Funcionario
- [ ] Puede loguear
- [ ] Ve solicitudes
- [ ] Puede cambiar estados
- [ ] Bitácora se registra

### Administración
- [ ] Puede acceder como admin
- [ ] SMTP configurado
- [ ] Plantillas disponibles
- [ ] Funcionarios creados

## ⚠️ Fase 10: Checklist de Seguridad

- [ ] Cambiar contraseña del admin
- [ ] Remover usuario de prueba
- [ ] Verificar permisos de carpetas
- [ ] Hacer backups regularmente
- [ ] Desactivar usuarios innecesarios

## 📝 Notas Adicionales

```
Problemas Encontrados:
_________________________________
_________________________________
_________________________________

Configuraciones Personalizadas:
_________________________________
_________________________________
_________________________________

Contacto de Soporte:
_________________________________
_________________________________
```

## ✅ Firma de Completación

Completado por: ___________________

Fecha: ___________________

Observaciones:
_________________________________
_________________________________

---

**Continúa a REFERENCIA_RAPIDA.md para uso del sistema**

