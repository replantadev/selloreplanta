# 🚀 DEPLOYMENT SELLO-REPLANTA v2.0.4 a GitHub

## 📋 Cambios en esta versión

### ✅ Verificación mejorada con reintentos automáticos
- Re-verificación automática cada hora si falla
- Botón "Re-verificar ahora" en página de configuración
- Mejor manejo de errores de conexión
- Panel visual mostrando estado del dominio

---

## 🔧 Archivos modificados

```
✅ sello-replanta.php (v2.0.3 → v2.0.4)
✅ CHANGELOG.md (añadido v2.0.4)
✅ readme.txt (stable tag → 2.0.4)
```

---

## 🚀 PASOS PARA DEPLOYMENT

### 1. Verificar estado actual

```powershell
cd "C:\Users\programacion2\Local Sites\repos\selloreplanta-clean"
git status
```

**Deberías ver:**
```
modified:   CHANGELOG.md
modified:   readme.txt
modified:   sello-replanta.php
```

---

### 2. Añadir cambios al staging

```powershell
git add CHANGELOG.md
git add readme.txt
git add sello-replanta.php
```

O todo de una vez:
```powershell
git add .
```

---

### 3. Verificar cambios antes de commit

```powershell
git diff --staged
```

Esto mostrará todos los cambios que vas a commitear.

---

### 4. Crear commit

```powershell
git commit -m "v2.0.4 - Verificación mejorada con reintentos automáticos

- Añadido botón Re-verificar ahora en configuración
- Re-verificación automática cada hora si falla
- Mejor manejo de errores de conexión
- Panel visual mostrando estado del dominio
- Timeout aumentado a 15s
- Validación HTTP y JSON mejorada
- Logs detallados con prefijo [Sello Replanta]"
```

---

### 5. Push a GitHub

```powershell
git push origin main
```

---

### 6. Crear TAG v2.0.4

```powershell
git tag -a v2.0.4 -m "Release v2.0.4 - Verificación mejorada

Mejoras principales:
- Re-verificación automática con reintentos
- Botón manual de re-verificación
- Mejor UX en página de configuración
- Soluciona problema de dominio no detectado"
```

---

### 7. Push del TAG

```powershell
git push origin v2.0.4
```

---

### 8. Crear Release en GitHub

#### Opción A: Via Web (RECOMENDADO)

1. Ir a: **https://github.com/replantadev/selloreplanta/releases**
2. Clic en **"Create a new release"**
3. **Choose a tag:** Seleccionar `v2.0.4` (recién creado)
4. **Release title:** `v2.0.4 - Verificación mejorada`
5. **Description:** Copiar esto:

```markdown
## 🎯 Verificación Mejorada con Reintentos Automáticos

Esta versión soluciona el problema de "El dominio no está en Replanta" cuando hay fallos temporales de conexión.

### ✨ Nuevas Características

- ✅ **Re-verificación automática** cada hora si la verificación falla
- ✅ **Botón "Re-verificar ahora"** en Ajustes → Sello Replanta
- ✅ **Panel visual** mostrando estado actual del dominio
- ✅ **Información detallada** sobre posibles causas de error

### 🔧 Mejoras Técnicas

- Timeout aumentado a 15 segundos para conexiones lentas
- Validación de código HTTP antes de procesar respuesta
- Validación de JSON antes de procesar datos
- Logging mejorado con prefijo `[Sello Replanta]`
- Timestamp de última verificación fallida

### 🐛 Correcciones

- **FIXED:** Plugin ya no cachea indefinidamente verificaciones fallidas
- **FIXED:** Verificación automática se reintenta si falla por conexión temporal
- **FIXED:** Logs duplicados eliminados

### 📊 Interfaz Mejorada

La página de configuración ahora muestra:
- Estado actual del dominio (✅ alojado / ❌ no alojado)
- Información sobre última verificación fallida
- Posibles causas si el dominio no está en Replanta
- Botón de re-verificación manual con protección CSRF

### 🔄 Actualización

Los sitios con el plugin instalado se actualizarán automáticamente en las próximas 24 horas.

Si necesitas actualizar manualmente:
1. Ve a **Plugins** en tu WordPress
2. Busca actualizaciones disponibles
3. Actualiza **Sello Replanta** a v2.0.4

### 💡 Nota para usuarios actuales

Si tu sello no aparece después de actualizar:
1. Ve a **Ajustes → Sello Replanta**
2. Haz clic en **"🔄 Re-verificar ahora"**
3. El plugin verificará tu dominio con la API mejorada
```

6. **Set as latest release:** ✅ (marcar)
7. Clic en **"Publish release"**

---

#### Opción B: Via GitHub CLI (si lo tienes instalado)

