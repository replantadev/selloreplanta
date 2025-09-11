# Changelog - Plugin Dominios Reseller

## [1.0.1] - 2025-09-07

### 🚨 CRÍTICO - Error Fatal Resuelto
- **FIXED**: Error fatal "Cannot access offset of type string on string" en línea 79
- **FIXED**: Validación robusta de tipos de datos en APIs WHM
- **FIXED**: Manejo seguro de respuestas de addon domains

### 🛡️ Mejoras de Seguridad y Estabilidad
- **ADDED**: Validaciones de tipo array antes de foreach
- **ADDED**: Timeouts en llamadas cURL (30s conexión, 10s timeout)
- **ADDED**: Códigos de estado HTTP en validaciones
- **ADDED**: Logging mejorado con prefijos identificables
- **ADDED**: Manejo de errores en estructura de respuesta API

### 🔧 Mejoras Técnicas
- **IMPROVED**: Función `obtener_addons_de_usuario()` con validaciones completas
- **IMPROVED**: Función `obtener_cuentas_whm()` con manejo robusto de errores
- **IMPROVED**: Función `obtener_trafico_real()` con validaciones de datos
- **IMPROVED**: Logging consistente con formato "[Dominios Reseller]"

### 🚀 Optimizaciones
- **OPTIMIZED**: Reducción de llamadas API fallidas
- **OPTIMIZED**: Mejor handling de respuestas malformadas
- **OPTIMIZED**: Skip automático de addon domains inválidos

## [1.0.0] - 2025-09-06
- **INITIAL**: Versión inicial del plugin
- **ADDED**: Integración con APIs WHM
- **ADDED**: Cálculo de huella de carbono por dominio
- **ADDED**: Gestión de addon domains
- **ADDED**: Interface de administración WordPress
