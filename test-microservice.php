<?php
/**
 * Script de prueba para verificar el microservicio de Medium/Dev.to
 */

echo "🔧 Test del Microservicio Replanta Republish AI\n";
echo "===============================================\n\n";

// URLs a probar
$urls_to_test = [
    'https://replanta.dev/medium-rr/ping',
    'https://replanta.dev/ping',
    'https://replanta.dev/medium-rr/replanta-medium',
    'https://replanta.net/medium-rr/ping',
    'https://replanta.net/ping',
    'https://replanta.net/medium-rr/replanta-medium'
];

function test_url($url, $method = 'GET', $payload = null) {
    echo "🔗 Probando: $url ($method)\n";
    
    $args = [
        'method' => $method,
        'timeout' => 15,
        'sslverify' => false,
        'headers' => [
            'User-Agent' => 'Replanta-Test/1.0'
        ]
    ];
    
    if ($payload && $method === 'POST') {
        $args['headers']['Content-Type'] = 'application/json';
        $args['body'] = json_encode($payload);
    }
    
    $response = wp_remote_request($url, $args);
    
    if (is_wp_error($response)) {
        echo "   ❌ Error: " . $response->get_error_message() . "\n";
        return false;
    }
    
    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    echo "   📊 Status: $code\n";
    echo "   📝 Response: " . substr($body, 0, 200) . (strlen($body) > 200 ? '...' : '') . "\n";
    
    if ($code === 200) {
        echo "   ✅ OK!\n";
        return true;
    } else {
        echo "   ⚠️  Código no esperado\n";
        return false;
    }
}

// Test de ping primero
echo "🏓 Probando endpoints de ping:\n";
echo "==============================\n";
foreach ($urls_to_test as $url) {
    if (strpos($url, 'ping') !== false) {
        test_url($url);
        echo "\n";
    }
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

foreach ($urls_to_test as $url) {
    if (strpos($url, 'replanta-medium') !== false) {
        test_url($url, 'POST', $test_payload);
        echo "\n";
    }
}

echo "\n📋 Resumen:\n";
echo "==========\n";
echo "Si ves '✅ OK!' en algún endpoint, ese microservicio está funcionando.\n";
echo "Si todos muestran errores, el microservicio puede no estar corriendo.\n";
echo "Revisa los logs del servidor donde está alojado el microservicio Python.\n";
?>
