<?php

/**
 * Plugin Name: Replanta Republish AI
 * Description: Genera versiones para Medium (y otros) usando OpenAI al publicar un post.
 * Version: 0.1
 * Author: Replanta
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'inc/class-handler.php';
require_once plugin_dir_path(__FILE__) . 'admin-page.php';
// Hook a publicación de posts
add_action('publish_post', ['Replanta_Republish_AI', 'handle_new_post'], 10, 2);

// Hook al menú admin para agregar submenú de configuración
add_action('admin_menu', function () {
    add_menu_page(
        'Replanta Republish AI',
        'Republish AI',
        'manage_options',
        'replanta-republish-ai',
        'replanta_republish_ai_admin_page'
    );

    // Añadir submenu para configuración
    add_submenu_page(
        'replanta-republish-ai',
        'Configuración',
        'Configuración',
        'manage_options',
        'replanta-republish-ai-config',
        'replanta_republish_ai_config_page'
    );
});

// Página de configuración
function replanta_republish_ai_config_page() {
    if (!current_user_can('manage_options')) return;

    echo '<div class="wrap">';
    echo '<h1>🔧 Configuración Replanta Republish AI</h1>';

    // Manejar test de conexión WHM
    if (isset($_POST['test_whm_connection'])) {
        $options = get_option('replanta_republish_ai_options');
        $token = $options['whm_token'] ?? '';
        
        if (empty($token)) {
            echo '<div class="notice notice-error"><p>❌ Error: Debes configurar primero el API Token de WHM.</p></div>';
        } else {
            echo '<div class="notice notice-info"><p>🔄 Probando conexión con WHM...</p></div>';
            $test_result = test_republish_whm_connection($token);
            
            if ($test_result['success']) {
                echo '<div class="notice notice-success"><p>✅ Conexión exitosa! Se encontraron ' . $test_result['count'] . ' cuentas en WHM.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>❌ Error de conexión: ' . esc_html($test_result['error']) . '</p></div>';
            }
        }
    }

    echo '<form method="post" action="options.php">';
    settings_fields('replanta_republish_ai_options_group');
    do_settings_sections('replanta-republish-ai-config');
    submit_button('Guardar configuración');
    echo '</form>';

    // Botón de prueba de conexión
    echo '<form method="post" style="margin-top: 10px;">';
    echo '<input type="hidden" name="test_whm_connection" value="1">';
    submit_button('🔧 Probar Conexión WHM', 'secondary', 'test_connection');
    echo '</form>';

    echo '</div>';
}

// Configurar settings
add_action('admin_init', function () {
    register_setting('replanta_republish_ai_options_group', 'replanta_republish_ai_options', 'replanta_republish_ai_validate_options');
    
    add_settings_section('replanta_republish_ai_whm', 'Configuración WHM', function () {
        echo '<p>Configura el acceso a WHM para verificar dominios antes de republicar contenido.</p>';
        echo '<p><strong>Servidor WHM:</strong> 77.95.113.38:2087 (IP directa)</p>';
        echo '<p><em>El token debe tener permisos para listar cuentas (listaccts).</em></p>';
    }, 'replanta-republish-ai-config');

    add_settings_field('whm_token', 'API Token WHM', function () {
        $opts = get_option('replanta_republish_ai_options');
        $token = isset($opts['whm_token']) ? esc_attr($opts['whm_token']) : '';
        echo "<input type='password' name='replanta_republish_ai_options[whm_token]' value='$token' class='regular-text' autocomplete='off' placeholder='Introduce tu token WHM aquí...'>";
        echo "<p class='description'>Token de autenticación para acceder a la API de WHM</p>";
    }, 'replanta-republish-ai-config', 'replanta_republish_ai_whm');

    add_settings_field('openai_api_key', 'OpenAI API Key', function () {
        $opts = get_option('replanta_republish_ai_options');
        $api_key = isset($opts['openai_api_key']) ? esc_attr($opts['openai_api_key']) : '';
        echo "<input type='password' name='replanta_republish_ai_options[openai_api_key]' value='$api_key' class='regular-text' autocomplete='off' placeholder='sk-...'>";
        echo "<p class='description'>Clave API de OpenAI para generar contenido</p>";
    }, 'replanta-republish-ai-config', 'replanta_republish_ai_whm');
});

function replanta_republish_ai_validate_options($input) {
    return [
        'whm_token' => sanitize_text_field($input['whm_token'] ?? ''),
        'openai_api_key' => sanitize_text_field($input['openai_api_key'] ?? '')
    ];
}

// Función para probar conexión WHM
function test_republish_whm_connection($token) {
    if (empty($token)) {
        return [
            'success' => false,
            'error' => 'Token WHM no configurado'
        ];
    }

    // Usar la IP directa para evitar problemas con Cloudflare
    $whm_url = 'https://77.95.113.38:2087/json-api/listaccts?api.version=1';

    $response = wp_remote_get($whm_url, [
        'headers' => [
            'Authorization' => 'whm replanta:' . $token,
            'Accept' => 'application/json',
            'User-Agent' => 'WordPress/Replanta-Plugin'
        ],
        'timeout' => 30,
        'sslverify' => false,
        'blocking' => true
    ]);

    if (is_wp_error($response)) {
        $error_msg = $response->get_error_message();
        error_log('[Replanta Republish AI] WP Error: ' . $error_msg);
        return [
            'success' => false,
            'error' => 'Error de conexión: ' . $error_msg
        ];
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    error_log('[Replanta Republish AI] Test Connection - Status: ' . $status_code . ' Body: ' . substr($body, 0, 200));

    if ($status_code !== 200) {
        return [
            'success' => false,
            'error' => 'Código de respuesta HTTP: ' . $status_code . ' - ' . substr($body, 0, 100)
        ];
    }

    $data = json_decode($body, true);

    if (!$data || !isset($data['data']['acct'])) {
        return [
            'success' => false,
            'error' => 'Respuesta inválida del servidor WHM'
        ];
    }

    return [
        'success' => true,
        'count' => count($data['data']['acct']),
        'message' => 'Conexión exitosa con WHM'
    ];
}

add_action('add_meta_boxes', function () {
    add_meta_box('rr_ai_meta', '🧠 Republish AI Info', function ($post) {
        echo '<p><strong>📝 Título generado:</strong><br>' . esc_html(get_post_meta($post->ID, '_rr_ai_title', true)) . '</p>';
        echo '<p><strong>📌 Resumen:</strong><br>' . esc_html(get_post_meta($post->ID, '_rr_ai_summary', true)) . '</p>';
        echo '<p><strong>🔗 Medium:</strong><br>';
        echo '<a href="' . esc_url(get_post_meta($post->ID, '_rr_ai_medium_url', true)) . '" target="_blank">Ver publicación</a></p>';
        echo '<p><strong>📄 Tags:</strong> ' . esc_html(get_post_meta($post->ID, '_rr_ai_tags', true)) . '</p>';
        echo '<p><strong>📂 Categoría:</strong> ' . esc_html(implode(', ', (array)get_post_meta($post->ID, '_rr_ai_category', true))) . '</p>';
        echo '<hr>';
        echo '<p><strong>🔗 Dev.to:</strong><br>';
        $devto_url = get_post_meta($post->ID, '_rr_ai_devto_url', true);
        if ($devto_url) {
            echo '<a href="' . esc_url($devto_url) . '" target="_blank">Ver publicación en Dev.to</a></p>';
        } else {
            echo '<span style="color:gray;">No enviado</span></p>';
        }
    }, 'post', 'side', 'high');
});

add_filter('manage_posts_columns', function ($columns) {
    $columns['rr_sent_ai'] = 'Republish AI';
    return $columns;
});

add_action('manage_posts_custom_column', function ($column, $post_id) {
    if ($column === 'rr_sent_ai') {
        if ($column === 'rr_sent_ai') {
            $medium = get_post_meta($post_id, '_rr_ai_medium_url', true);
            $devto  = get_post_meta($post_id, '_rr_ai_devto_url', true);

            if ($medium || $devto) {
                if ($medium) echo '<a href="' . esc_url($medium) . '" target="_blank">Medium</a><br>';
                if ($devto)  echo '<a href="' . esc_url($devto)  . '" target="_blank">Dev.to</a>';
            } else {
                echo '<span style="color:gray;">No</span>';
            }
        }
    }
}, 10, 2);
