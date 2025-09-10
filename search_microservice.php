<?php
/**
 * Script para buscar el microservicio en diferentes puertos y subdominios
 */

$base_domains = [
    'https://replanta.net',
    'https://api.replanta.net',
    'https://microservice.replanta.net',
    'https://python.replanta.net',
    'https://medium.replanta.net',
];

$ports = ['', ':3000', ':5000', ':8000', ':8080', ':9000'];
$paths = [
    '/medium-rr/replanta-medium',
    '/api/medium',
    '/medium',
    '/replanta-medium',
    '/microservice/medium',
    '/',
    '/health',
    '/status'
];

echo "=== BÚSQUEDA EXHAUSTIVA DEL MICROSERVICIO ===\n\n";

foreach ($base_domains as $domain) {
    foreach ($ports as $port) {
        $base_url = $domain . $port;
        
        echo "Probando dominio: $base_url\n";
        echo str_repeat("-", 40) . "\n";
        
        foreach ($paths as $path) {
            $full_url = $base_url . $path;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $full_url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            
            curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($http_code == 200) {
                echo "✅ ENCONTRADO: $full_url (HTTP 200)\n";
            } elseif ($http_code > 0 && $http_code != 404) {
                echo "⚠️ RESPONDE: $full_url (HTTP $http_code)\n";
            }
            // Silenciar 404s para no llenar la pantalla
        }
        echo "\n";
    }
}

echo "=== FIN DE LA BÚSQUEDA ===\n";
?>
