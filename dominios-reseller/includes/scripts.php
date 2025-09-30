<?php
// Seguridad: evita el acceso directo
if (!defined('ABSPATH')) exit;

/**
 * Carga scripts y estilos en la página del plugin
 * DESACTIVADO - Usando CSS/JS inline en el archivo principal
 */
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_dominios-reseller') return;

    // DESACTIVADO: CSS/JS inline se carga desde dominios_reseller_inline_assets()
    // Los assets externos causan errores 404 por symlink
    
    /*
    // Obtener la URL base del plugin correctamente
    $plugin_url = plugin_dir_url(dirname(__FILE__));
    $version = DOMINIOS_RESELLER_VERSION;

    // CSS moderno para la interfaz
    wp_enqueue_style(
        'dominios-reseller-admin-css',
        $plugin_url . 'assets/css/admin.css',
        [],
        $version
    );

    // JS para funcionalidades dinámicas - usar el archivo correcto
    wp_enqueue_script(
        'dominios-reseller-admin-js',
        $plugin_url . 'assets/js/admin.js',
        ['jquery'],
        $version,
        true
    );
    */

    // DESACTIVADO: Localización también comentada
    /*
    // Localización para AJAX
    wp_localize_script('dominios-reseller-admin-js', 'dominios_reseller_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('dominios_reseller_nonce'),
        'mensaje_guardado' => __('Datos actualizados correctamente.', 'dominios-reseller'),
        'mensaje_error'    => __('Error al actualizar los datos.', 'dominios-reseller')
    ]);

    // También definir ajaxurl globalmente para compatibilidad
    wp_localize_script('dominios-reseller-admin-js', 'ajaxurl', admin_url('admin-ajax.php'));
    */
});

/**
 * AJAX: Probar conexión WHM
 */
add_action('wp_ajax_test_whm_connection', function() {
    check_ajax_referer('dominios_reseller_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permisos para esta acción.', 'dominios-reseller'));
    }

    $server = sanitize_text_field($_POST['server'] ?? 'uk');
    $options = get_option('dominios_reseller_options');
    $token_key = $server . '_whm_token';
    $token = $options[$token_key] ?? '';

    $result = test_whm_connection($token, $server);

    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result['error']);
    }
});

/**
 * AJAX: Calcular emisiones
 */
add_action('wp_ajax_calculate_emissions', function() {
    check_ajax_referer('dominios_reseller_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permisos para esta acción.', 'dominios-reseller'));
    }

    $domain = sanitize_text_field($_POST['domain']);
    $server = sanitize_text_field($_POST['server']);
    $trees = intval($_POST['trees']);
    $co2 = floatval($_POST['co2']);

    global $wpdb;
    $tabla = $wpdb->prefix . 'dominios_reseller';

    $result = $wpdb->update(
        $tabla,
        [
            'trees_planted' => $trees,
            'co2_evaded' => $co2
        ],
        ['domain' => $domain]
    );

    if ($result !== false) {
        wp_send_json_success(__('Emisiones calculadas correctamente.', 'dominios-reseller'));
    } else {
        wp_send_json_error(__('Error al guardar los datos.', 'dominios-reseller'));
    }
});

/**
 * AJAX: Guardar datos de dominios
 */
add_action('wp_ajax_save_domain_data', function() {
    check_ajax_referer('dominios_reseller_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permisos para esta acción.', 'dominios-reseller'));
    }

    $server = sanitize_text_field($_POST['server']);
    $data = json_decode(stripslashes($_POST['data']), true);

    if (!is_array($data)) {
        wp_send_json_error(__('Datos inválidos.', 'dominios-reseller'));
    }

    global $wpdb;
    $tabla = $wpdb->prefix . 'dominios_reseller';
    $updated = 0;
    $errors = 0;

    foreach ($data as $item) {
        $domain = sanitize_text_field($item['domain']);
        $trees = intval($item['trees']);
        $co2 = floatval($item['co2']);

        $result = $wpdb->update(
            $tabla,
            [
                'trees_planted' => $trees,
                'co2_evaded' => $co2
            ],
            ['domain' => $domain]
        );

        if ($result !== false) {
            $updated++;
        } else {
            $errors++;
        }
    }

    if ($errors === 0) {
        wp_send_json_success(sprintf(__('Datos guardados correctamente. %d dominios actualizados.', 'dominios-reseller'), $updated));
    } else {
        wp_send_json_error(sprintf(__('Error al guardar algunos datos. %d actualizados, %d errores.', 'dominios-reseller'), $updated, $errors));
    }
});

/**
 * AJAX: Guardar datos de tabla unificada
 */
add_action('wp_ajax_save_unified_domain_data', function() {
    check_ajax_referer('dominios_reseller_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permisos para esta acción.', 'dominios-reseller'));
    }

    $data = json_decode(stripslashes($_POST['data']), true);

    if (!is_array($data)) {
        wp_send_json_error(__('Datos inválidos.', 'dominios-reseller'));
    }

    global $wpdb;
    $tabla = $wpdb->prefix . 'dominios_reseller';
    $updated = 0;
    $errors = 0;

    foreach ($data as $item) {
        $domain = sanitize_text_field($item['domain']);
        $trees = intval($item['trees']);
        $co2 = floatval($item['co2']);

        $result = $wpdb->update(
            $tabla,
            [
                'trees_planted' => $trees,
                'co2_evaded' => $co2
            ],
            ['domain' => $domain]
        );

        if ($result !== false) {
            $updated++;
        } else {
            $errors++;
        }
    }

    if ($errors === 0) {
        wp_send_json_success(sprintf(__('Datos guardados correctamente. %d dominios actualizados.', 'dominios-reseller'), $updated));
    } else {
        wp_send_json_error(sprintf(__('Error al guardar algunos datos. %d actualizados, %d errores.', 'dominios-reseller'), $updated, $errors));
    }
});