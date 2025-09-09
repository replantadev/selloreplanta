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

    echo '<form method="post" action="options.php">';
    settings_fields('dominios_reseller_options_group');
    do_settings_sections('dominios-reseller');
    submit_button('Guardar ajustes');
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
    }, 'dominios-reseller');

    add_settings_field('whm_token', 'API Token', function () {
        $opts = get_option('dominios_reseller_options');
        $token = isset($opts['whm_token']) ? esc_attr($opts['whm_token']) : '';
        echo "<input type='password' name='dominios_reseller_options[whm_token]' value='$token' class='regular-text' autocomplete='off'>";
    }, 'dominios-reseller', 'dominios_reseller_main');
});

function dominios_reseller_validate_options($input) {
    return [
        'whm_token' => sanitize_text_field($input['whm_token'] ?? '')
    ];
}