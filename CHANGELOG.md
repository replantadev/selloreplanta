# Changelog - Sello Replanta

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
