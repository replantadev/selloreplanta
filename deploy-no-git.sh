#!/bin/bash

# Script de deployment que NO requiere git pull
# Para usar cuando el webhook ya trae los cambios

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

log_message "${GREEN}✅ Starting deployment (skipping git pull)${NC}"

# Cambiar al directorio del repositorio
cd "$REPO_DIR" || exit 1

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
        VERSION=$(grep -i "Version:" "$WP_PLUGINS_DIR/$plugin/$plugin.php" | head -1 | sed 's/.*Version: *\([0-9.]*\).*/\1/')
        log_message "${GREEN}✅ $plugin.php exists - Version: $VERSION${NC}"
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
log_message "${BLUE}📅 Last modification times:${NC}"
if [ -f "$WP_PLUGINS_DIR/replanta-republish-ai/replanta-republish-ai.php" ]; then
    MODTIME=$(stat -c %y "$WP_PLUGINS_DIR/replanta-republish-ai/replanta-republish-ai.php")
    log_message "${GREEN}📄 replanta-republish-ai.php: $MODTIME${NC}"
else
    log_message "${YELLOW}⚠️ Main plugin file not found${NC}"
fi

exit 0
