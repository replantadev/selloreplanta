@echo off
REM PUSH DIRECTO - GitHub + cPanel al instante
cls
echo ==========================================
echo    PUSH DIRECTO - GitHub + cPanel
echo ==========================================

REM Configurar token de GitHub
set GITHUB_TOKEN=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
git config --global credential.helper store

REM 0. Sincronizar desde WordPress
echo [0/3] Sincronizando desde WordPress...
call sync-from-repos.bat

REM 1. GitHub con token
echo [1/3] Subiendo a GitHub con token...
git add .
set /p commit_msg="Mensaje del commit (Enter para auto): "
if "%commit_msg%"=="" set commit_msg=PUSH DIRECTO: %date% %time%
git commit -m "%commit_msg%"
git push https://%GITHUB_TOKEN%@github.com/replantadev/plugins.git main
if %errorlevel% neq 0 (
    echo ❌ ERROR en GitHub push
    pause
    exit /b 1
)
echo ✅ GitHub actualizado con token

REM 2. cPanel
echo.
echo [2/3] Desplegando a cPanel...
curl -X POST "https://replanta.net/webhook-deploy.php" ^
     -H "Content-Type: application/json" ^
     -d "{\"repository\":{\"name\":\"plugins\"},\"ref\":\"refs/heads/main\",\"force\":true}" ^
     --silent --show-error --fail --max-time 30
if %errorlevel% neq 0 (
    echo ❌ ERROR en deployment cPanel
    pause
    exit /b 1
)
echo ✅ cPanel deployment ejecutado

REM 3. Verificación
echo.
echo [3/3] Verificando...
timeout /t 5 /nobreak >nul
curl -s "https://replanta.net/wp-content/plugins/replanta-republish-ai/replanta-republish-ai.php" | findstr "1.4.1" >nul
if %errorlevel% == 0 (
    echo ✅ DEPLOYMENT COMPLETO - Plugin v1.4.1 en producción
) else (
    echo ⚠️  Deployment ejecutado - Verificar en WordPress admin
)

echo.
echo ==========================================
echo          DEPLOYMENT COMPLETADO
echo ==========================================
pause
