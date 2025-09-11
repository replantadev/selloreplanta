#!/bin/bash
# Deploy script para cPanel
# Este archivo va en: /home/replanta/deploy-plugins.sh

echo "=== REPLANTA PLUGINS DEPLOY ==="
echo "Actualizando repositorio..."

# Ir al repositorio
cd /home/replanta/repos/plugins

# Pull de los últimos cambios
git pull origin master

echo "Sincronizando plugins a WordPress..."

# Copiar plugins específicos (evitando sobreescribir otros plugins)
rsync -av --delete /home/replanta/repos/plugins/replanta-republish-ai/ /home/replanta/public_html/wp-content/plugins/replanta-republish-ai/
rsync -av --delete /home/replanta/repos/plugins/dominios-reseller/ /home/replanta/public_html/wp-content/plugins/dominios-reseller/
rsync -av --delete /home/replanta/repos/plugins/selloreplanta-main/ /home/replanta/public_html/wp-content/plugins/selloreplanta-main/
rsync -av --delete /home/replanta/repos/plugins/truspilot-replanta/ /home/replanta/public_html/wp-content/plugins/truspilot-replanta/
rsync -av --delete /home/replanta/repos/plugins/indice/ /home/replanta/public_html/wp-content/plugins/indice/

echo "✅ Deploy completado!"
echo "Plugins actualizados en WordPress"
