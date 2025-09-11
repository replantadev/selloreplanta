@echo off
echo ========================================
echo       REPLANTA PLUGINS DEPLOYMENT
echo ========================================
echo.

echo [1/3] Agregando cambios al repositorio...
git add .
if %errorlevel% neq 0 (
    echo ERROR: No se pudieron agregar los archivos
    pause
    exit /b 1
)

echo [2/3] Creando commit...
set /p message="Describe los cambios (Enter para usar mensaje automatico): "
if "%message%"=="" set message="Deploy automatico v1.4.2 - %date% %time%"
git commit -m "%message%"
if %errorlevel% neq 0 (
    echo ERROR: No se pudo crear el commit
    pause
    exit /b 1
)

echo [3/3] Desplegando a produccion...
echo.
echo → Subiendo a GitHub...
git push origin master:main
if %errorlevel% neq 0 (
    echo ERROR: No se pudo subir a GitHub
    pause
    exit /b 1
)

echo → Desplegando a cPanel...
git push cpanel master
if %errorlevel% neq 0 (
    echo ERROR: No se pudo desplegar a cPanel
    pause
    exit /b 1
)

echo.
echo ✅ DEPLOYMENT COMPLETADO EXITOSAMENTE
echo.
echo ¡Los plugins han sido actualizados en produccion!
echo Verifica WordPress admin en 30 segundos.
echo.
pause
