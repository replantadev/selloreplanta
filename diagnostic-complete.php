<?php
/**
 * Script completo de diagnóstico del microservicio
 */

echo "🔧 DIAGNÓSTICO COMPLETO - Microservicio Republish AI\n";
echo "====================================================\n\n";

// URLs a probar con diferentes variaciones
$test_urls = [
    // Endpoint principal
    'https://replanta.net/medium-rr/',
    'https://replanta.net/medium-rr/ping',
    'https://replanta.net/medium-rr/replanta-medium',
    
    // Sin barra final
    'https://replanta.net/medium-rr',
    
    // Con índice
    'https://replanta.net/medium-rr/index.php',
    'https://replanta.net/medium-rr/app.py',
    
    // Otras variaciones
    'https://replanta.net/app/medium-rr/',
    'https://replanta.net/apps/medium-rr/',
    
    // Subdominios
    'https://medium-rr.replanta.net/',
    'https://api.replanta.net/medium-rr/',
    
    // Puerto directo (poco probable pero vale la pena probar)
    'https://replanta.net:5000/',
    'https://replanta.net:8000/medium-rr/',
];

function test_detailed_url($url) {
    echo "🔗 Probando: $url\n";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'Replanta-Diagnostic/1.0',
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => false,
        CURLOPT_VERBOSE => false
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $redirect_url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    echo "   📊 Status: $http_code\n";
    echo "   ⏱️  Tiempo: " . round($total_time * 1000) . "ms\n";
    echo "   📄 Content-Type: " . ($content_type ?: 'N/A') . "\n";
    
    if ($redirect_url) {
        echo "   🔄 Redirige a: $redirect_url\n";
    }
    
    if ($error) {
        echo "   ❌ Error cURL: $error\n";
    } else {
        // Extraer solo el body (sin headers)
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($response, $header_size);
        
        if (strlen($body) > 0) {
            echo "   📝 Response preview: " . substr(strip_tags($body), 0, 100) . "...\n";
            
            // Buscar indicadores de Flask/Python
            if (stripos($body, 'flask') !== false || stripos($body, 'python') !== false) {
                echo "   🐍 ¡Posible aplicación Python detectada!\n";
            }
            
            // Buscar JSON
            if (stripos($content_type, 'json') !== false || (substr(trim($body), 0, 1) === '{')) {
                echo "   📋 ¡Respuesta JSON detectada!\n";
            }
        }
    }
    
    // Determinar el estado
    if ($http_code === 200) {
        echo "   ✅ FUNCIONANDO\n";
        return true;
    } elseif ($http_code === 404) {
        echo "   ❌ NO ENCONTRADO\n";
    } elseif ($http_code === 500) {
        echo "   ⚠️  ERROR INTERNO DEL SERVIDOR\n";
    } elseif ($http_code === 0) {
        echo "   💀 NO RESPONDE / TIMEOUT\n";
    } else {
        echo "   ⚠️  CÓDIGO INESPERADO\n";
    }
    
    echo "\n";
    return false;
}

echo "🌐 Probando URLs del microservicio:\n";
echo "===================================\n";

$working_urls = [];
foreach ($test_urls as $url) {
    if (test_detailed_url($url)) {
        $working_urls[] = $url;
    }
}

echo "\n📋 RESUMEN:\n";
echo "============\n";

if (empty($working_urls)) {
    echo "❌ NINGUNA URL ESTÁ FUNCIONANDO\n\n";
    echo "🔧 POSIBLES CAUSAS:\n";
    echo "   1. El microservicio Python no está ejecutándose\n";
    echo "   2. La configuración del servidor web (Nginx/Apache) no está correcta\n";
    echo "   3. El firewall está bloqueando las conexiones\n";
    echo "   4. La aplicación está en un directorio diferente\n";
    echo "   5. Hay un problema con el archivo .htaccess o la configuración de URL rewriting\n\n";
    
    echo "✅ ACCIONES RECOMENDADAS:\n";
    echo "   1. Verificar que la aplicación Python esté corriendo en el servidor\n";
    echo "   2. Revisar la configuración del servidor web\n";
    echo "   3. Comprobar los logs del servidor para errores\n";
    echo "   4. Verificar que el directorio public_html/medium-rr contiene app.py\n";
    echo "   5. Probar acceso directo al servidor con SSH y curl local\n";
} else {
    echo "✅ URLs FUNCIONANDO:\n";
    foreach ($working_urls as $url) {
        echo "   - $url\n";
    }
}

echo "\n🔍 SIGUIENTE PASO:\n";
echo "   Actualiza el plugin con las URLs que funcionen.\n";
echo "   Si ninguna funciona, necesitas configurar el servidor.\n";
?>
