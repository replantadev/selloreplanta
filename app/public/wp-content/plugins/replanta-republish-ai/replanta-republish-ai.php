<?php

/**
 * Plugin Name: Replanta Republish AI
 * Description: Genera versiones para Medium (y otros) usando OpenAI al publicar un post. Incluye diagnóstico avanzado y recuperación de errores.
 * Version: 1.4.2
 * Author: Replanta
 * Changelog: v1.4.2 - Sistema de deployment mejorado con GitHub token, webhook optimizado
 */

if (!defined('ABSPATH')) exit;

// Definir constantes del plugin
define('RREPLANTA_AI_VERSION', '1.4.2');
define('RREPLANTA_AI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RREPLANTA_AI_PLUGIN_URL', plugin_dir_url(__FILE__));

// Inicializar logging
if (!function_exists('rr_ai_log')) {
    function rr_ai_log($message, $level = 'info') {
        $timestamp = current_time('Y-m-d H:i:s');
        error_log("[{$timestamp}] [Replanta Republish AI] [{$level}] {$message}");
        
        // También guardar en la base de datos para el admin
        $logs = get_option('rr_ai_debug_logs', []);
        array_unshift($logs, [
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message
        ]);
        
        // Mantener solo los últimos 100 logs
        $logs = array_slice($logs, 0, 100);
        update_option('rr_ai_debug_logs', $logs);
    }
}

// Cargar archivos principales
require_once RREPLANTA_AI_PLUGIN_DIR . 'inc/class-handler.php';
require_once RREPLANTA_AI_PLUGIN_DIR . 'inc/diagnosis.php';
require_once RREPLANTA_AI_PLUGIN_DIR . 'inc/recovery.php';
require_once RREPLANTA_AI_PLUGIN_DIR . 'inc/deploy-status.php';

// Hook principal a publicación de posts
add_action('publish_post', ['Replanta_Republish_AI', 'handle_new_post'], 10, 2);

// Activación del plugin
register_activation_hook(__FILE__, function() {
    rr_ai_log('Plugin activado - versión ' . RREPLANTA_AI_VERSION, 'info');
    update_option('rr_ai_plugin_version', RREPLANTA_AI_VERSION);
});

// Mostrar notificación de actualización
add_action('admin_notices', function() {
    $current_version = RREPLANTA_AI_VERSION;
    $last_seen_version = get_option('rr_ai_last_seen_version', '0.1');
    
    if (version_compare($last_seen_version, $current_version, '<')) {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>🎉 Replanta Republish AI actualizado a v' . $current_version . '</strong></p>';
        echo '<p>✨ Nueva versión: Debug mejorado, errores 404 corregidos, estructura optimizada. ';
        echo '<a href="' . admin_url('admin.php?page=replanta-republish-ai') . '">Ver dashboard</a></p>';
        echo '</div>';
        
        // Marcar como visto cuando visiten cualquier página del plugin
        if (isset($_GET['page']) && strpos($_GET['page'], 'replanta-republish-ai') !== false) {
            update_option('rr_ai_last_seen_version', $current_version);
        }
    }
});

// Sistema de menús mejorado
add_action('admin_menu', function () {
    // Menú principal
    add_menu_page(
        'Replanta Republish AI',
        'Republish AI',
        'edit_posts',
        'replanta-republish-ai',
        'replanta_republish_ai_dashboard_page',
        'dashicons-share',
        30
    );

    // Submenús
    add_submenu_page(
        'replanta-republish-ai',
        'Dashboard',
        'Dashboard',
        'edit_posts',
        'replanta-republish-ai',
        'replanta_republish_ai_dashboard_page'
    );

    add_submenu_page(
        'replanta-republish-ai',
        'Configuración',
        'Configuración',
        'manage_options',
        'replanta-republish-ai-config',
        'replanta_republish_ai_config_page'
    );

    add_submenu_page(
        'replanta-republish-ai',
        'Diagnóstico',
        'Diagnóstico',
        'edit_posts',
        'replanta-republish-ai-diagnosis',
        'replanta_republish_ai_diagnosis_page'
    );

    add_submenu_page(
        'replanta-republish-ai',
        'Recuperación',
        'Recuperación',
        'edit_posts',
        'replanta-republish-ai-recovery',
        'replanta_republish_ai_recovery_page'
    );

    add_submenu_page(
        'replanta-republish-ai',
        'Deploy Status',
        'Deploy Status',
        'edit_posts',
        'replanta-deploy-status',
        'replanta_deploy_status_page'
    );

    add_submenu_page(
        'replanta-republish-ai',
        'Debug Logs',
        'Debug Logs',
        'edit_posts',
        'replanta-republish-ai-debug',
        'replanta_republish_ai_debug_page'
    );
});

