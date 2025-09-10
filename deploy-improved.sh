#!/bin/bash
echo "🚀 Deploying plugins to WordPress..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
REPO_DIR="/home/replanta/repos/plugins"
WP_PLUGINS_DIR="/home/replanta/replanta.net/wp-content/plugins"

# Function to deploy a plugin
deploy_plugin() {
    local plugin_name=$1
    echo -e "${BLUE}📦 Deploying ${plugin_name}...${NC}"
    
    if [ -d "$REPO_DIR/$plugin_name" ]; then
        # Create backup
        if [ -d "$WP_PLUGINS_DIR/$plugin_name" ]; then
            echo -e "${YELLOW}💾 Creating backup...${NC}"
            cp -r "$WP_PLUGINS_DIR/$plugin_name" "$WP_PLUGINS_DIR/${plugin_name}_backup_$(date +%Y%m%d_%H%M%S)"
        fi
        
        # Sync files
        rsync -av --delete "$REPO_DIR/$plugin_name/" "$WP_PLUGINS_DIR/$plugin_name/"
        
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✅ ${plugin_name} deployed successfully!${NC}"
        else
            echo -e "${RED}❌ Error deploying ${plugin_name}${NC}"
            return 1
        fi
    else
        echo -e "${RED}❌ Plugin ${plugin_name} not found in repo${NC}"
        return 1
    fi
}

# Pull latest changes from GitHub
echo -e "${BLUE}📥 Pulling latest changes from GitHub...${NC}"
cd "$REPO_DIR"
git pull origin main

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Failed to pull from GitHub${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Git pull completed${NC}"
echo ""

# Deploy all plugins
echo -e "${BLUE}🔄 Starting deployment of all plugins...${NC}"
echo ""

PLUGINS=(
    "dominios-reseller"
    "replanta-republish-ai" 
    "selloreplanta-main"
    "truspilot-replanta"
)

DEPLOYED=0
FAILED=0

for plugin in "${PLUGINS[@]}"; do
    if deploy_plugin "$plugin"; then
        ((DEPLOYED++))
    else
        ((FAILED++))
    fi
    echo ""
done

# Summary
echo -e "${BLUE}📊 Deployment Summary:${NC}"
echo -e "${GREEN}✅ Successfully deployed: ${DEPLOYED}${NC}"
if [ $FAILED -gt 0 ]; then
    echo -e "${RED}❌ Failed deployments: ${FAILED}${NC}"
fi

# Clear WordPress cache if possible
if [ -f "$WP_PLUGINS_DIR/../../wp-config.php" ]; then
    echo -e "${YELLOW}🧹 Clearing WordPress cache...${NC}"
    # You can add cache clearing commands here if needed
fi

echo ""
echo -e "${GREEN}🎉 Deployment completed!${NC}"
echo -e "${BLUE}📁 Files synced to: ${WP_PLUGINS_DIR}/${NC}"
