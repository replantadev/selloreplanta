@echo off
echo === ACTUALIZANDO WEBHOOK DEL SERVIDOR ===
echo.
echo Subiendo webhook mejorado con token de GitHub...

curl -X POST "https://replanta.net/webhook-deploy.php" ^
     -F "action=update_webhook" ^
     -F "webhook_file=@webhook-deploy-improved.php" ^
     -H "Authorization: Bearer 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"

echo.
echo Si eso no funciona, copia manualmente el archivo webhook-deploy-improved.php
echo al directorio raiz de replanta.net
echo.
pause
