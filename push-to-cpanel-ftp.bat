@echo off
echo === PUSH TO CPANEL VIA FTP/RSYNC ===
echo Subiendo archivos directamente via FTP...

REM CONFIGURACION - EDITA ESTAS VARIABLES
set FTP_HOST=replanta.net
set FTP_USER=tu_usuario_ftp
set FTP_PASS=tu_password_ftp
set FTP_PATH=/public_html/wp-content/plugins/

echo.
echo Sincronizando desde WordPress...
call sync-to-repos.bat

echo.
echo Subiendo plugins via FTP...

REM Verificar si WinSCP está disponible
where winscp.com >nul 2>&1
if errorlevel 1 (
    echo WinSCP no encontrado. Instalalo para usar este metodo.
    echo Alternativa: Usar FileZilla o el webhook method
    pause
    exit /b 1
)

REM Plugin: replanta-republish-ai
echo Subiendo replanta-republish-ai...
winscp.com /command ^
    "open ftp://%FTP_USER%:%FTP_PASS%@%FTP_HOST%" ^
    "synchronize remote plugins\replanta-republish-ai %FTP_PATH%replanta-republish-ai" ^
    "exit"

REM Plugin: selloreplanta-main
echo Subiendo selloreplanta-main...
winscp.com /command ^
    "open ftp://%FTP_USER%:%FTP_PASS%@%FTP_HOST%" ^
    "synchronize remote plugins\selloreplanta-main %FTP_PATH%selloreplanta-main" ^
    "exit"

REM Repetir para otros plugins...

echo.
echo === Upload via FTP completado ===
echo Los plugins han sido subidos a produccion
pause
