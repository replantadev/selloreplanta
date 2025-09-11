@echo off
REM Deployment directo a cPanel - Version Windows
echo === PUSH TO CPANEL DIRECTO ===

REM Verificar directorio
if not exist "plugins" (
    echo Error: Ejecutar desde el directorio raiz del repositorio
    pause
    exit /b 1
)

echo 1. Sincronizando desde WordPress local...
call sync-from-repos.bat

echo.
echo 2. Subiendo a GitHub...
git add .
git commit -m "AUTO DEPLOY: %date% %time%"
git push origin main

echo.
echo 3. Deployment directo a cPanel...

REM Usar curl para forzar el webhook
curl -X POST "https://replanta.net/webhook-deploy.php" ^
     -H "Content-Type: application/json" ^
     -d "{\"repository\":{\"name\":\"plugins\"},\"ref\":\"refs/heads/main\",\"force_pull\":true,\"timestamp\":\"%date%_%time%\"}" ^
     --connect-timeout 10 ^
     --max-time 30

echo.
echo 4. Esperando deployment...
timeout /t 10 /nobreak

echo.
echo 5. Verificando deployment...
curl -s "https://replanta.net/wp-content/plugins/replanta-republish-ai/replanta-republish-ai.php" | findstr "1.4.1" >nul
if %errorlevel% == 0 (
    echo ✅ DEPLOYMENT EXITOSO - Plugin actualizado
) else (
    echo ❌ DEPLOYMENT FALLO - Revisar servidor
)

echo.
echo === PUSH CPANEL COMPLETADO ===
pause
