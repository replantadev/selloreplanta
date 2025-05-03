<?php

/**
 * Plugin Name: Sello Replanta
 * Description: Muestra un sello de Replanta en el pie de página si el dominio está alojado en Replanta.
 * Version: 1.0.12
 * Author: Replanta
 * Author URI: https://replanta.net
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sello-replanta
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/replantadev/selloreplanta
 */

// Evitar el acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

define('SR_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SR_PLUGIN_URL', plugin_dir_url(__FILE__));

if (file_exists(SR_PLUGIN_PATH . 'vendor/autoload.php')) {
    require_once SR_PLUGIN_PATH . 'vendor/autoload.php';
}

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/replantadev/selloreplanta/',
    __FILE__,
    'sello-replanta'
);

// Añadir menú en el administrador
add_action('admin_menu', 'sello_replanta_menu');

function sello_replanta_menu()
{
    add_options_page('Sello Replanta', 'Sello Replanta', 'manage_options', 'sello-replanta', 'sello_replanta_options_page');
}

// Crear la página de opciones
function sello_replanta_options_page()
{
    $domain = wp_parse_url(home_url(), PHP_URL_HOST);
    $is_hosted = get_option('sello_replanta_is_hosted', null);

    if (is_null($is_hosted)) {
        $is_hosted = verificar_dominio_replanta($domain);
        update_option('sello_replanta_is_hosted', $is_hosted);
    }

?>
    <div class="wrap">
        <h1>Sello Replanta</h1>
        <?php if ($is_hosted): ?>
            <p>El dominio está alojado en Replanta.</p>
            <form method="post" action="options.php">
                <?php
                settings_fields('sello_replanta_options_group');
                do_settings_sections('sello-replanta');
                submit_button();
                ?>
            </form>
        <?php else: ?>
            <p>El dominio no está alojado en Replanta.</p>
        <?php endif; ?>
    </div>
<?php
}

add_action('plugins_loaded', 'sello_replanta_load_textdomain');

function sello_replanta_load_textdomain()
{
    load_plugin_textdomain('sello-replanta', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

add_action('wp_enqueue_scripts', 'sello_replanta_enqueue_assets');

function sello_replanta_enqueue_assets()
{
    // Registrar y cargar el CSS
    wp_enqueue_style(
        'sello-replanta-styles',
        plugin_dir_url(__FILE__) . 'assets/css/sello-replanta.css',
        array(),
        '1.0.8'
    );

    // Registrar y cargar el JS
    wp_enqueue_script(
        'sello-replanta-scripts',
        plugin_dir_url(__FILE__) . 'assets/js/sello-replanta.js',
        array('jquery'),
        '1.0.8',
        true
    );

    // Pasar datos al script JS
    wp_localize_script('sello-replanta-scripts', 'selloReplantaData', array(
        'customBgColor' => isset(get_option('sello_replanta_options')['bg_color']) ? get_option('sello_replanta_options')['bg_color'] : '',
    ));
}

// Registrar configuraciones
add_action('admin_init', 'sello_replanta_settings');

function sello_replanta_settings()
{
    register_setting('sello_replanta_options_group', 'sello_replanta_options', 'sello_replanta_options_validate');
    add_settings_section('sello_replanta_main', 'Configuración Principal', 'sello_replanta_section_text', 'sello-replanta');
    add_settings_field('sello_replanta_mode', 'Modo', 'sello_replanta_setting_mode', 'sello-replanta', 'sello_replanta_main');
    add_settings_field('sello_replanta_bg_color', 'Color de Fondo', 'sello_replanta_setting_bg_color', 'sello-replanta', 'sello_replanta_main');
}

function sello_replanta_section_text()
{
    echo '<p>Configure las opciones del Sello Replanta.</p>';
}

function sello_replanta_setting_mode()
{
    $options = get_option('sello_replanta_options');
    $mode = isset($options['mode']) ? $options['mode'] : 'light';
    echo "<select id='sello_replanta_mode' name='sello_replanta_options[mode]'>
            <option value='light'" . selected($mode, 'light', false) . ">Claro</option>
            <option value='dark'" . selected($mode, 'dark', false) . ">Oscuro</option>
          </select>";
}

function sello_replanta_setting_bg_color()
{
    $options = get_option('sello_replanta_options');
    $bg_color = isset($options['bg_color']) ? $options['bg_color'] : '';
    echo "<input type='text' id='sello_replanta_bg_color' name='sello_replanta_options[bg_color]' value='" . esc_attr($bg_color) . "' placeholder='Ejemplo: #ffffff' />";
    echo "<p class='description'>Deja este campo vacío para usar el color detectado automáticamente.</p>";
}

function sello_replanta_options_validate($input)
{
    $newinput = array();
    $newinput['mode'] = sanitize_text_field($input['mode']);
    $newinput['bg_color'] = sanitize_hex_color($input['bg_color']); // Validar color hexadecimal
    return $newinput;
}

// Función para verificar si el dominio está alojado en Replanta
function verificar_dominio_replanta($domain)
{
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Verificando dominio: ' . $domain);
    }

    $url = 'https://replanta.net/wp-json/replanta/v1/check_domain';
    $response = wp_remote_post($url, array(
        'body' => json_encode(array('domain' => $domain)),
        'headers' => array(
            'Content-Type' => 'application/json'
        )
    ));

    if (is_wp_error($response)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Error en la solicitud: ' . $response->get_error_message());
        }
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    $is_hosted = isset($data['hosted']) && $data['hosted'] === true;

    if (defined('WP_DEBUG') && WP_DEBUG) {
        if ($is_hosted) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Dominio alojado en Replanta: ' . $domain);
            }
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Dominio no alojado en Replanta: ' . $domain);
            }
        }
    }

    return $is_hosted;
}

