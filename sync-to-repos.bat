@echo off
REM Script para sincronizar plugins desde WordPress hacia los repositorios

echo === WORDPRESS TO REPOSITORIES SYNC ===
echo Sincronizando plugins desde WordPress hacia repositorios...

REM Verificar que estamos en el directorio correcto
if not exist "app\public\wp-content\plugins" (
    echo Error: No se encontro la instalacion de WordPress
    exit /b 1
)

REM Sincronizar DNIWOO hacia su repositorio individual
echo Sincronizando DNIWOO hacia repositorio individual...
robocopy "app\public\wp-content\plugins\dniwoo" "dniwoo" /E /XO /NP /NJH /NJS 2>nul

REM Sincronizar plugins de Replanta hacia el repositorio colectivo
echo Sincronizando selloreplanta-main hacia repositorio colectivo...
robocopy "app\public\wp-content\plugins\selloreplanta-main" "plugins\selloreplanta-main" /E /XO /NP /NJH /NJS 2>nul

echo Sincronizando dominios-reseller hacia repositorio colectivo...
robocopy "app\public\wp-content\plugins\dominios-reseller" "plugins\dominios-reseller" /E /XO /NP /NJH /NJS 2>nul

echo Sincronizando truspilot-replanta hacia repositorio colectivo...
robocopy "app\public\wp-content\plugins\truspilot-replanta" "plugins\truspilot-replanta" /E /XO /NP /NJH /NJS 2>nul

echo Sincronizando replanta-republish-ai hacia repositorio colectivo...
robocopy "app\public\wp-content\plugins\replanta-republish-ai" "plugins\replanta-republish-ai" /E /XO /NP /NJH /NJS 2>nul

echo === Sincronizacion hacia repositorios completada ===
echo Los plugins han sido sincronizados hacia sus repositorios