// Función Dashboard principal
function replanta_republish_ai_dashboard_page() {
    global $wpdb;
    
    if (!current_user_can('edit_posts')) return;

    echo '<div class="wrap">';
    echo '<h1>🧠 Replanta Republish AI - Dashboard</h1>';
    
    // Estado de configuración
    $options = get_option('replanta_republish_ai_options', []);
    $openai_configured = !empty($options['openai_api_key']);
    $auto_publish = isset($options['auto_publish']) ? $options['auto_publish'] : '1';
    
    echo '<div class="notice notice-info"><h3>📊 Estado del Sistema</h3>';
    echo '<p><strong>🤖 OpenAI API:</strong> ' . ($openai_configured ? 
        '<span style="color: green;">✅ Configurado</span>' : 
        '<span style="color: red;">❌ No configurado</span> - <a href="' . admin_url('admin.php?page=replanta-republish-ai-config') . '">Configurar</a>') . '</p>';
    echo '<p><strong>📤 Publicación automática:</strong> ' . ($auto_publish == '1' ? 
        '<span style="color: green;">✅ Activada</span>' : 
        '<span style="color: orange;">⚠️ Desactivada</span>') . '</p>';
    echo '</div>';
    
    // Estadísticas rápidas
    $total_posts = wp_count_posts()->publish;
    $sent_posts = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_rr_sent_to_ai' AND meta_value = '1'");
    
    echo '<div style="display: flex; gap: 20px; margin: 20px 0;">';
    echo '<div style="background: #f9f9f9; padding: 20px; border-radius: 8px; text-align: center;">';
    echo '<h3>📝 Posts Totales</h3><h2>' . $total_posts . '</h2>';
    echo '</div>';
    echo '<div style="background: #f9f9f9; padding: 20px; border-radius: 8px; text-align: center;">';
    echo '<h3>🚀 Enviados a Medium</h3><h2>' . ($sent_posts ?: 0) . '</h2>';
    echo '</div>';
    echo '</div>';
    
    // Acciones rápidas
    echo '<h3>🚀 Acciones Rápidas</h3>';
    echo '<p><a href="' . admin_url('admin.php?page=replanta-republish-ai-diagnosis') . '" class="button button-primary">🔍 Probar Conexión</a> ';
    echo '<a href="' . admin_url('admin.php?page=replanta-republish-ai-recovery') . '" class="button">🔄 Recuperar Posts</a> ';
    echo '<a href="' . admin_url('admin.php?page=replanta-republish-ai-debug') . '" class="button">📋 Ver Logs</a></p>';
    
    echo '</div>';
}

// Función de configuración
function replanta_republish_ai_config_page() {
    if (!current_user_can('manage_options')) return;

    echo '<div class="wrap">';
    echo '<h1>🔧 Configuración Replanta Republish AI</h1>';

    echo '<form method="post" action="options.php">';
    settings_fields('replanta_republish_ai_options_group');
    do_settings_sections('replanta-republish-ai-config');
    submit_button('Guardar configuración');
    echo '</form>';

    echo '</div>';
}

// Función Debug Logs
function replanta_republish_ai_debug_page() {
    if (!current_user_can('edit_posts')) return;

    echo '<div class="wrap">';
    echo '<h1>📋 Debug Logs</h1>';
    
    // Limpiar logs si se solicita
    if (isset($_POST['clear_logs'])) {
        delete_option('rr_ai_debug_logs');
        echo '<div class="notice notice-success"><p>Logs limpiados exitosamente.</p></div>';
    }
    
    $logs = get_option('rr_ai_debug_logs', []);
    
    echo '<form method="post" style="margin-bottom: 20px;">';
    echo '<input type="hidden" name="clear_logs" value="1">';
    submit_button('🗑️ Limpiar Logs', 'secondary', 'clear_logs');
    echo '</form>';
    
    if (empty($logs)) {
        echo '<p>No hay logs disponibles.</p>';
    } else {
        echo '<div style="background: #f1f1f1; padding: 15px; border-radius: 5px; max-height: 500px; overflow-y: auto; font-family: monospace;">';
        foreach ($logs as $log) {
            $color = $log['level'] == 'error' ? 'red' : ($log['level'] == 'warning' ? 'orange' : 'blue');
            echo '<div style="margin-bottom: 10px; border-left: 3px solid ' . $color . '; padding-left: 10px;">';
            echo '<strong>[' . $log['timestamp'] . ']</strong> <span style="color: ' . $color . ';">[' . strtoupper($log['level']) . ']</span> ' . esc_html($log['message']);
            echo '</div>';
        }
        echo '</div>';
    }
    
    echo '</div>';
}

