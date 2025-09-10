@echo off
echo === PUSH TO GITHUB AND CPANEL ===
echo Subiendo cambios a GitHub y cPanel simultaneamente...

echo.
echo PASO 1: Sincronizando desde WordPress...
call sync-to-repos.bat

echo.
echo PASO 2: Subiendo a GitHub...
call push-to-github.bat

echo.
echo PASO 3: Subiendo a cPanel...
call push-to-cpanel.bat

echo.
echo === DEPLOYMENT COMPLETO ===
echo Los cambios han sido subidos a:
echo - GitHub (backup y colaboracion)
echo - cPanel (produccion)
pause
