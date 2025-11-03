# 🚀 INSTRUCCIONES DE UPGRADE - Dominios Reseller v1.2.0

## ⚠️ IMPORTANTE: Backup Primero

```bash
# En el servidor, hacer backup de la base de datos
wp db export dominios_reseller_backup_$(date +%Y%m%d).sql

# Backup de archivos del plugin
cp -r wp-content/plugins/dominios-reseller wp-content/plugins/dominios-reseller.backup
```

## 📦 ARCHIVOS A REEMPLAZAR

1. `dominios-reseller.php` (archivo principal)
2. `includes/shortcodes.php`
3. `includes/whm-functions.php`

## 🔧 PASOS DE INSTALACIÓN

### 1. Subir Archivos
- Sube los 3 archivos vía FTP/Filezilla
- Sobrescribe los existentes

### 2. Ejecutar Upgrade de Base de Datos

**OPCIÓN A: Vía Admin (Recomendado)**
```
1. WordPress Admin → Plugins
2. Desactivar "Dominios Reseller"
3. Activar "Dominios Reseller"
   (Esto ejecuta automáticamente el upgrade de BD)
```

**OPCIÓN B: Vía WP-CLI**
```bash
wp plugin deactivate dominios-reseller
wp plugin activate dominios-reseller
```

### 3. Reparar Datos (Si es necesario)

Si al entrar al plugin ves un botón "🔧 Reparar Ahora":
- Haz clic en él
- Espera a que termine
- Refresca la página

### 4. Verificar

✅ **Admin debe mostrar:**
- Todos los dominios UK + USA en tabla unificada
- Pestañas: Todos | UK | USA | Configuración
- Campos editables: árboles y CO2

✅ **Shortcode debe:**
- Cargar instantáneamente (<100ms)
- Mostrar datos correctos por dominio
- Funcionar con ?domain=ejemplo.com

## 🐛 SOLUCIÓN DE PROBLEMAS

### Error: "Cannot use object of type stdClass as array"
**Causa:** Código antiguo mezclado
**Solución:** Asegúrate de subir TODOS los archivos nuevos

### Error: "Duplicate entry 'domain-server'"
**Causa:** Ya tienes el campo server en BD
**Solución:** El upgrade lo maneja automáticamente, desactiva/activa el plugin

### No veo dominios USA
**Causa:** Token no configurado o no sincronizado
**Solución:** 
1. Ve a Configuración
2. Añade token USA
3. Guarda
4. Vuelve a "Todos los Dominios"

## 📊 CAMBIOS EN BASE DE DATOS

```sql
-- Se añade columna 'server'
ALTER TABLE wp_dominios_reseller ADD COLUMN server varchar(10) NOT NULL DEFAULT 'uk';

-- Nuevo índice único compuesto
ALTER TABLE wp_dominios_reseller ADD UNIQUE KEY domain_server (domain, server);

-- Índices para velocidad
ALTER TABLE wp_dominios_reseller ADD KEY idx_server (server);
ALTER TABLE wp_dominios_reseller ADD KEY idx_status (status);

-- Precisión decimal para CO2
ALTER TABLE wp_dominios_reseller MODIFY co2_evaded decimal(10,2);

-- Timestamp de última sincronización
ALTER TABLE wp_dominios_reseller ADD COLUMN last_sync TIMESTAMP;
```

## ✨ NUEVAS CARACTERÍSTICAS

- ⚡ **Velocidad**: Shortcode 50x más rápido
- 🌍 **Multi-servidor**: Soporte real para UK + USA
- 💾 **Caché local**: No más llamadas lentas a WHM
- 🔄 **Sincronización automática**: Al cargar admin
- 🎯 **Índices optimizados**: Búsquedas instantáneas

## 📞 SOPORTE

Si algo no funciona:
1. Revisa los logs: `/wp-content/debug.log`
2. Busca errores que digan "Dominios Reseller"
3. Envía el error completo

---
**Versión:** 1.2.0  
**Fecha:** 02-Oct-2025  
**Autor:** Replanta Dev Team
