<?php
// Seguridad: evita el acceso directo
if (!defined('ABSPATH')) exit;

/**
 * Carga scripts y estilos en la página del plugin
 */
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_dominios-reseller') return;

    // JS para funcionalidades dinámicas
    wp_enqueue_script(
        'dominios-reseller-js',
        plugin_dir_url(__FILE__) . '../assets/js/dominios-reseller.js',
        ['jquery'],
        '1.0',
        true
    );

    wp_localize_script('dominios-reseller-js', 'dominiosReseller', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('dominios_reseller_nonce'),
        'mensaje_guardado' => __('Datos actualizados correctamente.', 'dominios-reseller'),
        'mensaje_error'    => __('Error al actualizar los datos.', 'dominios-reseller')
    ]);

    // CSS opcional para estilo admin
    
});