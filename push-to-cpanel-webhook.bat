@echo off
echo === PUSH TO CPANEL VIA WEBHOOK ===
echo Deployando a produccion usando webhook de GitHub...

REM Primero sincronizar y subir a GitHub
echo PASO 1: Sincronizando desde WordPress...
call sync-to-repos.bat

echo.
echo PASO 2: Subiendo a GitHub...
call push-to-github.bat

echo.
echo PASO 3: Triggering cPanel deployment via webhook...

REM CONFIGURACION - WEBHOOK SIMPLE QUE USA TU SISTEMA GIT EXISTENTE
set WEBHOOK_URL=https://replanta.net/webhook-simple.php?token=replanta_deploy_2025_secure

REM Hacer llamada al webhook
echo Enviando señal de deployment a cPanel...
curl -X POST "%WEBHOOK_URL%" -H "Content-Type: application/json" -d "{\"action\":\"deploy\",\"plugins\":[\"replanta-republish-ai\",\"selloreplanta-main\",\"dominios-reseller\",\"truspilot-replanta\"]}"

if errorlevel 1 (
    echo ADVERTENCIA: Error al contactar webhook de cPanel
    echo Verifica que el webhook este configurado correctamente
) else (
    echo SUCCESS: Señal de deployment enviada a cPanel
)

echo.
echo === Deployment via webhook completado ===
echo Los cambios deberan aparecer en produccion en unos minutos
pause
