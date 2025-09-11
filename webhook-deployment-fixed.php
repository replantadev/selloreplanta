<?php
// webhook-deployment-fixed.php - Colocar en /home/replanta/replanta.net/
// Este webhook SÍ actualiza el repositorio y ejecuta el deploy

header('Content-Type: application/json');

$log_file = '/home/replanta/webhook-deployment.log';

function log_message($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// Verificar que es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Solo POST permitido']);
    exit;
}

// Log del inicio
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
log_message("🚀 Webhook activado desde: $client_ip");

// Directorio del repositorio
$repo_dir = '/home/replanta/repos/plugins';
$deploy_script = '/home/replanta/deploy.sh';

try {
    // Cambiar al directorio del repositorio
    if (!is_dir($repo_dir)) {
        throw new Exception("Directorio del repositorio no existe: $repo_dir");
    }
    
    chdir($repo_dir);
    log_message("📂 Cambiado a directorio: $repo_dir");
    
    // Hacer git pull usando el repositorio público (sin credenciales)
    log_message("📥 Iniciando git pull...");
    
    // Reset hard para evitar conflictos
    exec('git reset --hard HEAD 2>&1', $reset_output, $reset_code);
    log_message("🔄 Git reset: " . implode(' ', $reset_output));
    
    // Pull de la rama main (que es donde pusheas)
    exec('git pull origin main 2>&1', $pull_output, $pull_code);
    log_message("📥 Git pull output: " . implode(' ', $pull_output));
    
    if ($pull_code !== 0) {
        // Si falla, intentar configurar origin
        exec('git remote set-url origin https://github.com/replantadev/plugins.git 2>&1', $remote_output);
        log_message("🔧 Reconfigurado remote: " . implode(' ', $remote_output));
        
        // Intentar pull nuevamente
        exec('git pull origin main 2>&1', $pull_output2, $pull_code2);
        log_message("📥 Git pull retry: " . implode(' ', $pull_output2));
        
        if ($pull_code2 !== 0) {
            throw new Exception("Git pull falló: " . implode(' ', $pull_output2));
        }
    }
    
    log_message("✅ Git pull exitoso");
    
    // Verificar que el script de deploy existe
    if (!file_exists($deploy_script)) {
        throw new Exception("Script de deploy no existe: $deploy_script");
    }
    
    // Hacer ejecutable el script
    chmod($deploy_script, 0755);
    
    // Ejecutar el script de deployment
    log_message("🚀 Ejecutando deployment...");
    exec("bash $deploy_script 2>&1", $deploy_output, $deploy_code);
    
    $deploy_result = implode("\n", $deploy_output);
    log_message("📋 Deploy output: $deploy_result");
    
    if ($deploy_code === 0) {
        log_message("✅ Deployment completado exitosamente");
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Deployment completado',
            'git_pull' => implode(' ', $pull_output),
            'deploy_output' => $deploy_result
        ]);
    } else {
        throw new Exception("Deploy script falló con código: $deploy_code");
    }
    
} catch (Exception $e) {
    log_message("❌ Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
