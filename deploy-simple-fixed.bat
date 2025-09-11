@echo off
echo ========================================
echo       REPLANTA DEPLOYMENT FINAL
echo ========================================
echo.

echo [1/3] Agregando cambios...
git add .

echo [2/3] Commit...
set /p message="Mensaje del commit (Enter = automatico): "
if "%message%"=="" set message="Deploy v1.4.2 - %date% %time%"
git commit -m "%message%"

echo [3/3] Desplegando...
echo.
echo → GitHub...
git push origin master:main
if %errorlevel% neq 0 (
    echo ERROR en GitHub push
    pause
    exit /b 1
)

echo → Activando webhook...
curl -X POST "https://replanta.net/webhook-deployment-fixed.php" ^
     -H "Content-Type: application/json" ^
     -d "{\"action\":\"deploy\"}"

echo.
echo ✅ DEPLOYMENT COMPLETADO
echo.
echo Plugins actualizándose en servidor...
echo Verifica WordPress admin en 1 minuto.
echo.
pause