add_action('wp_footer', 'sello_replanta_display_badge');

function sello_replanta_display_badge()
{
    $is_hosted = get_option('sello_replanta_is_hosted', false);
    $options = get_option('sello_replanta_options');
    $mode = isset($options['mode']) ? $options['mode'] : 'light';
    $custom_bg_color = isset($options['bg_color']) ? $options['bg_color'] : '';

    if ($is_hosted) {
        $image_file = ($mode === 'dark') ? 'carbon-negative-b.svg' : 'carbon-negative.svg';
        $image_url = plugin_dir_url(__FILE__) . 'imagenes/' . $image_file;
        $domain = wp_parse_url(home_url(), PHP_URL_HOST);

        // Generar el div del sello
        echo '<div id="sello-replanta-container" style="display:none;">
                <div class="sello-replanta-footer">';
        // Modificar en sello_replanta_display_badge()
        echo '<div class="sello-replanta-wrapper" aria-label="Certificado hosting ecológico">';
        echo '<a href="https://replanta.net/web-hosting-ecologico/?utm_source=client-site&utm_medium=badge&utm_campaign=seal&domain=' . esc_attr($domain) . '" 
        target="_blank" 
        rel="noopener sponsored" 
        class="replanta-seal-link">
        <img src="' . esc_url($image_url) . '" 
             alt="Certificado Hosting Ecológico Replanta - ' . esc_attr($domain) . '" 
             width="110" 
             height="40"
             loading="lazy">
                </a>';
        echo '</div></div>';


        // En sello_replanta_display_badge()
        echo '<script type="application/ld+json">
                {
                    "@context": "https://schema.org",
                    "@type": "Certification",
                    "name": "Certificación Hosting Ecológico Replanta",
                    "description": "Certificado oficial de sostenibilidad web emitido por Replanta",
                    "image": "' . esc_url($image_url) . '",
                    "recognizedBy": {
                        "@type": "Organization",
                        "name": "Replanta",
                        "url": "https://replanta.net",
                        "sameAs": "https://replanta.net/certificacion-ecologica"
                    },
                    "validFor": {
                        "@type": "WebSite",
                        "name": "' . esc_attr(get_bloginfo('name')) . '",
                        "url": "' . esc_url(home_url()) . '"
                    }
                }
                </script>';
    }
}
