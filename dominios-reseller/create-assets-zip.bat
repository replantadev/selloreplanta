@echo off
echo ========================================
echo   CREAR ZIP DE ASSETS PARA UPLOAD
echo ========================================
echo.

echo Creando archivo ZIP con todos los assets...

:: Crear directorio temporal
mkdir temp_assets 2>nul
mkdir temp_assets\assets 2>nul
mkdir temp_assets\assets\css 2>nul
mkdir temp_assets\assets\js 2>nul

:: Copiar archivos
copy "assets\css\admin.css" "temp_assets\assets\css\"
copy "assets\js\admin.js" "temp_assets\assets\js\"
copy "assets\.htaccess" "temp_assets\assets\"

:: Usar PowerShell para crear ZIP
powershell "Compress-Archive -Path 'temp_assets\*' -DestinationPath 'dominios-reseller-assets.zip' -Force"

:: Limpiar temporal
rmdir /s /q temp_assets

echo.
echo ========================================
echo   ZIP CREADO: dominios-reseller-assets.zip
echo ========================================
echo.
echo INSTRUCCIONES:
echo 1. Sube el archivo dominios-reseller-assets.zip al servidor
echo 2. Extrae el contenido en: /wp-content/plugins/dominios-reseller/
echo 3. Verifica que quede: /wp-content/plugins/dominios-reseller/assets/css/admin.css
echo 4. Verifica que quede: /wp-content/plugins/dominios-reseller/assets/js/admin.js
echo.
pause