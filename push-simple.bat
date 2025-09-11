@echo off
echo ==========================================
echo    PUSH SIMPLE - GitHub + cPanel
echo ==========================================

REM 1. Sincronizar desde WordPress
echo [1/4] Sincronizando desde WordPress...
call sync-from-repos.bat

REM 2. Git add y commit
echo [2/4] Preparando commit...
git add .
set /p commit_msg="Mensaje del commit (Enter para auto): "
if "%commit_msg%"=="" set commit_msg=AUTO-DEPLOY: %date% %time%
git commit -m "%commit_msg%"

REM 3. Push a GitHub
echo [3/4] Subiendo a GitHub...
git push origin master:main --force
if %errorlevel% neq 0 (
    echo ❌ ERROR en GitHub push - intentando sin force
    git push origin master:main
)

REM 4. Deployment a cPanel
echo [4/4] Desplegando a cPanel...
curl -X POST "https://replanta.net/webhook-deploy.php" ^
     -H "Content-Type: application/json" ^
     -d "{\"repository\":{\"name\":\"plugins\"},\"ref\":\"refs/heads/main\",\"force_pull\":true}" ^
     --max-time 30

echo.
echo ✅ DEPLOYMENT COMPLETADO
echo - GitHub: Actualizado
echo - cPanel: Webhook enviado
echo.
echo Verifica los plugins en WordPress admin en 1-2 minutos
pause
