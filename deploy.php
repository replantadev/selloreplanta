<?php
// deploy.php - UN SOLO archivo para deployment
// Colocar en: /home/replanta/replanta.net/deploy.php

header('Content-Type: text/plain');

$repo_dir = '/home/replanta/repos/plugins';
$wp_plugins = '/home/replanta/replanta.net/wp-content/plugins';
$log_file = '/home/replanta/deploy.log';

function log_msg($msg) {
    global $log_file;
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - $msg\n", FILE_APPEND);
    echo "$msg\n";
}

log_msg("🚀 Iniciando deployment...");

// 1. Configurar repositorio si no existe
if (!is_dir("$repo_dir/.git")) {
    log_msg("📁 Repositorio no existe, clonando...");
    $parent_dir = dirname($repo_dir);
    if (!is_dir($parent_dir)) {
        mkdir($parent_dir, 0755, true);
    }
    exec("cd '$parent_dir' && git clone https://github.com/replantadev/plugins.git plugins 2>&1", $clone_out, $clone_code);
    if ($clone_code !== 0) {
        log_msg("❌ Error clonando: " . implode(' ', $clone_out));
        exit(1);
    }
    log_msg("✅ Repositorio clonado correctamente");
}

// 2. Actualizar repositorio
log_msg("📥 Actualizando repositorio...");
chdir($repo_dir);
exec('git reset --hard HEAD 2>&1', $reset_out);
exec('git pull origin main 2>&1', $pull_out, $pull_code);

if ($pull_code !== 0) {
    log_msg("❌ Error en git pull: " . implode(' ', $pull_out));
    exit(1);
}
log_msg("✅ Repositorio actualizado");

// 2. Sincronizar plugins
$plugins = ['replanta-republish-ai', 'dniwoo', 'dominios-reseller', 'selloreplanta-main', 'truspilot-replanta', 'indice'];

foreach ($plugins as $plugin) {
    if (is_dir("$repo_dir/$plugin")) {
        log_msg("🔄 Sincronizando $plugin SEGURO...");
        
        // VERIFICACIÓN DE SEGURIDAD: Solo actualizar archivos específicos
        $target_dir = "$wp_plugins/$plugin";
        
        // Crear directorio si no existe
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
            log_msg("📁 Directorio $plugin creado");
        }
        
        // Sincronizar SIN --delete para proteger archivos extra
        exec("rsync -av '$repo_dir/$plugin/' '$target_dir/' 2>&1", $sync_out);
        log_msg("✅ $plugin sincronizado SEGURO (sin borrar archivos extras)");
        
        // Log de lo que se sincronizó
        foreach ($sync_out as $line) {
            if (strpos($line, '.php') !== false || strpos($line, '.js') !== false || strpos($line, '.css') !== false) {
                log_msg("   → $line");
            }
        }
    }
}

// 3. Verificar versiones
foreach ($plugins as $plugin) {
    $main_file = "$wp_plugins/$plugin/$plugin.php";
    if (file_exists($main_file)) {
        $content = file_get_contents($main_file);
        if (preg_match('/Version:\s*([0-9.]+)/', $content, $matches)) {
            log_msg("📋 $plugin: v{$matches[1]}");
        }
    }
}

log_msg("🎉 Deployment completado!");
?>
