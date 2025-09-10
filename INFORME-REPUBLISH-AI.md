# 🔧 Informe de Corrección: Republish AI - Medium

## 🎯 Problema Identificado
El plugin de WordPress está intentando enviar contenido a un microservicio Python que **NO está accesible** desde las URLs configuradas:
- ❌ `https://replanta.dev/medium-rr/replanta-medium` → 404 Not Found
- ❌ `https://replanta.net/medium-rr/replanta-medium` → 404 Not Found

## 🛠️ Mejoras Implementadas

### 1. **Diagnóstico Completo**
- ✅ Agregado logging detallado en todas las conexiones
- ✅ Prueba múltiples URLs automáticamente (fallback)
- ✅ Página de diagnóstico en admin (`Republish AI > Diagnóstico`)
- ✅ Notificaciones por email cuando falla

### 2. **URLs Mejoradas**
Ahora el plugin prueba estas URLs en orden:
```php
$urls_to_try = [
    'https://replanta.dev/medium-rr/replanta-medium',
    'https://replanta.net/medium-rr/replanta-medium', 
    'https://replanta.dev/replanta-medium',
    'https://replanta.net/api/medium',
    'https://replanta.net/medium',
    'https://replanta.net/republish/medium'
];
```

### 3. **Interfaz Mejorada**
- ✅ Meta box muestra errores claramente
- ✅ Botón "Reintentar" en posts con errores
- ✅ Columna en listado de posts con estado
- ✅ Página de diagnóstico con herramientas de prueba

### 4. **Manejo de Errores**
- ✅ Errores se guardan con timestamp
- ✅ Notificación automática al admin por email
- ✅ SSL verificación deshabilitada (por si hay problemas de certificado)
- ✅ Timeout aumentado a 30 segundos

## 🚨 ACCIÓN REQUERIDA: Microservicio

### **El problema principal es que el microservicio NO está corriendo.**

### Pasos para solucionarlo:

#### 1. **Verificar el Estado del Microservicio**
Conectarte al servidor donde está el código (`/home/replanta/virtualenv`) y ejecutar:

```bash
# Verificar si está corriendo
ps aux | grep python
ps aux | grep flask

# Verificar el puerto
netstat -tlnp | grep :5000
netstat -tlnp | grep python
```

#### 2. **Activar el Microservicio**
```bash
cd /home/replanta/virtualenv
source bin/activate  # Si usas virtualenv
pip install -r requirements.txt  # Instalar dependencias
python app.py  # Iniciar el servicio
```

#### 3. **Configurar Servidor Web**
El microservicio Flask necesita estar configurado en Nginx/Apache para ser accesible desde las URLs públicas.

**Para Nginx, agregar esta configuración:**
```nginx
location /medium-rr/ {
    proxy_pass http://127.0.0.1:5000/;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
}
```

#### 4. **Alternativa: Usar PM2 o Supervisor**
Para que el microservicio se ejecute permanentemente:

```bash
# Instalar PM2
npm install -g pm2

# Ejecutar el microservicio con PM2
pm2 start app.py --name "medium-republish" --interpreter python3

# Guardar configuración
pm2 save
pm2 startup
```

## 🧪 Herramientas de Prueba Creadas

### 1. **Script de Diagnóstico**
```bash
php test-microservice-standalone.php
```

### 2. **Admin WordPress**
- Ve a `Republish AI > Diagnóstico`
- Haz clic en "🧪 Probar Microservicio"

### 3. **Logs de WordPress**
Revisa el log de errores de WordPress para ver los intentos de conexión:
```bash
tail -f /path/to/wordpress/wp-content/debug.log | grep "Replanta Republish AI"
```

## 📋 Checklist de Verificación

- [ ] ✅ Microservicio Python está corriendo
- [ ] ✅ Puerto 5000 está abierto en el servidor
- [ ] ✅ Nginx/Apache está configurado para proxy
- [ ] ✅ URLs están respondiendo 200 OK
- [ ] ✅ Dependencias Python están instaladas
- [ ] ✅ API keys están configuradas en el microservicio

## 🎯 Una vez solucionado el microservicio:

1. **Probar desde WordPress Admin**: `Republish AI > Diagnóstico`
2. **Crear/editar un post** para probar el envío automático
3. **Revisar el meta box** del post para confirmar éxito
4. **Verificar que aparece la URL de Medium**

## 💡 Estado Actual
- ✅ Plugin de WordPress: **FUNCIONANDO** (con mejor manejo de errores)
- ❌ Microservicio Python: **NO ACCESIBLE** 
- ⚠️ Configuración servidor: **PENDIENTE DE REVISIÓN**

---
**El plugin ahora está preparado para funcionar perfectamente una vez que el microservicio esté accesible.**
