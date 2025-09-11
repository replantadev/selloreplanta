<?php
/*
Plugin Name: Dominios Reseller
Description: Certifica dominios ecológicos desde WHM, muestra árboles plantados y CO2 evitado.
Version: 1.0.1
Author: Replanta
*/

// Activar plugin: crear tabla si no existe
register_activation_hook(__FILE__, 'dominios_reseller_create_table');
function dominios_reseller_create_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'dominios_reseller';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        domain varchar(255) NOT NULL UNIQUE,
        trees_planted int(11) DEFAULT 0,
        co2_evaded float DEFAULT 0,
        fecha_emision DATE DEFAULT NULL,
        validez DATE DEFAULT NULL,
        status varchar(20) DEFAULT 'Activo',
        primary_domain varchar(255) DEFAULT NULL,
        startdate bigint(20) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Incluir archivos del plugin
foreach ([
    'includes/whm-functions.php',
    'includes/emisiones-functions.php',
    'includes/ajax-handlers.php',
    'includes/shortcodes.php',
    'includes/scripts.php'
] as $file) {
    $path = plugin_dir_path(__FILE__) . $file;
    if (file_exists($path)) require_once $path;
}

// Registrar página del plugin en el admin
add_action('admin_menu', function () {
    add_menu_page(
        'Dominios Reseller',
        'Dominios Reseller',
        'manage_options',
        'dominios-reseller',
        'dominios_reseller_admin_page',
        'dashicons-cloud',
        56
    );
});

function dominios_reseller_admin_page() {
    echo '<div class="wrap">';
    echo '<h1>Certificación de Dominios Ecológicos</h1>';
    echo '<p>Configuración del plugin y gestión de dominios certificados.</p>';

    // Manejar test de conexión
    if (isset($_POST['test_whm_connection'])) {
        $options = get_option('dominios_reseller_options');
        $token = $options['whm_token'] ?? '';
        
        if (empty($token)) {
            echo '<div class="notice notice-error"><p>❌ Error: Debes configurar primero el API Token de WHM.</p></div>';
        } else {
            echo '<div class="notice notice-info"><p>🔄 Probando conexión con WHM...</p></div>';
            $test_result = test_whm_connection($token);
            
            if ($test_result['success']) {
                echo '<div class="notice notice-success"><p>✅ Conexión exitosa! Se encontraron ' . $test_result['count'] . ' cuentas en WHM.</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>❌ Error de conexión: ' . esc_html($test_result['error']) . '</p></div>';
            }
        }
    }

    echo '<form method="post" action="options.php">';
    settings_fields('dominios_reseller_options_group');
    do_settings_sections('dominios-reseller');
    submit_button('Guardar ajustes');
    echo '</form>';

    // Botón de prueba de conexión
    echo '<form method="post" style="margin-top: 10px;">';
    echo '<input type="hidden" name="test_whm_connection" value="1">';
    submit_button('🔧 Probar Conexión WHM', 'secondary', 'test_connection');
    echo '</form>';

    echo '<hr>';
    echo '<div id="dominios-list">';
    mostrar_lista_dominios();
    echo '</div>';
    echo '</div>';
}

add_action('admin_init', function () {
    register_setting('dominios_reseller_options_group', 'dominios_reseller_options', 'dominios_reseller_validate_options');
    add_settings_section('dominios_reseller_main', 'Configuración WHM', function () {
        echo '<p>Introduce tu API Token para obtener los dominios desde WHM.</p>';
        echo '<p><strong>Servidor WHM:</strong> 77.95.113.38:2087 (IP directa)</p>';
        echo '<p><em>El token debe tener permisos para listar cuentas (listaccts).</em></p>';
    }, 'dominios-reseller');

    add_settings_field('whm_token', 'API Token WHM', function () {
        $opts = get_option('dominios_reseller_options');
        $token = isset($opts['whm_token']) ? esc_attr($opts['whm_token']) : '';
        echo "<input type='password' name='dominios_reseller_options[whm_token]' value='$token' class='regular-text' autocomplete='off' placeholder='Introduce tu token WHM aquí...'>";
        echo "<p class='description'>Token de autenticación para acceder a la API de WHM</p>";
    }, 'dominios-reseller', 'dominios_reseller_main');
});

function dominios_reseller_validate_options($input) {
    return [
        'whm_token' => sanitize_text_field($input['whm_token'] ?? '')
    ];
}

// Función para probar conexión WHM
function test_whm_connection($token) {
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
        error_log('[Dominios Reseller] WP Error: ' . $error_msg);
        return [
            'success' => false,
            'error' => 'Error de conexión: ' . $error_msg
        ];
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    error_log('[Dominios Reseller] Test Connection - Status: ' . $status_code . ' Body: ' . substr($body, 0, 200));

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