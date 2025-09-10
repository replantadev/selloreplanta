# � Guía de Deployment - Plugins Replanta

## 📋 Resumen del Sistema

Sistema automatizado para desplegar plugins de WordPress desde VS Code local hacia producción en replanta.net usando Git + webhooks.

### 🔄 Flujo de trabajo:
```
VS Code Local → GitHub → Webhook → Servidor → WordPress Producción
```

## 🛠️ Configuración Inicial (Ya completada)

- ✅ Repositorio Git en `/home/replanta/repos/plugins`
- ✅ Webhook en `https://replanta.dev/webhook-simple.php`
- ✅ Scripts de deployment automatizados
- ✅ Token de seguridad configurado

## 📝 Comandos Disponibles

### 🎯 Comando Principal (Recomendado)
```bash
.\push-to-cpanel-webhook.bat
```
**Hace:** Push a GitHub + Trigger webhook + Deploy automático

### 🧪 Comandos de Testing
```bash
.\test-webhook.bat          # Solo probar webhook
.\push-to-github.bat        # Solo push a GitHub (sin deploy)
```

## � Verificación del Deploy

### ✅ Indicadores de éxito:
- Mensaje: `SUCCESS: Señal de deployment enviada a cPanel`
- Mensaje: `=== Deployment via webhook completado ===`
- Tiempo: `Los cambios deberán aparecer en producción en unos minutos`

### 🚨 Si hay errores:
1. Verificar conexión a internet
2. Comprobar que el webhook esté subido en `replanta.dev`
3. Revisar logs en `/home/replanta/deployment.log`

## 📁 Estructura del Proyecto

```
repos/
├── plugins/                    # Código de plugins
│   └── replanta-republish-ai/  # Plugin principal
├── dniwoo/                     # Plugin dniwoo
├── webhook-simple.php          # Webhook para deployment
├── deploy-improved.sh          # Script de deployment
├── push-to-cpanel-webhook.bat  # Comando principal
├── test-webhook.bat           # Test del webhook
└── push-to-github.bat         # Solo GitHub push
```

## 🎯 Workflow de Desarrollo Diario

### 1. � Editar código
- Trabaja normalmente en VS Code
- Modifica archivos en `/plugins/replanta-republish-ai/`

### 2. 🚀 Desplegar
```bash
# Desde la terminal de VS Code:
cd "c:\Users\programacion2\Local Sites\repos"
.\push-to-cpanel-webhook.bat
```

### 3. ⏱️ Esperar (2-3 minutos)
- El sistema hace push a GitHub
- Activa el webhook
- Sincroniza archivos al servidor
- Actualiza WordPress

### 4. ✅ Verificar
- Ir al admin de WordPress → Plugins → Replanta Tools
- Ver página "Deploy Status" para confirmar versión
- Probar funcionalidad en producción

## 🔧 Solución de Problemas

### ❌ Error: "Token de acceso inválido"
- El webhook no puede autenticar
- Verificar que el archivo `webhook-simple.php` esté en `replanta.dev`

### ❌ Error: "404 Not Found"
- El webhook no está accesible
- Subir `webhook-simple.php` a la raíz de `replanta.dev`

### ❌ Los cambios no aparecen
- Esperar 5 minutos (puede tardar)
- Verificar logs: `/home/replanta/deployment.log`
- Ejecutar manualmente: `.\test-webhook.bat`

## 📊 Monitoreo

### Logs disponibles:
- **Local**: Salida del comando `.bat`
- **Servidor**: `/home/replanta/deployment.log`
- **WordPress**: Admin → Plugins → Replanta Tools → Deploy Status

### Verificación de versión:
- El plugin incrementa automáticamente la versión
- Se muestra en WordPress admin
- También visible en el código fuente

## 🔒 Seguridad

- Token único para el webhook: `replanta_deploy_2025_secure`
- Solo acepta requests POST con token válido
- Logs de todos los intentos de acceso
- Ejecución limitada a scripts autorizados

## 🎉 Casos de Uso

### 🔧 Desarrollo normal:
```bash
# Editar código → Desplegar
.\push-to-cpanel-webhook.bat
```

### 🧪 Solo testing:
```bash
# Probar conectividad
.\test-webhook.bat
```

### 📤 Solo backup en GitHub:
```bash
# Guardar sin desplegar
.\push-to-github.bat
```

---

## 📞 Soporte

Si tienes problemas:
1. Revisar este documento
2. Verificar logs en WordPress admin
3. Comprobar conectividad con `.\test-webhook.bat`
4. Revisar archivos en el servidor vía SSH/cPanel

**Nota**: El sistema está diseñado para ser simple y confiable. En caso de dudas, siempre puedes hacer deployment manual vía SSH/cPanel como backup.

## ⚡ Opciones de Deployment

### **Opción A: Manual** (Recomendado para empezar)
1. `.\push-to-github.bat` - Sube cambios a GitHub
2. En servidor: `cd /home/replanta/repos/plugins && bash deploy.sh`

### **Opción B: Semi-automático** (Webhook manual)
1. `.\push-to-cpanel-webhook.bat` - Sube a GitHub y ejecuta deployment

### **Opción C: Completamente automático** (GitHub Webhook)
1. `.\push-to-github.bat` - GitHub automáticamente ejecuta deployment

## 🔍 Monitoreo

### **Logs de deployment:**
```bash
tail -f /home/replanta/deployment.log
```

### **Estado del repositorio:**
```bash
cd /home/replanta/repos/plugins
git status
git log --oneline -5
```

## 🛠️ Troubleshooting

### **Si el deployment falla:**
1. SSH al servidor
2. `cd /home/replanta/repos/plugins`
3. `git pull` (manual)
4. `bash deploy.sh` (manual)
5. Revisar logs: `tail /home/replanta/deployment.log`

### **Permisos necesarios:**
```bash
chmod +x /home/replanta/repos/plugins/deploy.sh
chmod 644 /home/replanta/public_html/webhook-simple.php
```

## 🎉 Ventajas de este sistema

- ✅ Usa tu infraestructura Git existente
- ✅ Backups automáticos antes de cada deploy
- ✅ Logs detallados
- ✅ Rollback fácil si hay problemas
- ✅ Control granular por plugin
- ✅ Compatible con tu flujo actual
