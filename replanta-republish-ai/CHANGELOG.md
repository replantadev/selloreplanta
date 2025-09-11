# Replanta Republish AI - Changelog

## Versión 1.2.0 (10 Septiembre 2025)

### 🚀 Nuevas Características
- **Página de Diagnóstico**: Nueva sección en admin para probar conexiones con el microservicio
- **Recuperación Manual**: Página para manejar posts que fallaron en el envío
- **Múltiples URLs Fallback**: El plugin ahora prueba 6 URLs diferentes automáticamente
- **Modo de Recuperación**: Los datos se guardan para procesamiento manual cuando falla el microservicio

### 🔧 Mejoras
- **Logging Avanzado**: Registro detallado de todos los intentos de conexión
- **Notificaciones Inteligentes**: Emails de error limitados a 1 por día para evitar spam
- **Interfaz Mejorada**: Meta box rediseñado con mejor información de estado
- **Manejo de Errores**: Mejor gestión de errores con timestamps y datos de recuperación

### 🛠️ Correcciones
- **URLs Actualizadas**: Prioridad a `replanta.net/medium-rr/` basándose en la configuración del servidor
- **SSL Verificación**: Deshabilitada para evitar problemas de certificados
- **Timeout Aumentado**: De 20s a 30s para conexiones más estables

### 📊 Páginas de Admin Nuevas
1. **Republish AI > Diagnóstico**: Herramientas de prueba del microservicio
2. **Republish AI > Recuperación Manual**: Gestión de posts fallidos
3. **Republish AI > Configuración**: (Existente, mejorado)

### 🔍 Para Verificar la Actualización
- Ve a **Plugins** en WordPress admin
- Busca "Replanta Republish AI"
- La versión debe mostrar **1.2.0**
- Deberían aparecer 3 nuevos submenús en el admin

---

## Versión 0.1 (Inicial)
- Funcionalidad básica de envío a Medium
- Configuración de API keys
- Meta box básico de información
