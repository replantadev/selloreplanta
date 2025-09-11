#!/bin/bash
# Deployment directo a cPanel via SSH/rsync
# Requiere configurar SSH keys o credenciales FTP

echo "=== PUSH TO CPANEL DIRECTO ==="

# Verificar que estamos en el directorio correcto
if [ ! -d "plugins" ]; then
    echo "Error: Ejecutar desde el directorio raíz del repositorio"
    exit 1
fi

echo "1. Sincronizando desde WordPress local..."
./sync-from-repos.bat

echo "2. Subiendo a GitHub..."
git add .
git commit -m "AUTO DEPLOY: $(date '+%d/%m/%Y %H:%M:%S')"
git push origin main

echo "3. Deployment directo a cPanel..."

# OPCIÓN A: Usar rsync si tienes SSH
# rsync -avz --delete ./plugins/ usuario@replanta.net:~/public_html/wp-content/plugins/

# OPCIÓN B: Usar WinSCP/FTP directo  
# winscp.exe /script=deploy-script.txt

# OPCIÓN C: Usar curl para trigger el webhook con fuerza
curl -X POST "https://replanta.net/webhook-deploy.php" \
     -H "Content-Type: application/json" \
     -d '{"repository":{"name":"plugins"},"ref":"refs/heads/main","force_pull":true}' \
     --connect-timeout 10 \
     --max-time 30

echo "4. Verificando deployment..."
sleep 5

# Verificar que el deployment funcionó
response=$(curl -s "https://replanta.net/wp-content/plugins/replanta-republish-ai/replanta-republish-ai.php" | head -10)
if [[ $response == *"1.4.1"* ]]; then
    echo "✅ DEPLOYMENT EXITOSO - Plugin actualizado a v1.4.1"
else
    echo "❌ DEPLOYMENT FALLÓ - Revisar logs del servidor"
fi

echo "=== PUSH CPANEL COMPLETADO ==="
