# Sistema IPS CRM - Manual de Instalación y Corrección de Errores

## Descripción
Sistema de gestión de clientes y citas para IPS (Instituciones Prestadoras de Servicios de Salud) con roles de Administrador, Coordinador y Asesor.

## Requisitos del Sistema
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Extensión PDO para PHP
- Extensión mysqli para PHP

## Instalación

### 1. Configuración de Base de Datos
1. Crear una base de datos MySQL llamada `citas2`
2. Ejecutar el script `create_tables.sql` para crear las tablas necesarias
3. Verificar que todas las tablas se crearon correctamente

### 2. Configuración del Sistema
1. Editar el archivo `config.php` con los datos de conexión a la base de datos:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'tu_usuario');
   define('DB_PASS', 'tu_contraseña');
   define('DB_NAME', 'citas2');
   ```

### 3. Permisos de Directorios
Asegurar que los siguientes directorios tengan permisos de escritura:
- `uploads/` (para archivos subidos)
- `assets/` (para archivos estáticos)

### 4. Usuarios por Defecto
El sistema incluye usuarios de prueba con contraseña `password`:
- **Administrador**: usuario `admin`, rol `administrador`
- **Coordinador**: usuario `coordinador`, rol `coordinador`
- **Asesor**: usuario `asesor`, rol `asesor`

## Verificación y Pruebas del Sistema

### Ejecutar Verificación Automática
1. Acceder a `verificar_sistema.php` desde el navegador
2. Revisar todos los puntos de verificación
3. Corregir cualquier error (❌) antes de usar el sistema

### Ejecutar Pruebas Completas
1. Acceder a `pruebas_sistema.php` para verificar funcionalidad completa
2. Este script prueba conexión, autenticación, inserción de datos y exportación

### Generar Datos de Prueba
1. Acceder a `generar_datos_prueba.php` para crear datos de prueba completos
2. Este script crea clientes con múltiples gestiones para probar exportación

### Limpiar Proyecto
1. Acceder a `limpiar_proyecto.php` para eliminar archivos innecesarios
2. Este script identifica y elimina archivos de prueba y diagnóstico

### Verificación Manual
1. **Conexión a Base de Datos**: Verificar que `config.php` tenga los datos correctos
2. **Archivos Requeridos**: Asegurar que todos los archivos estén en su lugar
3. **Permisos**: Verificar permisos de lectura/escritura en directorios críticos
4. **Estructura de BD**: Confirmar que todas las tablas existen

## Corrección de Errores Comunes

### Error: "Fatal error: Uncaught Error: Class 'Database' not found"
**Causa**: Problema con rutas de includes
**Solución**: 
- Verificar que `models/Database.php` existe
- Asegurar que las rutas en `require_once` usen `__DIR__`

### Error: "Table 'historial_gestion' doesn't exist"
**Causa**: Tabla no creada o estructura incorrecta
**Solución**:
1. Ejecutar `create_tables.sql` completo
2. Si la tabla está corrupta, usar `recrear_tabla_historial_gestion.sql`

### Error: "Permission denied" al subir archivos
**Causa**: Permisos insuficientes en directorio `uploads/`
**Solución**:
```bash
chmod 755 uploads/
chown www-data:www-data uploads/  # En sistemas Linux
```

### Error: "PDOException: SQLSTATE[HY000] [2002] Connection refused"
**Causa**: Servidor MySQL no está ejecutándose o datos de conexión incorrectos
**Solución**:
1. Verificar que MySQL esté ejecutándose
2. Revisar datos de conexión en `config.php`
3. Confirmar que el usuario tenga permisos en la base de datos

### Error: "Undefined variable" en vistas
**Causa**: Variables no definidas en controladores
**Solución**:
- Verificar que todos los controladores definan las variables necesarias
- Usar operador de fusión null (`??`) para variables opcionales

## Funcionalidades Mejoradas

### Exportación Completa de Datos
- **Nueva funcionalidad**: Exportación de TODAS las gestiones por cliente
- **Sin columnas vacías**: El archivo CSV solo incluye columnas con datos
- **Información completa**: Incluye email, ciudad, tipo de contacto, resultado, observaciones, próximas acciones
- **Múltiples gestiones**: Si un cliente tiene 15 gestiones, se exportan las 15

### Estructura del Proyecto

```
asignaciones2/
├── assets/
│   ├── css/
│   └── js/
├── controllers/
│   ├── AdminController.php
│   ├── CoordinadorController.php
│   └── AsesorController.php
├── models/
│   ├── Database.php
│   ├── UsuarioModel.php
│   └── ClienteModel.php
├── views/
│   ├── login_form.php
│   ├── admin_dashboard.php
│   ├── coordinador_dashboard.php
│   └── asesor_dashboard.php
├── uploads/
├── config.php
├── index.php
├── create_tables.sql
├── verificar_sistema.php
├── corregir_errores.php
├── pruebas_sistema.php
├── generar_datos_prueba.php
├── limpiar_proyecto.php
└── README.md
```

## Funcionalidades por Rol

### Administrador
- Gestión de usuarios (crear, editar, eliminar)
- Asignación de asesores a coordinadores
- Estadísticas generales del sistema
- Gestión de roles y permisos

### Coordinador
- Carga de archivos CSV con clientes
- Asignación de clientes a asesores
- Transferencia de clientes entre asesores
- **Exportación completa de gestión** (NUEVA)
- Generación de reportes
- Monitoreo de asesores

### Asesor
- Gestión de clientes asignados
- Registro de gestiones y llamadas
- Programación de citas
- Seguimiento de clientes
- Notificaciones de próximas acciones

## Scripts de Utilidad

### verificar_sistema.php
- Verificación completa del sistema
- Comprobación de conexión, archivos y estructura
- Diagnóstico de problemas comunes

### corregir_errores.php
- Corrección automática de errores comunes
- Creación de directorios faltantes
- Verificación de permisos

### pruebas_sistema.php
- Pruebas completas de funcionalidad
- Verificación de autenticación
- Prueba de inserción y consulta de datos

### generar_datos_prueba.php
- Generación de datos de prueba completos
- Creación de clientes con múltiples gestiones
- Datos realistas para probar exportación

### limpiar_proyecto.php
- Identificación de archivos innecesarios
- Eliminación de archivos de prueba y diagnóstico
- Limpieza del proyecto

## Mantenimiento

### Logs del Sistema
- Los errores se registran en `error.log`
- Actividades de usuarios en tabla `logs_actividades`
- Historial de gestiones en tabla `historial_gestion`

### Respaldos
- Realizar respaldos regulares de la base de datos
- Respaldo de archivos subidos en `uploads/`
- Respaldo de configuración en `config.php`

### Actualizaciones
- Mantener PHP y MySQL actualizados
- Revisar logs de errores regularmente
- Monitorear rendimiento de consultas SQL

## Soporte
Para reportar errores o solicitar soporte:
1. Ejecutar `verificar_sistema.php` y adjuntar el resultado
2. Incluir mensajes de error específicos
3. Describir pasos para reproducir el problema
4. Especificar versión de PHP y MySQL

## Notas de Seguridad
- Cambiar contraseñas por defecto en producción
- Configurar HTTPS en servidor web
- Implementar validación de entrada en todos los formularios
- Mantener actualizaciones de seguridad
- Revisar permisos de archivos regularmente
