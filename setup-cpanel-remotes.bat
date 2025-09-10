@echo off
echo === CONFIGURACION DE REMOTES PARA CPANEL ===
echo Configurando conexiones Git hacia cPanel...

REM CONFIGURACION - EDITA ESTAS VARIABLES SEGUN TU CPANEL
set CPANEL_USER=replanta
set CPANEL_HOST=replanta.net
set CPANEL_BASE_PATH=/home/replanta/public_html/wp-content/plugins

echo.
echo IMPORTANTE: Antes de ejecutar este script, asegurate de tener:
echo 1. Token de GitHub configurado en cPanel
echo 2. Acceso SSH o Git habilitado en tu hosting
echo 3. Las rutas correctas del servidor
echo.
pause

echo Configurando remote 'cpanel' para cada plugin...

REM Plugin: replanta-republish-ai
echo.
echo Configurando replanta-republish-ai...
cd "plugins\replanta-republish-ai"
git remote remove cpanel 2>nul
git remote add cpanel ssh://%CPANEL_USER%@%CPANEL_HOST%%CPANEL_BASE_PATH%/replanta-republish-ai.git
git remote -v
cd ..\..

REM Plugin: selloreplanta-main  
echo.
echo Configurando selloreplanta-main...
cd "plugins\selloreplanta-main"
git remote remove cpanel 2>nul
git remote add cpanel ssh://%CPANEL_USER%@%CPANEL_HOST%%CPANEL_BASE_PATH%/selloreplanta-main.git
git remote -v
cd ..\..

REM Plugin: dominios-reseller
echo.
echo Configurando dominios-reseller...
cd "plugins\dominios-reseller"
git remote remove cpanel 2>nul
git remote add cpanel ssh://%CPANEL_USER%@%CPANEL_HOST%%CPANEL_BASE_PATH%/dominios-reseller.git
git remote -v
cd ..\..

REM Plugin: truspilot-replanta
echo.
echo Configurando truspilot-replanta...
cd "plugins\truspilot-replanta"
git remote remove cpanel 2>nul
git remote add cpanel ssh://%CPANEL_USER%@%CPANEL_HOST%%CPANEL_BASE_PATH%/truspilot-replanta.git
git remote -v
cd ..\..

echo.
echo === Configuracion completada ===
echo Remotes 'cpanel' agregados a todos los plugins
echo Ahora puedes usar 'push-to-cpanel.bat' para deployar
pause
