@echo off
REM Script para hacer push de cambios a GitHub

echo === PUSH TO GITHUB ===
echo Subiendo cambios a los repositorios de GitHub...

REM Commit y push DNIWOO
echo Procesando repositorio DNIWOO...
cd dniwoo
git add .
git commit -m "Update from WordPress development environment - %date% %time%"
git push origin main
cd ..

REM Commit y push plugins collection
echo Procesando repositorio de plugins colectivo...
cd plugins
git add .
git commit -m "Update from WordPress development environment - %date% %time%"
git push origin main
cd ..

echo === Push a GitHub completado ===
echo Todos los cambios han sido subidos a GitHub
