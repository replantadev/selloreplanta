# Replanta WordPress Development Environment

Este directorio contiene el entorno de desarrollo completo para todos los plugins de Replanta y DNIWOO.

## Estructura del Proyecto

```
/repos/
├── app/                    # WordPress Local by Flywheel installation
│   └── public/
│       └── wp-content/
│           └── plugins/    # Plugins activos para desarrollo y testing
├── dniwoo/                 # Repositorio individual DNIWOO
├── plugins/                # Repositorio colectivo Replanta plugins
├── sync-to-repos.bat      # Sincronizar WordPress → Repositorios
├── sync-from-repos.bat    # Sincronizar Repositorios → WordPress  
└── push-to-github.bat     # Subir cambios a GitHub
```

## Workflow de Desarrollo

### 1. Desarrollo en WordPress
- Trabaja en los plugins directamente en `app/public/wp-content/plugins/`
- Prueba funcionalidades en el entorno WordPress local
- Detecta errores y problemas en tiempo real

### 2. Sincronización a Repositorios
```bash
# Sincronizar cambios desde WordPress hacia repositorios Git
.\sync-to-repos.bat
```

### 3. Commit y Push a GitHub
```bash
# Subir cambios a GitHub
.\push-to-github.bat
```

### 4. Sincronización desde Repositorios
```bash
# Traer cambios desde repositorios hacia WordPress (cuando sea necesario)
.\sync-from-repos.bat
```

## Plugins Incluidos

### DNIWOO (Repositorio Individual)
- **GitHub**: https://github.com/replantadev/dniwoo
- **Funcionalidad**: Plugin premium WooCommerce con sistema de actualizaciones automáticas
- **WordPress Standards**: ✅ Compliant

### Replanta Plugins Collection (Repositorio Colectivo)
- **GitHub**: https://github.com/replantadev/plugins
- **Plugins incluidos**:
  - `selloreplanta-main` - Sello de neutralidad en carbono
  - `dominios-reseller` - Gestión de dominios y WHM
  - `truspilot-replanta` - Integración Trustpilot
  - `replanta-republish-ai` - Republicación automática con IA

## Configuración de Repositorios

### DNIWOO
- **Repositorio**: Independiente para distribución profesional
- **Auto-updates**: Sistema propio con Plugin Update Checker
- **Contacto**: info@replanta.dev
- **Website**: replanta.net

### Plugins Replanta
- **Repositorio**: Colectivo para gestión centralizada
- **Desarrollo**: Conjunto coordinado
- **Deploy**: Hacia servidores cPanel de producción

## Comandos Útiles

```bash
# Ver estado de WordPress plugins
ls app\public\wp-content\plugins

# Ver estado repositorio DNIWOO
cd dniwoo && git status

# Ver estado repositorio plugins
cd plugins && git status

# Ejecutar WordPress Local
# (desde Local by Flywheel application)
```

## Deployment a Producción

Una vez que los cambios están en GitHub:
1. **Manual**: Descargar desde GitHub y subir vía cPanel
2. **Automatizado**: Usar webhooks de GitHub hacia servidores (próxima implementación)

## Notas Importantes

- **Siempre hacer sync antes de push**: Asegúrate de sincronizar WordPress → Repos antes de subir a GitHub
- **Probar en WordPress local**: Usa el entorno WordPress para detectar errores antes de deploy
- **Commits descriptivos**: Los scripts automáticos incluyen timestamp, pero puedes editarlos manualmente si es necesario
