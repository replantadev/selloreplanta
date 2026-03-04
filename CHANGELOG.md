# Changelog - Sello Replanta

## [2.0.8] - 2026-03-04 - Plugin Icon

### Anadido
- **NUEVO**: Icono del plugin visible en la pantalla de actualizaciones de WordPress
- Usa el icono oficial de Replanta desde CDN

## [2.0.7] - 2026-02-18 - FIX FATAL ERROR: PUC + AUTO-UPDATE

### 🚨 BUG CORREGIDO: Fatal error "Class PucFactory not found"
- **CORREGIDO**: `vendor/` estaba excluido por `.gitignore` → la librería PUC no se desplegaba
- **CORREGIDO**: El `use` statement y `PucFactory::buildUpdateChecker()` estaban fuera del `if (file_exists(...))` → fatal error si faltaba vendor
- **CORREGIDO**: PUC buscaba rama `master` (default) pero el repo usa `main` → 403 en API
- **AÑADIDO**: `$updateChecker->setBranch('main')` para apuntar a la rama correcta
- **AÑADIDO**: `class_exists()` check para evitar fatal error si PUC no se carga

## [2.0.6] - 2026-02-18 🔧 Z-INDEX FIX COMPLETO

### 🚨 BUG CORREGIDO: Z-Index del admin no se aplicaba en el front (fix definitivo, sustituye v2.0.5 incompleto)
- **CORREGIDO**: El valor numérico de z-index configurado en admin se ignoraba. Un `switch` en PHP trataba el valor como string ('low','medium','high') cuando la validación lo guardaba como entero. Siempre caía en default → 9999.
- **CORREGIDO**: El CSS tenía `z-index: 9999 !important` hardcodeado en `#sello-replanta-container`, que machacaba cualquier inline style. Eliminado el `!important`.
- **CORREGIDO**: El JS (`detectAndFixConflicts`) sobrescribía el z-index a 99 al detectar chats, sin respetar el valor del admin. Ahora solo baja a 99 si el usuario dejó el default (9999).
- **AÑADIDO**: `data-zindex` en el contenedor HTML para que el JS conozca el valor real del admin.

### 📝 RESUMEN TÉCNICO
- Ahora el flujo es: Admin guarda `intval()` → PHP pone inline style `z-index: X` + `data-zindex="X"` → CSS no interfiere → JS solo modifica si es el default.

## [2.0.3] - 2025-11-03 🧹 CLEAN & OPTIMIZED

### ✨ MEJORAS DE CÓDIGO
- **VERIFICADO**: Control de Z-Index ya implementado como input numérico (no rango) para ajuste fino
- **LIMPIO**: Código JavaScript sin mensajes de consola (ya estaba limpio)
- **ACTUALIZADO**: Versiones sincronizadas en todos los archivos del plugin

### 📝 MANTENIMIENTO
- **SINCRONIZADO**: Versión 2.0.3 en PHP header, SR_VERSION y readme.txt
- **DOCUMENTADO**: CHANGELOG actualizado con estado actual del plugin

## [2.0.2] - 2025-09-07 🎯 BEAVER BUILDER + ASTRA FIX

