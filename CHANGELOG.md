# Changelog - Sello Replanta

## [2.0.5] - 2025-12-18 🔧 Z-INDEX FIX & OPTIMIZATION

### 🚨 PROBLEMA RESUELTO: Z-Index no se aplicaba correctamente
- **CORREGIDO**: Z-Index ahora se aplica directamente desde la configuración numérica del admin
- **ELIMINADO**: Switch statement defectuoso que trataba números como strings
- **MEJORADO**: Eliminada regla CSS `z-index: 9999 !important` que sobreescribía estilos inline
- **ACTUALIZADO**: JavaScript ahora respeta el z-index configurado en lugar de clases CSS

### 🛠️ MEJORAS EN JAVASCRIPT
- **MEJORADO**: Detección de conflictos con chats ahora usa `data-zindex` en lugar de clases CSS
- **OPTIMIZADO**: Reducción automática de z-index solo cuando es mayor a 1000 y hay chats detectados
- **AÑADIDO**: Atributo `data-zindex` al contenedor para comunicación PHP-JS

### 🔧 VALIDACIÓN Y GUARDADO
- **VERIFICADO**: Validación de z-index funciona correctamente (1-999999)
- **CONSISTENTE**: Valores por defecto ahora son números en lugar de strings
- **ESTABLE**: Eliminadas inconsistencias entre admin y front-end

### 📊 COMPATIBILIDAD
- **MANTENIDO**: Compatibilidad con todos los page builders (Elementor, Divi, Beaver, etc.)
- **MANTENIDO**: Detección automática de chats y ajuste de z-index/margen
- **MANTENIDO**: Todas las demás funcionalidades sin cambios

## [2.0.4] - 2025-11-10 🔄 VERIFICACIÓN MEJORADA

### 🎯 PROBLEMA RESUELTO: "El dominio no está en replanta"
- **CORREGIDO**: Verificación mejorada con reintentos automáticos
- **AÑADIDO**: Re-verificación cada hora si la primera verificación falla
- **AÑADIDO**: Botón "🔄 Re-verificar ahora" en página de configuración
- **AÑADIDO**: Interfaz mejorada mostrando estado actual del dominio

### 🛠️ MEJORAS EN VERIFICACIÓN API
- **MEJORADO**: Timeout aumentado a 15 segundos para conexiones lentas
- **MEJORADO**: Validación de código HTTP antes de procesar respuesta
- **MEJORADO**: Validación de JSON antes de procesar datos
- **AÑADIDO**: Logging detallado con prefijo [Sello Replanta]
- **AÑADIDO**: Timestamp de última verificación fallida

### 📊 NUEVA PÁGINA DE CONFIGURACIÓN
- **AÑADIDO**: Panel visual mostrando estado del dominio actual
- **AÑADIDO**: Indicador de éxito/error con iconos
- **AÑADIDO**: Información sobre última verificación fallida
- **AÑADIDO**: Posibles causas si el dominio no está en Replanta
- **AÑADIDO**: Botón de re-verificación manual con nonce de seguridad

### 🔐 SEGURIDAD Y ESTABILIDAD
- **MEJORADO**: Manejo robusto de errores de conexión
- **MEJORADO**: Limpieza automática de flags de error tras verificación exitosa
- **AÑADIDO**: Protección CSRF con wp_nonce_field en formularios

### 🐛 CORRECCIONES
- **FIXED**: Plugin ya no cachea indefinidamente verificaciones fallidas
- **FIXED**: Verificación automática se reintenta si falla por conexión temporal
- **FIXED**: Logs duplicados eliminados

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
