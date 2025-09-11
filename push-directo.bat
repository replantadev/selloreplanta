@echo off
REM PUSH DIRECTO - GitHub + cPanel al instante
cls
echo ==========================================
echo    PUSH DIRECTO - GitHub + cPanel
echo ==========================================

REM 1. GitHub
echo [1/3] Subiendo a GitHub...
git add .
git commit -m "PUSH DIRECTO: %date% %time%"
git push origin main
if %errorlevel% neq 0 (
    echo ❌ ERROR en GitHub push
    pause
    exit /b 1
)
echo ✅ GitHub actualizado

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
