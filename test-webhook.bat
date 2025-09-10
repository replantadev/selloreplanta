@echo off
echo === PRUEBA DEL WEBHOOK ===
echo Probando la conexion con el webhook de deployment...

set WEBHOOK_URL=https://replanta.net/webhook-simple.php?token=replanta_deploy_2025_secure

echo.
echo Enviando peticion de prueba al webhook...

curl -X POST "%WEBHOOK_URL%" ^
     -H "Content-Type: application/json" ^
     -d "{\"action\":\"test\",\"plugins\":[\"replanta-republish-ai\"]}" ^
     -w "\nStatus: %%{http_code}\nTime: %%{time_total}s\n"

if errorlevel 1 (
    echo.
    echo ERROR: No se pudo conectar al webhook
    echo Verifica que:
    echo 1. El archivo webhook-deploy.php este subido a replanta.dev
    echo 2. El token sea correcto
    echo 3. PHP este habilitado en el servidor
) else (
    echo.
    echo SUCCESS: Webhook respondio correctamente
    echo Revisa la respuesta de arriba para mas detalles
)

echo.
pause
