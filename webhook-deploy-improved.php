<?php
/**
 * Webhook mejorado para deployment directo a cPanel con GitHub token
 * Ejecuta git pull forzado cuando recibe la señal
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// GitHub token para autenticación
define('GITHUB_TOKEN', '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');

// Log function
function webhook_log($message) {
    $logfile = __DIR__ . '/webhook-deploy.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logfile, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

// Verificar si es consulta de status
if (isset($_GET['status']) && $_GET['status'] === 'check') {
    $last_deployment = __DIR__ . '/last_deployment.json';
    header('Content-Type: application/json');
    if (file_exists($last_deployment)) {
        echo file_get_contents($last_deployment);
    } else {
        echo json_encode(['status' => 'no_deployments', 'message' => 'No deployments found']);
    }
    exit;
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    webhook_log("ERROR: Método no permitido - " . $_SERVER['REQUEST_METHOD']);
    die('Method not allowed');
}

// Obtener payload
$payload = json_decode(file_get_contents('php://input'), true);
webhook_log("Payload recibido: " . json_encode($payload));

// Verificar payload
if (!$payload || !isset($payload['repository'])) {
    http_response_code(400);
    webhook_log("ERROR: Payload inválido");
    die('Invalid payload');
}

// Directorio del repositorio
$repo_dir = '/home/replanta/repos/plugins';
webhook_log("Directorio del repositorio: $repo_dir");

if (!is_dir($repo_dir)) {
    http_response_code(500);
    webhook_log("ERROR: Directorio no existe - $repo_dir");
    die('Repository directory not found');
}

// Cambiar al directorio
chdir($repo_dir);
webhook_log("Cambiado al directorio: " . getcwd());

// Comandos de deployment
$commands = [
    'git fetch origin 2>&1',
    'git reset --hard origin/main 2>&1',
    'git pull origin main 2>&1',
    'chmod -R 755 . 2>&1'
];

$output = [];
$success = true;

foreach ($commands as $cmd) {
    webhook_log("Ejecutando: $cmd");
    exec($cmd, $cmd_output, $return_code);
    
    $output[] = [
        'command' => $cmd,
        'output' => $cmd_output,
        'return_code' => $return_code
    ];
    
    webhook_log("Salida: " . implode("\n", $cmd_output));
    webhook_log("Código de retorno: $return_code");
    
    if ($return_code !== 0) {
        $success = false;
        webhook_log("ERROR en comando: $cmd");
    }
}

// Verificar archivos actualizados
$plugin_file = $repo_dir . '/replanta-republish-ai/replanta-republish-ai.php';
if (file_exists($plugin_file)) {
    $content = file_get_contents($plugin_file);
    if (strpos($content, '1.4.1') !== false) {
        webhook_log("✅ Archivo plugin actualizado correctamente a v1.4.1");
    } else {
        webhook_log("❌ Archivo plugin NO contiene v1.4.1");
        $success = false;
    }
} else {
    webhook_log("❌ Archivo plugin no encontrado: $plugin_file");
    $success = false;
}

// Respuesta
http_response_code($success ? 200 : 500);
header('Content-Type: application/json');

$response = [
    'success' => $success,
    'timestamp' => date('Y-m-d H:i:s'),
    'repository' => $payload['repository']['name'] ?? 'unknown',
    'commands_executed' => count($commands),
    'output' => $output
];

webhook_log("Respuesta: " . json_encode($response));
echo json_encode($response, JSON_PRETTY_PRINT);
?>
