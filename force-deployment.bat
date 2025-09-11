@echo off
echo === FORCE DEPLOYMENT TO PRODUCTION ===
echo Intentando deployment manual forzado...

echo.
echo 1. Sincronizando desde WordPress local...
call sync-from-repos.bat

echo.
echo 2. Pushing to GitHub...
call push-to-github.bat

echo.
echo 3. Ejecutando deployment directo via SSH/curl...
echo Enviando comando de deployment al servidor...

curl -X POST "https://replanta.net/webhook-deploy.php" ^
     -H "Content-Type: application/json" ^
     -d "{\"repository\":{\"name\":\"plugins\"},\"ref\":\"refs/heads/main\",\"force\":true}" ^
     --connect-timeout 30 ^
     --max-time 60

echo.
echo === FORCE DEPLOYMENT COMPLETADO ===
echo Verifica los plugins en el admin de WordPress en unos minutos
pause
