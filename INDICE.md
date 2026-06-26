# 📑 Índice General del Sistema

## 🎯 Punto de Entrada Rápido

Comienza aquí si es tu primer acceso:

1. **Página de Inicio**: [inicio.html](inicio.html)
2. **Información del Sistema**: [info.php](info.php)
3. **Sitio Principal (Solicitantes)**: [index.php](index.php)

---

## 📚 Documentación

### Para Nuevos Usuarios
- **[INSTALACION.md](INSTALACION.md)** ⭐ **LEER PRIMERO**
  - Guía paso a paso de instalación
  - Configuración de base de datos
  - Pruebas iniciales

- **[CHECKLIST_CONFIGURACION.md](CHECKLIST_CONFIGURACION.md)**
  - Lista de verificación por fases
  - Checklist interactivo
  - Firmas de completación

### Para Usuarios del Sistema
- **[README.md](README.md)** - Documentación completa
  - Características de cada perfil
  - Requisitos del sistema
  - Instrucciones de instalación
  - Seguridad implementada

- **[REFERENCIA_RAPIDA.md](REFERENCIA_RAPIDA.md)** - Guía rápida
  - Accesos directos (URLs)
  - Usuarios por defecto
  - Funciones principales
  - Troubleshooting rápido

### Para Desarrolladores
- **[API_EJEMPLOS.md](API_EJEMPLOS.md)**
  - Ejemplos de código PHP
  - Estructura de datos
  - Respuestas de funciones
  - Queries SQL útiles

### Resolución de Problemas
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)**
  - 10 problemas comunes
  - Soluciones detalladas
  - Comandos útiles
  - Contacto de soporte

---

## 🗂️ Estructura de Carpetas

```
carpetaslicencias/
│
├── 📄 ARCHIVOS RAÍZ
│   ├── index.php ..................... Formulario de solicitante (PUNTO DE ENTRADA PÚBLICO)
│   ├── info.php ...................... Información del sistema
│   ├── inicio.html ................... Página de bienvenida rápida
│   └── .gitignore .................... Configuración Git
│
├── 📁 config/ - CONFIGURACIÓN
│   ├── database.php .................. ⚠️ EDITAR: Conexión a BD
│   └── smtp.php ...................... ⚠️ EDITAR: Configuración email
│
├── 📁 includes/ - FUNCIONES DEL SISTEMA
│   ├── auth.php ...................... Autenticación y sesiones
│   ├── email.php ..................... Envío de correos
│   ├── solicitudes.php ............... Gestión de solicitudes
│   └── constants.php ................. Constantes y variables
│
├── 📁 public/ - RECURSOS PÚBLICOS
│   ├── css/
│   │   └── style.css ................. Estilos de todo el sitio
│   └── js/
│       └── validaciones.js ........... Validaciones JavaScript
│
├── 📁 funcionario/ - PANEL DE FUNCIONARIO
│   ├── login.php ..................... Página de login
│   ├── dashboard.php ................. Panel principal
│   ├── ver_solicitud.php ............. Ver detalles
│   ├── actualizar_solicitud.php ...... Cambiar estado
│   └── logout.php .................... Cerrar sesión
│
├── 📁 administrador/ - PANEL DE ADMINISTRADOR
│   ├── dashboard.php ................. Panel principal
│   ├── plantillas_correo.php ......... Editar plantillas email
│   ├── config_smtp.php ............... Configurar servidor SMTP
│   └── usuarios.php .................. Gestionar funcionarios
│
├── 📁 sql/ - BASE DE DATOS
│   └── database.sql .................. Script completo de BD
│
├── 📁 src/ - EXTRAS (Vacío para futuro uso)
│
└── 📚 DOCUMENTACIÓN
    ├── README.md ..................... Documentación principal
    ├── INSTALACION.md ................ Guía de instalación
    ├── CHECKLIST_CONFIGURACION.md .... Lista de verificación
    ├── REFERENCIA_RAPIDA.md .......... Guía rápida
    ├── TROUBLESHOOTING.md ............ Resolución de problemas
    ├── API_EJEMPLOS.md ............... Ejemplos para desarrolladores
    ├── INDICE.md ..................... Este archivo
    └── .gitignore .................... Archivos a ignorar en Git
```

---

## 🚀 Guía de Inicio Rápido (5 minutos)

### Opción A: Completamente Nuevo
1. Abre [INSTALACION.md](INSTALACION.md)
2. Sigue pasos de instalación
3. Vuelve aquí cuando listes

### Opción B: Tienes Linux/XAMPP instalado
1. Ejecuta `sql/database.sql`
2. Edita `config/database.php`
3. Abre [inicio.html](inicio.html) en el navegador
4. ¡Listo!

