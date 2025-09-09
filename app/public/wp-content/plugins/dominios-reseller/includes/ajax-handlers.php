<?php
// Seguridad: evitar acceso directo
if (!defined('ABSPATH')) exit;
// Seguridad: evita llamadas sin nonce válido
function verificar_nonce_ajax() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dominios_reseller_nonce')) {
        wp_send_json_error(['message' => 'Permiso denegado.']);
        exit;
    }
}

add_action('wp_ajax_actualizar_dominio', 'dominios_reseller_actualizar_dominio');
add_action('wp_ajax_recalcular_co2', 'dominios_reseller_recalcular_co2');
add_action('wp_ajax_nopriv_recalcular_co2', 'dominios_reseller_recalcular_co2');
add_action('wp_ajax_nopriv_actualizar_dominio', 'dominios_reseller_actualizar_dominio');

/**
 * AJAX: Actualiza árboles plantados y CO2 manualmente
 */
function dominios_reseller_actualizar_dominio()
{
    verificar_nonce_ajax();
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'No autorizado.']);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'dominios_reseller';

    $domain = sanitize_text_field($_POST['domain'] ?? '');
    $trees = intval($_POST['trees_planted'] ?? 0);
    $co2 = floatval($_POST['co2_evaded'] ?? 0);

    if (!$domain) {
        wp_send_json_error(['message' => 'Dominio no válido.']);
    }

    $updated = $wpdb->update($table, [
        'trees_planted' => $trees,
        'co2_evaded'    => $co2,
    ], ['domain' => $domain]);

    if ($updated !== false) {
        wp_send_json_success(['message' => 'Datos actualizados correctamente.']);
    } else {
        wp_send_json_error(['message' => 'Error al actualizar datos.']);
    }
}

/**
 * AJAX: Recalcula y guarda el CO2 evitado automáticamente
 */
function dominios_reseller_recalcular_co2()
{
    verificar_nonce_ajax();
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'No autorizado.']);
    }

    $domain = sanitize_text_field($_POST['domain'] ?? '');

    if (!$domain) {
        wp_send_json_error(['message' => 'Dominio no válido.']);
    }

    $opts = get_option('dominios_reseller_options');
    $token = sanitize_text_field($opts['whm_token'] ?? '');

    if (!$token) {
        wp_send_json_error(['message' => 'API token no configurado.']);
    }

    $nuevo_co2 = recalcular_co2_para_dominio($domain, $token);

    if ($nuevo_co2 !== false) {
        wp_send_json_success(['co2_evaded' => $nuevo_co2]);
    } else {
        wp_send_json_error(['message' => 'Error al calcular emisiones.']);
    }
}