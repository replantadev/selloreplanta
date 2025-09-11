<?php
// deploy-plugins.php - Colocar en la raíz del sitio web
// URL: https://replanta.net/deploy-plugins.php

// Verificación básica de seguridad
$allowed_ips = ['tu.ip.local.aqui']; // Agregar tu IP aquí
$client_ip = $_SERVER['REMOTE_ADDR'];

if (!in_array($client_ip, $allowed_ips) && $client_ip !== '127.0.0.1') {
    http_response_code(403);
    die('Acceso denegado');
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if ($input['action'] !== 'deploy') {
    http_response_code(400);
    echo json_encode(['error' => 'Acción inválida']);
    exit;
}

// Ejecutar el script de deploy
$deploy_script = '/home/replanta/deploy-plugins.sh';

if (!file_exists($deploy_script)) {
    http_response_code(500);
    echo json_encode(['error' => 'Script de deploy no encontrado']);
    exit;
}

// Hacer el script ejecutable
chmod($deploy_script, 0755);

// Ejecutar deploy en background
$output = [];
$return_var = 0;
exec("bash $deploy_script 2>&1", $output, $return_var);

if ($return_var === 0) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Deploy ejecutado correctamente',
        'output' => implode("\n", $output)
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error en el deploy',
        'output' => implode("\n", $output)
    ]);
}
?>