### Opción C: Solo mirar funcionalidades
1. Abre [index.php](index.php) - Para solicitantes
2. Abre `funcionario/login.php` - Para funcionarios
3. Usuario: `admin@carpetaslicencias.cl` / Contraseña: `admin123`

---

## 🔑 Usuarios por Defecto

| Rol | Correo | Contraseña | Acción |
|-----|--------|-----------|--------|
| Admin | admin@carpetaslicencias.cl | admin123 | **Cambiar después** ⚠️ |
| Funcionario | Crear desde admin | Crear desde admin | Crear en administración |

---

## 📱 URLs Principales

| Funcionalidad | URL |
|---------------|-----|
| Solicitar carpeta | `http://localhost/carpetaslicencias/` |
| Login funcionario | `http://localhost/carpetaslicencias/funcionario/login.php` |
| Panel funcionario | `http://localhost/carpetaslicencias/funcionario/dashboard.php` |
| Dashboard admin | `http://localhost/carpetaslicencias/administrador/dashboard.php` |
| Info del sistema | `http://localhost/carpetaslicencias/info.php` |
| Inicio rápido | `http://localhost/carpetaslicencias/inicio.html` |

---

## 🎨 Estados de Solicitudes

```
🟡 Pendiente                    → Sin procesar aún
🟢 Cargada                      → Completada exitosamente
🔵 Cargada con Observaciones    → Completada con notas
🔴 No Encontrada               → Carpeta no existe
⚫ Rechazada                    → No fue aceptada
```

---

## ✨ Características Principales

✅ Formulario público sin login
✅ Validación automática de RUN chileno
✅ Notificaciones por email (configurable)
✅ Cambios de estado con bitácora completa
✅ Plantillas de correo personalizables
✅ Gestión de funcionarios
✅ Seguridad con autenticación y hashing
✅ 346 municipios de Chile incluidos
✅ Interface responsiva (móvil compatible)
✅ Documentación completa en español

---

## 🔒 Seguridad

- Autenticación por sesiones seguras
- Contraseñas hasheadas con BCrypt
- Validación de entradas (SQL Injection protection)
- Prepared statements en todas las queries
- Validación de RUN chileno

---

## 📊 Base de Datos

**9 Tablas principales:**
1. usuarios - Funcionarios y administradores
2. solicitudes - Solicitudes de carpetas
3. municipalidades - 346 comunas de Chile
4. bitacora_cambios - Historial de cambios
5. plantillas_correo - Templates de email
6. config_smtp - Configuración del servidor
7. configuracion - Configuración general
8. (Y 2 más para uso futuro)

---

## 🛠️ Mantenimiento

### Backup de Base de Datos
```bash
mysqldump -u root carpetas_licencias > backup.sql
```

### Restaurar Backup
```bash
mysql -u root < backup.sql
```

### Limpiar Sesiones Antiguas
```bash
del C:\xampp\tmp\sess_*
```

---

## 📞 Soporte y Ayuda

**Problema encontrado?** Consulta:
1. [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Soluciones rápidas
2. [README.md](README.md) - Documentación completa
3. [REFERENCIA_RAPIDA.md](REFERENCIA_RAPIDA.md) - Guía rápida

**Algo no funciona después de seguir guías?**
- Revisa logs: `C:\xampp\apache\logs\error.log`
- Habilita debug en `config/database.php`
- Contacta al equipo de desarrollo

---

## 📋 Lista de Verificación Rápida

Antes de comenzar:
- [ ] XAMPP instalado y corriendo
- [ ] Base de datos creada
- [ ] Configuración de BD completada
- [ ] SMTP configurado (opcional pero recomendado)
- [ ] Funcionarios creados
- [ ] Página de inicio accesible

---

## 🎓 Próximos Pasos

1. **Primero**: Lee [INSTALACION.md](INSTALACION.md)
2. **Luego**: Ejecuta [CHECKLIST_CONFIGURACION.md](CHECKLIST_CONFIGURACION.md)
3. **Después**: Consulta [REFERENCIA_RAPIDA.md](REFERENCIA_RAPIDA.md) para uso diario
4. **Si hay dudas**: [TROUBLESHOOTING.md](TROUBLESHOOTING.md)

---

## 📦 Versión y Información

- **Versión**: 1.0
- **Fecha**: Abril 2024
- **Tipo**: Sistema Municipal Open Source
- **Lenguaje**: PHP 7.4+ (Vanilla, sin frameworks)
- **Base de Datos**: MySQL 5.7+
- **Licencia**: Uso Municipal

---

**¿Lista para comenzar? → Abre [inicio.html](inicio.html) ahora 🚀**

