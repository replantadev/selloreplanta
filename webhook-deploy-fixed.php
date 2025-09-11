<?php
/**
 * Webhook para deployment directo - Estructura de plugins en raíz
 * Los plugins están directamente en el repo, no en subdirectorios
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log function
function webhook_log($message) {
    $logfile = __DIR__ . '/webhook-deploy.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logfile, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

// Headers
header('Content-Type: application/json');
webhook_log("Webhook received from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    webhook_log("ERROR: Método no permitido");
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Directorio donde están los plugins en el servidor
$repo_dir = '/home/replanta/repos/plugins';
$wp_plugins_dir = '/home/replanta/public_html/wp-content/plugins';

webhook_log("Iniciando deployment desde: $repo_dir hacia: $wp_plugins_dir");

try {
    // 1. Cambiar al directorio del repositorio
    if (!is_dir($repo_dir)) {
        throw new Exception("Repository directory not found: $repo_dir");
    }
    
    chdir($repo_dir);
    webhook_log("Changed to repository directory: $repo_dir");
    
    // 2. Hacer git pull
    $commands = [
        'git fetch origin main',
        'git reset --hard origin/main',
        'git pull origin main'
    ];
    
    $git_results = [];
    foreach ($commands as $cmd) {
        $output = [];
        $return_var = 0;
        exec($cmd . ' 2>&1', $output, $return_var);
        
        $result = [
            'command' => $cmd,
            'output' => implode("\n", $output),
            'return_code' => $return_var
        ];
        
        $git_results[] = $result;
        webhook_log("Executed: $cmd | Return: $return_var | Output: " . implode(" ", $output));
    }
    
    // 3. Copiar plugins desde el repo hacia WordPress
    $plugins_to_copy = [
        'replanta-republish-ai',
        'selloreplanta-main', 
        'dominios-reseller',
        'truspilot-replanta',
        'indice'
    ];
    
    $copy_results = [];
    foreach ($plugins_to_copy as $plugin) {
        $source = "$repo_dir/$plugin";
        $dest = "$wp_plugins_dir/$plugin";
        
        if (is_dir($source)) {
            // Usar rsync para copiar
            $rsync_cmd = "rsync -av --delete '$source/' '$dest/'";
            $output = [];
            $return_var = 0;
            exec($rsync_cmd . ' 2>&1', $output, $return_var);
            
            $copy_results[] = [
                'plugin' => $plugin,
                'command' => $rsync_cmd,
                'output' => implode("\n", $output),
                'return_code' => $return_var
            ];
            
            webhook_log("Copied plugin: $plugin | Return: $return_var");
        } else {
            webhook_log("WARNING: Plugin directory not found: $source");
        }
    }
    
    // 4. Verificar último commit
    $git_log_cmd = 'git log -1 --format="%H %s %ad" --date=iso';
    $git_output = [];
    exec($git_log_cmd, $git_output);
    
    $response = [
        'status' => 'success',
        'timestamp' => date('c'),
        'latest_commit' => $git_output[0] ?? 'Unknown',
        'git_results' => $git_results,
        'copy_results' => $copy_results
    ];
    
    webhook_log("Deployment completed successfully");
    
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'error' => $e->getMessage(),
        'timestamp' => date('c')
    ];
    webhook_log("Deployment failed: " . $e->getMessage());
    http_response_code(500);
}

// Respuesta
echo json_encode($response, JSON_PRETTY_PRINT);
webhook_log("Webhook response sent: " . $response['status']);
?>
