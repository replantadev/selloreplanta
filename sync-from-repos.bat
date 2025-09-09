@echo off
REM Script para sincronizar desde repositorios hacia WordPress

echo === REPOSITORIES TO WORDPRESS SYNC ===
echo Sincronizando desde repositorios hacia WordPress...

REM Verificar que estamos en el directorio correcto
if not exist "app\public\wp-content\plugins" (
    echo Error: No se encontro la instalacion de WordPress
    exit /b 1
)

REM Sincronizar DNIWOO desde su repositorio individual
echo Sincronizando DNIWOO desde repositorio individual...
robocopy "dniwoo" "app\public\wp-content\plugins\dniwoo" /E /XO /NP /NJH /NJS 2>nul

REM Sincronizar plugins de Replanta desde el repositorio colectivo
echo Sincronizando selloreplanta-main desde repositorio colectivo...
robocopy "plugins\selloreplanta-main" "app\public\wp-content\plugins\selloreplanta-main" /E /XO /NP /NJH /NJS 2>nul

echo Sincronizando dominios-reseller desde repositorio colectivo...
robocopy "plugins\dominios-reseller" "app\public\wp-content\plugins\dominios-reseller" /E /XO /NP /NJH /NJS 2>nul

echo Sincronizando truspilot-replanta desde repositorio colectivo...
robocopy "plugins\truspilot-replanta" "app\public\wp-content\plugins\truspilot-replanta" /E /XO /NP /NJH /NJS 2>nul

echo Sincronizando replanta-republish-ai desde repositorio colectivo...
robocopy "plugins\replanta-republish-ai" "app\public\wp-content\plugins\replanta-republish-ai" /E /XO /NP /NJH /NJS 2>nul

echo === Sincronizacion desde repositorios completada ===
echo Los plugins han sido actualizados en WordPress
