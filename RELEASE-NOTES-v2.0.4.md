## 🎯 Verificación Mejorada con Reintentos Automáticos

Esta versión soluciona el problema de **"El dominio no está en Replanta"** cuando hay fallos temporales de conexión con la API.

---

### ✨ Nuevas Características

- ✅ **Re-verificación automática** cada hora si la verificación inicial falla
- ✅ **Botón "Re-verificar ahora"** en Ajustes → Sello Replanta
- ✅ **Panel visual mejorado** mostrando estado actual del dominio
- ✅ **Información detallada** sobre posibles causas de error
- ✅ **Indicadores visuales** (✅ alojado / ❌ no alojado)

---

### 🔧 Mejoras Técnicas

- **Timeout aumentado** a 15 segundos para conexiones lentas
- **Validación de código HTTP** antes de procesar respuesta (verifica status 200)
- **Validación de JSON** antes de procesar datos (evita errores de parsing)
- **Logging mejorado** con prefijo `[Sello Replanta]` para debug
- **Timestamp de fallos** guarda cuándo falló la última verificación
- **Limpieza automática** de flags de error tras verificación exitosa

---

### 🐛 Correcciones

- **FIXED:** Plugin ya no cachea indefinidamente verificaciones fallidas
- **FIXED:** Verificación automática se reintenta si falla por conexión temporal
- **FIXED:** Logs duplicados eliminados en función `verificar_dominio_replanta()`
- **FIXED:** Mejor manejo de errores cuando la API no responde

---

### 📊 Interfaz de Usuario Mejorada

La página **Ajustes → Sello Replanta** ahora muestra:

1. **Panel de estado** con información clara:
   - Dominio actual siendo verificado
   - Estado de verificación (✅ alojado / ❌ no alojado)
   - Tiempo desde última verificación fallida

2. **Información contextual:**
   - Posibles causas si el dominio no está en Replanta
   - Mensajes informativos según el estado
   - Botón de acción claramente visible

3. **Botón de re-verificación manual:**
   - Protección CSRF con nonce
   - Mensaje de confirmación tras re-verificación
   - Fácil de usar para clientes

---

### 🔄 Actualización Automática

**Las instalaciones existentes se actualizarán automáticamente en las próximas 24 horas** gracias al sistema de actualización de GitHub.

#### Actualización Manual:

Si prefieres actualizar inmediatamente:

1. Ve a **Plugins** en tu panel de WordPress
2. Busca **actualizaciones disponibles**
3. Actualiza **Sello Replanta PRO** a v2.0.4
4. ¡Listo! 🎉

---

### 💡 Para Usuarios Actuales

**Si después de actualizar tu sello no aparece:**

1. Ve a **Ajustes → Sello Replanta**
2. Haz clic en el botón **"🔄 Re-verificar ahora"**
3. El plugin verificará tu dominio con la nueva lógica mejorada
4. Si tu dominio está en Replanta, el sello aparecerá automáticamente

---

### 🔐 Compatibilidad y Requisitos

- ✅ WordPress 5.0 o superior
- ✅ PHP 7.0 o superior
- ✅ Compatible con todos los page builders (Elementor, Divi, Beaver Builder, etc.)
- ✅ Requiere que el dominio esté alojado en replanta.net
- ✅ Requiere API REST activa en replanta.net (`/wp-json/replanta/v1/check_domain`)

---

### 📝 Notas Técnicas

Esta actualización trabaja en conjunto con la nueva API REST implementada en replanta.net (dominios-reseller v1.2.1). Si eres administrador de replanta.net, asegúrate de que la API esté activa antes de que los clientes actualicen.

**Endpoint de verificación:** `https://replanta.net/wp-json/replanta/v1/check_domain`

---

### 🙏 Agradecimientos

Gracias por usar Sello Replanta PRO. Esta actualización mejora significativamente la experiencia de usuario y la confiabilidad del plugin.

Si tienes algún problema o sugerencia, no dudes en contactarnos.

---

**Replanta** - Hosting Ecológico con Carbono Negativo 🌱
