@echo off
cls
echo ================================================
echo           REPLANTA DEPLOYMENT SYSTEM
echo ================================================
echo.

REM Agregamos todos los cambios
echo [1/3] Agregando archivos...
git add .

REM Commit con mensaje
echo [2/3] Creando commit...
set /p mensaje="Mensaje del commit (Enter = automatico): "
if "%mensaje%"=="" set mensaje="Deploy: %date% %time%"
git commit -m "%mensaje%"

REM Push a GitHub y cPanel
echo [3/3] Desplegando...
echo.
echo → Subiendo a GitHub...
git push origin master:main
if errorlevel 1 (
    echo ❌ Error subiendo a GitHub
    pause
    exit /b 1
)

echo → Desplegando a cPanel...
curl -s -X POST "https://replanta.net/deploy.php"
echo.

echo ✅ DEPLOYMENT COMPLETADO
echo.
echo Revisa WordPress admin en 1 minuto
pause
