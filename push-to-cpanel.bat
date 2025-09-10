@echo off
echo === PUSH TO CPANEL PRODUCTION ===
echo Subiendo cambios directamente a cPanel/Produccion...

REM Configuracion - EDITA ESTAS VARIABLES
set CPANEL_USER=replanta
set CPANEL_HOST=replanta.dev
set CPANEL_PATH=/home/replanta/public_html/wp-content/plugins/

REM Verificar que git este disponible
git --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Git no esta instalado o no esta en el PATH
    pause
    exit /b 1
)

REM Verificar que estemos en un repositorio git
if not exist ".git" (
    echo ERROR: No estas en un repositorio Git
    pause
    exit /b 1
)

echo.
echo Procesando plugins para cPanel...

REM Plugin: replanta-republish-ai
echo.
echo Subiendo replanta-republish-ai a cPanel...
cd "plugins\replanta-republish-ai"
git add .
git commit -m "Update from VS Code development - %date% %time%"
git push cpanel main
if errorlevel 1 (
    echo ADVERTENCIA: Error al subir replanta-republish-ai a cPanel
) else (
    echo SUCCESS: replanta-republish-ai subido a cPanel
)
cd ..\..

REM Plugin: selloreplanta-main  
echo.
echo Subiendo selloreplanta-main a cPanel...
cd "plugins\selloreplanta-main"
git add .
git commit -m "Update from VS Code development - %date% %time%"
git push cpanel main
if errorlevel 1 (
    echo ADVERTENCIA: Error al subir selloreplanta-main a cPanel
) else (
    echo SUCCESS: selloreplanta-main subido a cPanel
)
cd ..\..

REM Plugin: dominios-reseller
echo.
echo Subiendo dominios-reseller a cPanel...
cd "plugins\dominios-reseller"
git add .
git commit -m "Update from VS Code development - %date% %time%"
git push cpanel main
if errorlevel 1 (
    echo ADVERTENCIA: Error al subir dominios-reseller a cPanel
) else (
    echo SUCCESS: dominios-reseller subido a cPanel
)
cd ..\..

REM Plugin: truspilot-replanta
echo.
echo Subiendo truspilot-replanta a cPanel...
cd "plugins\truspilot-replanta"
git add .
git commit -m "Update from VS Code development - %date% %time%"
git push cpanel main
if errorlevel 1 (
    echo ADVERTENCIA: Error al subir truspilot-replanta a cPanel
) else (
    echo SUCCESS: truspilot-replanta subido a cPanel
)
cd ..\..

echo.
echo === Push a cPanel completado ===
echo Todos los plugins han sido subidos a produccion
pause
