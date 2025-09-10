<?php
/**
 * Script de prueba independiente para verificar el microservicio
 */

echo "🔧 Test del Microservicio Replanta Republish AI\n";
echo "===============================================\n\n";

function test_url($url, $method = 'GET', $payload = null) {
    echo "🔗 Probando: $url ($method)\n";
    
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'Replanta-Test/1.0',
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CUSTOMREQUEST => $method
    ]);
    
    if ($payload && $method === 'POST') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($payload))
        ]);
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        echo "   ❌ Error cURL: $error\n";
        return false;
    }
    
    echo "   📊 Status: $http_code\n";
    echo "   📝 Response: " . substr($response, 0, 200) . (strlen($response) > 200 ? '...' : '') . "\n";
    
    if ($http_code === 200) {
        echo "   ✅ OK!\n";
        return true;
    } else {
        echo "   ⚠️  Código no esperado\n";
        return false;
    }
}

// URLs a probar
$urls_to_test = [
    'https://replanta.dev/medium-rr/ping',
    'https://replanta.dev/ping',
    'https://replanta.net/medium-rr/ping',
    'https://replanta.net/ping'
];

// Test de ping primero
echo "🏓 Probando endpoints de ping:\n";
echo "==============================\n";
foreach ($urls_to_test as $url) {
    test_url($url);
    echo "\n";
}

echo "\n🧪 Probando endpoints de contenido:\n";
echo "==================================\n";

// Payload de prueba para Medium
$test_payload = [
    'title' => 'Test Article from WordPress',
    'url' => 'https://example.com/test-post',
    'excerpt' => 'This is a test excerpt for the article.',
    'content' => '<p>This is a test content for the article. It contains some <strong>bold text</strong> and <em>italic text</em>.</p>',
    'categories' => ['Technology', 'Web Development'],
    'tags' => ['test', 'wordpress', 'medium'],
    'image' => 'https://example.com/test-image.jpg',
    'publish' => false
];

$content_urls = [
    'https://replanta.dev/medium-rr/replanta-medium',
    'https://replanta.net/medium-rr/replanta-medium',
    'https://replanta.dev/replanta-medium',
    'https://replanta.net/replanta-medium'
];

foreach ($content_urls as $url) {
    test_url($url, 'POST', $test_payload);
    echo "\n";
}

echo "\n📋 Resumen:\n";
echo "==========\n";
echo "Si ves '✅ OK!' en algún endpoint, ese microservicio está funcionando.\n";
echo "Si todos muestran errores, el microservicio puede no estar corriendo.\n";
echo "Revisa los logs del servidor donde está alojado el microservicio Python.\n";
?>
