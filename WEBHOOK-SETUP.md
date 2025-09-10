# 🚀 Configuración del Webhook para Deployment Automático

## 📋 Pasos de Instalación

### 1. **Subir el webhook a tu servidor**
1. Sube el archivo `webhook-deploy.php` a `https://replanta.dev/webhook-deploy.php`
2. Asegúrate de que sea accesible desde el navegador

### 2. **Verificar permisos**
El webhook necesita permisos para:
- ✅ Ejecutar comandos Git (`git clone`, `git pull`)
- ✅ Crear/modificar directorios en `/home/replanta/public_html/wp-content/plugins/`
- ✅ Escribir logs en `/home/replanta/deployment.log`

### 3. **Probar la configuración**
```bash
# Desde VS Code, ejecuta:
.\test-webhook.bat
```

### 4. **Usar el deployment**
```bash
# Deployment completo (GitHub + cPanel)
.\push-to-cpanel-webhook.bat

# Solo GitHub (como siempre)
.\push-to-github.bat
```

## 🔧 Configuración del Servidor

### **Si tienes cPanel:**
1. Ve a **File Manager**
2. Sube `webhook-deploy.php` a `public_html/`
3. Asegúrate de que los permisos sean `644`

### **Si tienes acceso SSH:**
```bash
# Dar permisos al webhook
chmod 644 /home/replanta/public_html/webhook-deploy.php

# Crear directorio de logs
touch /home/replanta/deployment.log
chmod 666 /home/replanta/deployment.log
```

## 🔍 Troubleshooting

### **Error 404 - Webhook no encontrado**
- Verifica que `webhook-deploy.php` esté en la raíz de `public_html/`
- Prueba acceder a `https://replanta.dev/webhook-deploy.php` desde el navegador

### **Error 403 - Permisos**
- Verifica permisos del archivo webhook
- Asegúrate de que PHP esté habilitado

### **Error de Git**
- Verifica que Git esté instalado en el servidor
- Puede que necesites configurar SSH keys para GitHub

### **Error de escritura**
- Verifica permisos de la carpeta `plugins/`
- Crea manualmente el archivo de log si es necesario

## 📊 Logs

Los logs de deployment se guardan en:
- `/home/replanta/deployment.log` (en el servidor)
- Consola de Windows (durante la ejecución)

## 🎯 URLs Configuradas

- **Webhook**: `https://replanta.dev/webhook-deploy.php`
- **Token**: `replanta_deploy_2025_secure`
- **Repositorio**: `https://github.com/replantadev/plugins.git`

## ⚡ Flujo de Trabajo

1. **Hacer cambios** en VS Code
2. **Ejecutar** `.\push-to-cpanel-webhook.bat`
3. **Automáticamente**:
   - Sincroniza desde WordPress local
   - Sube cambios a GitHub
   - Activa webhook en el servidor
   - Descarga y actualiza plugins en producción

¡Todo listo en un solo comando! 🎉
