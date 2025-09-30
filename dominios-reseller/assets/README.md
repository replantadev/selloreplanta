# Dominios Reseller Assets

## Estructura de Archivos
```
assets/
├── .htaccess          # Configuración de servidor para MIME types
├── css/
│   ├── admin.css      # Estilos para panel de admin (v1.1.3)
│   └── test.html      # Archivo de prueba para verificar carga
└── js/
    ├── admin.js       # JavaScript para panel de admin (v1.1.3)
    └── test.js        # Archivo de prueba para verificar carga
```

## Solución de Problemas 404/MIME Type

### 1. Verificar Archivos en Servidor
Los archivos deben existir en:
- `/wp-content/plugins/dominios-reseller/assets/css/admin.css`
- `/wp-content/plugins/dominios-reseller/assets/js/admin.js`

### 2. Verificar Permisos
- Directorios: 755
- Archivos: 644

### 3. URLs de Prueba
Acceder directamente para verificar carga:
- `https://tudominio.com/wp-content/plugins/dominios-reseller/assets/css/test.html`
- `https://tudominio.com/wp-content/plugins/dominios-reseller/assets/css/admin.css`
- `https://tudominio.com/wp-content/plugins/dominios-reseller/assets/js/admin.js`

### 4. Si Persisten Errores 404
1. Verificar que el plugin esté activado
2. Reactivar el plugin en WordPress
3. Verificar permisos de escritura en directorio
4. Limpiar cache si existe

### 5. Si Hay Errores MIME Type
1. Verificar que `.htaccess` esté en `/assets/`
2. Verificar configuración del servidor web
3. Confirmar que archivos tengan contenido válido

## Debug
- CSS incluye comentario de versión para verificar carga
- JS incluye console.log para verificar ejecución
- Incrementar versión en `DOMINIOS_RESELLER_VERSION` fuerza actualización de cache