@echo off
echo ========================================
echo   CONFIGURACION CPANEL GIT DEPLOYMENT
echo ========================================
echo.

echo 1. En cPanel Git, clona el repositorio:
echo    Clone URL: https://github.com/replantadev/plugins.git
echo    Path: /home/replanta/replanta.net/wp-content/plugins
echo.

echo 2. Despues de clonar, agrega el remote cpanel:
git remote add cpanel ssh://replanta@replanta.net/home/replanta/replanta.net/wp-content/plugins/.git

echo.
echo 3. Configura el push directo:
echo    git push cpanel master
echo.

echo Remote cpanel agregado. Ahora puedes usar:
echo   git push cpanel master
echo.

pause