```powershell
gh release create v2.0.4 --title "v2.0.4 - Verificación mejorada" --notes-file release-notes.md
```

---

### 9. Verificar Release

1. Ir a: **https://github.com/replantadev/selloreplanta/releases/latest**
2. Verificar que aparece **v2.0.4**
3. Verificar que los archivos ZIP están generados automáticamente

---

### 10. Probar actualización automática

En una web de cliente con el plugin:

1. Esperar ~5-10 minutos (GitHub Updater cachea)
2. Ir a **Plugins** en WordPress
3. Debería aparecer actualización disponible
4. Actualizar manualmente o esperar actualización automática

---

## 🔍 Verificación Post-Deployment

### Check 1: Tag existe en GitHub
```powershell
git ls-remote --tags origin
```
Debería listar `v2.0.4`

### Check 2: Release público
Visitar: https://github.com/replantadev/selloreplanta/releases/tag/v2.0.4

### Check 3: ZIP descargable
GitHub genera automáticamente:
- `Source code (zip)`
- `Source code (tar.gz)`

### Check 4: Actualización en cliente
1. Ir a una web de cliente
2. Dashboard → Actualizaciones
3. Debería aparecer "Sello Replanta v2.0.4 disponible"

---

## 📝 Resumen de Comandos (Todo junto)

```powershell
# Navegar al directorio
cd "C:\Users\programacion2\Local Sites\repos\selloreplanta-clean"

# Verificar estado
git status

# Añadir archivos
git add CHANGELOG.md readme.txt sello-replanta.php

# Commit
git commit -m "v2.0.4 - Verificación mejorada con reintentos automáticos"

# Push a main
git push origin main

# Crear tag
git tag -a v2.0.4 -m "Release v2.0.4 - Verificación mejorada"

# Push tag
git push origin v2.0.4

# Ahora ir a GitHub web para crear el Release
```

---

## ⚠️ IMPORTANTE

### Antes de crear el Release:

1. ✅ Verificar que `sello-replanta.php` tiene `Version: 2.0.4`
2. ✅ Verificar que `SR_VERSION = '2.0.4'`
3. ✅ Verificar que `readme.txt` tiene `Stable tag: 2.0.4`
4. ✅ Verificar que `CHANGELOG.md` incluye sección `[2.0.4]`
5. ✅ Hacer commit y push ANTES de crear tag

### Después de crear el Release:

1. ✅ Verificar que el ZIP se genera correctamente
2. ✅ Descargar el ZIP y verificar contenido
3. ✅ Probar en una instalación de prueba
4. ✅ Monitorear actualizaciones en webs de clientes

---

## 🐛 Si algo sale mal

### Error: Tag ya existe
```powershell
# Eliminar tag local
git tag -d v2.0.4

# Eliminar tag remoto
git push origin :refs/tags/v2.0.4

# Crear tag de nuevo
git tag -a v2.0.4 -m "Release v2.0.4"
git push origin v2.0.4
```

### Error: Olvidaste hacer commit antes del tag
```powershell
# Eliminar tag
git tag -d v2.0.4
git push origin :refs/tags/v2.0.4

# Hacer commit
git add .
git commit -m "v2.0.4 - Cambios finales"
git push origin main

# Crear tag de nuevo
git tag -a v2.0.4 -m "Release v2.0.4"
git push origin v2.0.4
```

---

## 📊 Cronología de Actualización

1. **T+0 min:** Push y Release creado
2. **T+5 min:** GitHub procesa release y genera ZIPs
3. **T+1 hora:** GitHub Updater en webs de clientes detecta nueva versión
4. **T+24 horas:** Mayoría de clientes actualizados (auto-update)
5. **T+1 semana:** Todos los clientes deberían estar en v2.0.4

---

## 🎯 Resultado Esperado

Después del deployment:

1. ✅ Release v2.0.4 visible en GitHub
2. ✅ Clientes ven notificación de actualización
3. ✅ Al actualizar, obtienen función de re-verificación
4. ✅ Problema de "dominio no está en replanta" se soluciona
5. ✅ Botón "Re-verificar ahora" disponible para todos

---

## 📞 Soporte Post-Release

Si clientes reportan problemas:

1. **Verificar que actualizaron:** Ir a Plugins, debe decir v2.0.4
2. **Limpiar opción:** `delete_option('sello_replanta_is_hosted');`
3. **Re-verificar:** Clic en botón "Re-verificar ahora"
4. **Verificar API:** La API en replanta.net debe estar activa

---

**Estado:** ✅ LISTO PARA DEPLOYMENT

**Próximo paso:** Ejecutar comandos git y crear Release en GitHub
