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

echo [3/3] Desplegando...
echo.
echo → Subiendo a GitHub...
git push origin master:main
if %errorlevel% neq 0 (
    echo ERROR: No se pudo subir a GitHub
    pause
    exit /b 1
)

echo → Ejecutando deploy en servidor...
curl -X POST "https://replanta.net/deploy-plugins.php" -H "Content-Type: application/json" -d "{\"action\":\"deploy\",\"branch\":\"master\"}"

echo.
echo ✅ DEPLOYMENT INICIADO
echo.
echo Los plugins se están actualizando en el servidor...
echo Verifica WordPress admin en 1-2 minutos.
echo.
pause