// Configurar settings
add_action('admin_init', function () {
    register_setting('replanta_republish_ai_options_group', 'replanta_republish_ai_options', 'replanta_republish_ai_validate_options');
    
    add_settings_section('replanta_republish_ai_api', 'Configuración API', function () {
        echo '<p>Configura las APIs necesarias para el funcionamiento del plugin.</p>';
    }, 'replanta-republish-ai-config');

    add_settings_field('openai_api_key', 'OpenAI API Key', function () {
        $opts = get_option('replanta_republish_ai_options', []);
        $key = isset($opts['openai_api_key']) ? esc_attr($opts['openai_api_key']) : '';
        echo "<input type='password' name='replanta_republish_ai_options[openai_api_key]' value='$key' class='regular-text' autocomplete='off' placeholder='sk-...'>";
        echo "<p class='description'>API Key de OpenAI para generar contenido</p>";
    }, 'replanta-republish-ai-config', 'replanta_republish_ai_api');

    add_settings_field('medium_integration_token', 'Medium Integration Token', function () {
        $opts = get_option('replanta_republish_ai_options', []);
        $token = isset($opts['medium_integration_token']) ? esc_attr($opts['medium_integration_token']) : '';
        echo "<input type='password' name='replanta_republish_ai_options[medium_integration_token]' value='$token' class='regular-text' autocomplete='off'>";
        echo "<p class='description'>Token para publicar en Medium (opcional)</p>";
    }, 'replanta-republish-ai-config', 'replanta_republish_ai_api');

    add_settings_field('microservice_urls', 'URLs del Microservicio', function () {
        $opts = get_option('replanta_republish_ai_options', []);
        $urls = isset($opts['microservice_urls']) ? esc_textarea($opts['microservice_urls']) : 
            "https://replanta.net/medium-rr/replanta-medium\nhttps://replanta.dev/medium-rr/replanta-medium";
        echo "<textarea name='replanta_republish_ai_options[microservice_urls]' rows='4' class='large-text'>$urls</textarea>";
        echo "<p class='description'>URLs del microservicio (una por línea, en orden de prioridad)</p>";
    }, 'replanta-republish-ai-config', 'replanta_republish_ai_api');

    add_settings_field('auto_publish', 'Publicación Automática', function () {
        $opts = get_option('replanta_republish_ai_options', []);
        $auto = isset($opts['auto_publish']) ? $opts['auto_publish'] : '1';
        echo "<label><input type='checkbox' name='replanta_republish_ai_options[auto_publish]' value='1' " . checked($auto, '1', false) . "> Publicar automáticamente en Medium al publicar posts</label>";
    }, 'replanta-republish-ai-config', 'replanta_republish_ai_api');
});

// Función de validación
function replanta_republish_ai_validate_options($input) {
    $clean = [];
    
    if (isset($input['openai_api_key'])) {
        $clean['openai_api_key'] = sanitize_text_field($input['openai_api_key']);
    }
    
    if (isset($input['medium_integration_token'])) {
        $clean['medium_integration_token'] = sanitize_text_field($input['medium_integration_token']);
    }
    
    if (isset($input['microservice_urls'])) {
        $clean['microservice_urls'] = sanitize_textarea_field($input['microservice_urls']);
    }
    
    if (isset($input['auto_publish'])) {
        $clean['auto_publish'] = '1';
    } else {
        $clean['auto_publish'] = '0';
    }
    
    rr_ai_log('Configuración actualizada', 'info');
    
    return $clean;
}

// Agregar enlaces de acción en la página de plugins
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=replanta-republish-ai') . '">Dashboard</a>';
    $config_link = '<a href="' . admin_url('admin.php?page=replanta-republish-ai-config') . '">Configuración</a>';
    array_unshift($links, $settings_link, $config_link);
    return $links;
});

// Hook para añadir meta box en posts
add_action('add_meta_boxes', function() {
    add_meta_box(
        'replanta-republish-ai-status',
        '🧠 Replanta Republish AI Status',
        'replanta_republish_ai_meta_box',
        'post',
        'side',
        'high'
    );
});

function replanta_republish_ai_meta_box($post) {
    $sent = get_post_meta($post->ID, '_rr_sent_to_ai', true);
    $medium_url = get_post_meta($post->ID, '_rr_ai_medium_url', true);
    $ai_title = get_post_meta($post->ID, '_rr_ai_title', true);
    
    echo '<div style="padding: 10px;">';
    
    if ($sent) {
        echo '<p style="color: green;"><strong>✅ Enviado a Medium</strong></p>';
        
        if ($medium_url) {
            echo '<p><a href="' . esc_url($medium_url) . '" target="_blank">🔗 Ver en Medium</a></p>';
        }
        
        if ($ai_title) {
            echo '<p><strong>📝 Título generado:</strong><br>' . esc_html($ai_title) . '</p>';
        }
        
        echo '<button type="button" class="button" onclick="if(confirm(\'¿Reenviar este post a Medium?\')) { location.href=\'' . admin_url('admin.php?page=replanta-republish-ai-recovery&action=retry&post_id=' . $post->ID) . '\'; }">🔄 Reenviar</button>';
    } else {
        echo '<p style="color: #666;">📤 No enviado a Medium</p>';
        echo '<button type="button" class="button button-primary" onclick="if(confirm(\'¿Enviar este post a Medium ahora?\')) { location.href=\'' . admin_url('admin.php?page=replanta-republish-ai-recovery&action=send&post_id=' . $post->ID) . '\'; }">📤 Enviar ahora</button>';
    }
    
    echo '</div>';
}
