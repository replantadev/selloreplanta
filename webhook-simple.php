<?php
/**
 * Webhook simple para auto-deployment usando el sistema Git existente
 * Coloca este archivo en: public_html/webhook-simple.php
 */

// Configuración de seguridad
define('DEPLOY_TOKEN', 'replanta_deploy_2025_secure');
define('REPO_DIR', '/home/replanta/repos/plugins');
define('DEPLOY_SCRIPT', '/home/replanta/repos/plugins/deploy.sh');
define('LOG_FILE', '/home/replanta/deployment.log');

// Headers de seguridad
header('Content-Type: application/json');

function log_message($message) {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message\n";
    file_put_contents(LOG_FILE, $log_entry, FILE_APPEND | LOCK_EX);
}

function send_response($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('c')
    ];
    
    if ($data) {
        $response['data'] = $data;
    }
    
    log_message($message);
    echo json_encode($response);
    exit;
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(false, 'Solo se permite método POST');
}

// Verificar token de seguridad
$token = $_GET['token'] ?? '';
if ($token !== DEPLOY_TOKEN) {
    log_message('Intento de acceso no autorizado desde: ' . $_SERVER['REMOTE_ADDR']);
    send_response(false, 'Token de acceso inválido');
}

log_message('Webhook activado desde: ' . $_SERVER['REMOTE_ADDR']);

// Verificar que el directorio del repo existe
if (!is_dir(REPO_DIR)) {
    send_response(false, 'Directorio de repositorio no encontrado: ' . REPO_DIR);
}

// Verificar que el script de deploy existe
if (!file_exists(DEPLOY_SCRIPT)) {
    send_response(false, 'Script de deploy no encontrado: ' . DEPLOY_SCRIPT);
}

log_message('Iniciando deployment...');

// Cambiar al directorio del repo y ejecutar deployment
$deploy_cmd = "cd " . REPO_DIR . " && bash deploy.sh 2>&1";
exec($deploy_cmd, $output, $return_code);

$output_text = implode("\n", $output);

if ($return_code === 0) {
    log_message('Deployment completado exitosamente');
    send_response(true, 'Deployment completado exitosamente', [
        'output' => $output_text,
        'return_code' => $return_code
    ]);
} else {
    log_message('Error en deployment: ' . $output_text);
    send_response(false, 'Error en deployment', [
        'output' => $output_text,
        'return_code' => $return_code
    ]);
}
?>
