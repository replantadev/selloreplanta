# Changelog - Sello Replanta

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

### 🔄 Cambios Técnicos
- Refactorización completa del JavaScript para mayor robustez
- CSS mejorado con mejor especificidad
- HTML simplificado y más semántico
- Eliminación de dependencias innecesarias del DOM

### 📱 Responsive
- Mejores tamaños para dispositivos móviles
- Padding adaptativo según el tamaño de pantalla

---

## Versiones Anteriores

### [1.0.17] - 2025-05-04
- Versión estable anterior
- Funcionalidad básica de mostrar sello
- Detección de dominio Replanta
- Configuración de modo claro/oscuro
