<?php
/**
 * Script de prueba para diagnosticar el microservicio de Medium
 */

// URLs a probar
$urls_to_test = [
    'https://replanta.net/medium-rr/replanta-medium',
    'https://replanta.net/api/medium',
    'https://replanta.net/medium',
    'https://replanta.net/republish/medium',
    'https://replanta.net/microservice/medium',
    'https://replanta.net/python/medium',
    'https://replanta.net/wp-json/replanta/v1/medium',
];

$test_payload = [
    'title' => 'Test Article',
    'url' => 'https://test.com/article',
    'excerpt' => 'Test excerpt',
    'content' => '<p>Test content</p>',
    'categories' => ['Technology'],
    'tags' => ['test', 'php'],
    'image' => 'https://test.com/image.jpg'
];

echo "=== DIAGNÓSTICO MICROSERVICIO MEDIUM ===\n\n";

foreach ($urls_to_test as $url) {
    echo "Probando: $url\n";
    echo str_repeat("-", 50) . "\n";
    
    // Primero probar con HEAD para ver si existe
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Status: $http_code\n";
    
    if ($http_code == 200) {
        echo "✅ URL accesible - probando POST...\n";
        
        // Probar POST con datos
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($test_payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: Replanta-Test/1.0'
        ]);
        
        $response = curl_exec($ch);
        $post_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        echo "POST Status: $post_http_code\n";
        if ($error) {
            echo "❌ Error cURL: $error\n";
        }
        
        echo "Respuesta (primeros 200 chars):\n";
        echo substr($response, 0, 200) . "\n";
        
        // Intentar decodificar JSON
        $json_data = json_decode($response, true);
        if ($json_data) {
            echo "✅ Respuesta JSON válida\n";
            if (isset($json_data['titulo'])) {
                echo "✅ Campo 'titulo' encontrado\n";
            } else {
                echo "❌ Campo 'titulo' no encontrado\n";
            }
        } else {
            echo "❌ Respuesta NO es JSON válido\n";
        }
        
    } elseif ($http_code == 404) {
        echo "❌ URL no encontrada (404)\n";
    } elseif ($http_code == 0) {
        echo "❌ Sin respuesta del servidor\n";
    } else {
        echo "⚠️ Código HTTP: $http_code\n";
    }
    
    echo "\n";
}

echo "=== FIN DEL DIAGNÓSTICO ===\n";
?>
