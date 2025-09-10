<?php
/**
 * Webhook para auto-deployment desde GitHub a cPanel
 * Coloca este archivo en: public_html/webhook-deploy.php
 */

// Configuración de seguridad
define('DEPLOY_TOKEN', 'replanta_deploy_2025_secure'); // Token que debe coincidir con el script
define('PLUGINS_PATH', '/home/replanta/public_html/wp-content/plugins/');
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

// Procesar request
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    send_response(false, 'Datos JSON inválidos');
}

// Lista de plugins a actualizar
$plugins_to_update = [
    'replanta-republish-ai' => [
        'repo' => 'https://github.com/replantadev/plugins.git',
        'branch' => 'main',
        'subdir' => 'replanta-republish-ai'
    ],
    'selloreplanta-main' => [
        'repo' => 'https://github.com/replantadev/plugins.git', 
        'branch' => 'main',
        'subdir' => 'selloreplanta-main'
    ],
    'dominios-reseller' => [
        'repo' => 'https://github.com/replantadev/plugins.git',
        'branch' => 'main', 
        'subdir' => 'dominios-reseller'
    ],
    'truspilot-replanta' => [
        'repo' => 'https://github.com/replantadev/plugins.git',
        'branch' => 'main',
        'subdir' => 'truspilot-replanta'
    ]
];

$results = [];
$temp_dir = PLUGINS_PATH . 'temp_deploy_' . time();

foreach ($plugins_to_update as $plugin => $config) {
    $plugin_path = PLUGINS_PATH . $plugin;
    
    log_message("Actualizando plugin: $plugin");
    
    try {
        // Crear directorio temporal
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }
        
        // Clonar repositorio en temporal
        $clone_cmd = "cd $temp_dir && git clone {$config['repo']} repo_temp 2>&1";
        exec($clone_cmd, $clone_output, $clone_return);
        
        if ($clone_return !== 0) {
            throw new Exception('Error al clonar repositorio: ' . implode(' ', $clone_output));
        }
        
        $source_path = "$temp_dir/repo_temp/{$config['subdir']}";
        
        if (!is_dir($source_path)) {
            throw new Exception("Subdirectorio {$config['subdir']} no encontrado en el repositorio");
        }
        
        // Backup del plugin actual si existe
        if (is_dir($plugin_path)) {
            $backup_path = $plugin_path . '_backup_' . date('Y-m-d_H-i-s');
            $backup_cmd = "mv $plugin_path $backup_path";
            exec($backup_cmd);
            log_message("Backup creado: $backup_path");
        }
        
        // Copiar archivos nuevos
        $copy_cmd = "cp -r $source_path $plugin_path 2>&1";
        exec($copy_cmd, $copy_output, $copy_return);
        
        if ($copy_return !== 0) {
            throw new Exception('Error al copiar archivos: ' . implode(' ', $copy_output));
        }
        
        // Verificar que se copiaron los archivos principales
        $main_file = $plugin_path . '/' . $plugin . '.php';
        if (!file_exists($main_file)) {
            throw new Exception("Archivo principal $main_file no encontrado después de la copia");
        }
        
        $results[$plugin] = 'Actualizado exitosamente';
        log_message("Plugin $plugin actualizado exitosamente");
        
    } catch (Exception $e) {
        $results[$plugin] = 'Error: ' . $e->getMessage();
        log_message("Error al actualizar $plugin: " . $e->getMessage());
    }
}

// Limpiar directorio temporal
if (is_dir($temp_dir)) {
    exec("rm -rf $temp_dir");
}

// Limpiar cache de WordPress si es posible
if (file_exists(PLUGINS_PATH . '../../../wp-config.php')) {
    $cache_cmd = "cd " . dirname(PLUGINS_PATH) . "/../../ && php -r \"if(function_exists('wp_cache_flush')){wp_cache_flush();}\"";
    exec($cache_cmd);
    log_message('Cache de WordPress limpiado');
}

send_response(true, 'Deployment completado', $results);
?>
