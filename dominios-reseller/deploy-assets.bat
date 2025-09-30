@echo off
echo ========================================
echo   DOMINIOS RESELLER - DEPLOY ASSETS
echo ========================================
echo.

echo [1/4] Creando directorio assets en produccion...
mkdir "\\replanta.net\wp-content\plugins\dominios-reseller\assets" 2>nul
mkdir "\\replanta.net\wp-content\plugins\dominios-reseller\assets\css" 2>nul
mkdir "\\replanta.net\wp-content\plugins\dominios-reseller\assets\js" 2>nul

echo [2/4] Copiando archivos CSS...
copy "assets\css\admin.css" "\\replanta.net\wp-content\plugins\dominios-reseller\assets\css\admin.css"
copy "assets\css\test.html" "\\replanta.net\wp-content\plugins\dominios-reseller\assets\css\test.html"

echo [3/4] Copiando archivos JavaScript...
copy "assets\js\admin.js" "\\replanta.net\wp-content\plugins\dominios-reseller\assets\js\admin.js"
copy "assets\js\test.js" "\\replanta.net\wp-content\plugins\dominios-reseller\assets\js\test.js"

echo [4/4] Copiando archivos de configuracion...
copy "assets\.htaccess" "\\replanta.net\wp-content\plugins\dominios-reseller\assets\.htaccess"
copy "assets\README.md" "\\replanta.net\wp-content\plugins\dominios-reseller\assets\README.md"

echo.
echo ========================================
echo   DEPLOYMENT COMPLETADO
echo ========================================
echo.
echo Archivos copiados a produccion:
echo - assets/css/admin.css
echo - assets/js/admin.js
echo - assets/.htaccess
echo - Archivos de test y documentacion
echo.
echo Ahora refresca la pagina del plugin en WordPress.
pause