### 🚨 PROBLEMA RESUELTO: Posicionamiento en Beaver + Astra
- **CORREGIDO**: Sello aparece ahora DESPUÉS del footer de Astra (#colophon) en sitios con Beaver Builder
- **MEJORADO**: Prioridad de inserción: Footers de tema → Page builders → Fallbacks
- **AÑADIDO**: Detección específica de footers de temas populares (Astra, GeneratePress, etc.)
- **MEJORADO**: Estrategias de inserción: 'after', 'append', 'prepend' para posicionamiento preciso

### 🏗️ Mejoras en Detección de Temas
- **AÑADIDO**: Selectores específicos para Astra Theme (#colophon, .ast-footer-wrap)
- **AÑADIDO**: Soporte mejorado para Twenty themes (.site-info)
- **AÑADIDO**: Detección de footers semánticos ([role="contentinfo"])
- **MEJORADO**: Logging detallado del proceso de inserción

### 🔍 Lógica de Posicionamiento Mejorada
- **MEJORADO**: Búsqueda en 3 pasos: Temas → Page Builders → Fallbacks
- **MEJORADO**: Estrategia 'after' para insertar DESPUÉS de footers
- **OPTIMIZADO**: Mejor detección de elementos visibles
- **AÑADIDO**: Información detallada en consola sobre estrategias de inserción

## [2.0.1] - 2025-09-07 🔧 COMPATIBILIDAD CON CHATS

### 🚨 PROBLEMA RESUELTO: Conflictos con Chats
- **CORREGIDO**: Sello aparecía por encima de chats como Crisp, Intercom, WhatsApp
- **AÑADIDO**: Detección automática de plugins de chat populares
- **AÑADIDO**: Ajuste automático de z-index cuando se detectan chats
- **AÑADIDO**: Margen inferior automático para evitar solapamientos

### ⚙️ NUEVAS CONFIGURACIONES PRO
- **AÑADIDO**: Control de Z-Index (Bajo, Medio, Alto, Muy Alto)
- **AÑADIDO**: Configuración de margen inferior personalizable
- **AÑADIDO**: Opción "Bajo" que coloca el sello debajo de chats automáticamente

### 🔧 DETECCIÓN INTELIGENTE DE CHATS
- **✅ Crisp Chat**: Detectado y compatible
- **✅ Intercom**: Detectado y compatible  
- **✅ Zendesk Chat**: Detectado y compatible
- **✅ Tawk.to**: Detectado y compatible
- **✅ LiveChat**: Detectado y compatible
- **✅ WhatsApp Floating**: Detectado y compatible
- **✅ Botones flotantes genéricos**: Detectados

### 📱 CONFIGURACIÓN RECOMENDADA
- **Para sitios con chat**: Z-Index "Bajo" + Margen 70px
- **Para sitios sin chat**: Z-Index "Automático" + Margen 0px
- **Detección automática**: El plugin ajusta automáticamente si detecta conflictos

### 🎯 MEJORAS TÉCNICAS
- Logging mejorado con información de conflictos detectados
- CSS específico para compatibilidad con chats
- Clase `.sello-chat-friendly` automática
- Ajustes dinámicos de posicionamiento

---

## [2.0.0] - 2025-09-07 🚀 VERSIÓN PRO

### 🌟 NUEVA VERSIÓN PRO
- **NUEVO**: Detección inteligente de page builders (Elementor, Divi, Beaver Builder)
- **NUEVO**: Configuración avanzada de posicionamiento
- **NUEVO**: Múltiples tamaños de sello (pequeño, normal, grande)
- **NUEVO**: Control de opacidad personalizable
- **NUEVO**: Posicionamiento fijo opcional
- **NUEVO**: Detección específica para `.elementor-location-footer`

### 🎯 COMPATIBILIDAD ELEMENTOR
- **AÑADIDO**: Detección automática de footer de Elementor
- **AÑADIDO**: Soporte para `.elementor-location-footer`
- **AÑADIDO**: Integración con hooks de Elementor Frontend
- **AÑADIDO**: Detección de widgets dinámicos

### ⚙️ CONFIGURACIONES PRO
- **AÑADIDO**: Selector de posición (automático, footer, body, fijo, elementor)
- **AÑADIDO**: Tres tamaños diferentes del sello
- **AÑADIDO**: Control deslizante de opacidad
- **AÑADIDO**: Panel de administración mejorado con info de page builders

### 🎨 CSS AVANZADO
- **MEJORADO**: Estilos específicos para cada page builder
- **AÑADIDO**: Soporte para modo oscuro automático
- **AÑADIDO**: Estilos responsive optimizados
- **AÑADIDO**: Compatibilidad con temas populares (Astra, Genesis)
- **AÑADIDO**: Soporte para accesibilidad mejorada

### 📱 JAVASCRIPT INTELIGENTE
- **REESCRITO**: Sistema de detección completamente nuevo
- **AÑADIDO**: Estrategias de posicionamiento en cascada
- **AÑADIDO**: Logging detallado para debugging
- **AÑADIDO**: Manejo de contenido dinámico de Elementor
- **AÑADIDO**: Múltiples fallbacks de inicialización

### 🔧 MEJORAS TÉCNICAS
- **OPTIMIZADO**: Rendimiento mejorado con detección selectiva
- **AÑADIDO**: Constante de versión (SR_VERSION)
- **MEJORADO**: Validación de opciones más robusta
- **AÑADIDO**: Soporte para estilos inline dinámicos

---

## [1.0.20] - 2025-09-07

### 🔧 Mejoras de Compatibilidad
- **CORREGIDO**: Problema principal donde el sello no se mostraba en algunos temas
- **MEJORADO**: JavaScript más robusto que funciona independientemente de la estructura del tema
- **MEJORADO**: Múltiples estrategias de posicionamiento para máxima compatibilidad
- **MEJORADO**: CSS con especificidad alta (!important) para evitar conflictos con temas

### ✨ Nuevas Características
- **AÑADIDO**: Detección automática de color de fondo mejorada
- **AÑADIDO**: Sistema de fallback para posicionamiento
- **AÑADIDO**: Mejor manejo de errores JavaScript
- **AÑADIDO**: Soporte responsive mejorado
- **AÑADIDO**: Función de versionado automático

### 🐛 Correcciones
- **CORREGIDO**: JavaScript no se ejecutaba por variable `selloReplantaData` no definida
- **CORREGIDO**: Problema de posicionamiento en temas sin elemento `<footer>`
- **CORREGIDO**: Imagen no se mostraba en algunos layouts
- **CORREGIDO**: Conflictos de CSS con algunos temas populares

---

## Versiones Anteriores

### [1.0.17] - 2025-05-04
- Versión estable anterior
- Funcionalidad básica de mostrar sello
- Detección de dominio Replanta
- Configuración de modo claro/oscuro
