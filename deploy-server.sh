#!/bin/bash

# Script de deployment mejorado para replanta.net
# Este es el que debe estar en /home/replanta/repos/plugins/deploy.sh

echo "🚀 Deploying plugins to WordPress..."

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Directorios
REPO_DIR="/home/replanta/repos/plugins"
WP_PLUGINS_DIR="/home/replanta/replanta.net/wp-content/plugins"
LOG_FILE="/home/replanta/deployment.log"

# Función de logging
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
    echo -e "$1"
}

# Cambiar al directorio del repositorio
cd "$REPO_DIR" || exit 1

log_message "${BLUE}📥 Pulling latest changes from GitHub...${NC}"

# Pull cambios de GitHub
if git pull origin main; then
    log_message "${GREEN}✅ Git pull successful${NC}"
else
    log_message "${RED}❌ Git pull failed${NC}"
    exit 1
fi

log_message "${BLUE}🔄 Syncing plugins to WordPress...${NC}"

# Lista de plugins a sincronizar
PLUGINS=(
    "replanta-republish-ai"
    "dniwoo"
)

# Sincronizar cada plugin
for plugin in "${PLUGINS[@]}"; do
    if [ -d "$REPO_DIR/$plugin" ]; then
        log_message "${YELLOW}📂 Syncing $plugin...${NC}"
        
        # Crear directorio si no existe
        mkdir -p "$WP_PLUGINS_DIR/$plugin"
        
        # Rsync con opciones de preservación
        if rsync -av --delete \
            --exclude='.git' \
            --exclude='.gitignore' \
            --exclude='*.md' \
            --exclude='node_modules' \
            "$REPO_DIR/$plugin/" "$WP_PLUGINS_DIR/$plugin/"; then
            log_message "${GREEN}✅ $plugin synced successfully${NC}"
        else
            log_message "${RED}❌ Failed to sync $plugin${NC}"
        fi
    else
        log_message "${YELLOW}⚠️ Plugin directory $plugin not found${NC}"
    fi
done

# Verificar archivos de WordPress
log_message "${BLUE}🔍 Verifying WordPress files...${NC}"

for plugin in "${PLUGINS[@]}"; do
    if [ -f "$WP_PLUGINS_DIR/$plugin/$plugin.php" ]; then
        log_message "${GREEN}✅ $plugin.php exists in WordPress${NC}"
    else
        log_message "${RED}❌ $plugin.php missing in WordPress${NC}"
    fi
done

# Establecer permisos correctos
log_message "${BLUE}🔒 Setting correct permissions...${NC}"
find "$WP_PLUGINS_DIR" -type f -exec chmod 644 {} \;
find "$WP_PLUGINS_DIR" -type d -exec chmod 755 {} \;

log_message "${GREEN}🎉 Deployment completed successfully!${NC}"

# Mostrar timestamp de último cambio
log_message "${BLUE}📅 Last modification in WordPress plugins:${NC}"
ls -la "$WP_PLUGINS_DIR/replanta-republish-ai/replanta-republish-ai.php" 2>/dev/null || log_message "${YELLOW}⚠️ Main plugin file not found${NC}"

exit 0
