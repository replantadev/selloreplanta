<?php
/**
 * Plugin Name: Sello Replanta
 * Description: Muestra un sello de Replanta en el pie de página si el dominio está alojado en Replanta.
 * Version: 1.0.2
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
    $domain = parse_url(home_url(), PHP_URL_HOST);
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

// Registrar configuraciones
add_action('admin_init', 'sello_replanta_settings');

function sello_replanta_settings()
{
    register_setting('sello_replanta_options_group', 'sello_replanta_options', 'sello_replanta_options_validate');
    add_settings_section('sello_replanta_main', 'Configuración Principal', 'sello_replanta_section_text', 'sello-replanta');
    add_settings_field('sello_replanta_mode', 'Modo', 'sello_replanta_setting_mode', 'sello-replanta', 'sello_replanta_main');
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

function sello_replanta_options_validate($input)
{
    $newinput = array();
    $newinput['mode'] = trim($input['mode']);
    return $newinput;
}

// Función para verificar si el dominio está alojado en Replanta
function verificar_dominio_replanta($domain)
{
    error_log('Verificando dominio: ' . $domain);

    $url = 'https://replanta.net/wp-json/replanta/v1/check_domain';
    $response = wp_remote_post($url, array(
        'body' => json_encode(array('domain' => $domain)),
        'headers' => array(
            'Content-Type' => 'application/json'
        )
    ));

    if (is_wp_error($response)) {
        error_log('Error en la solicitud: ' . $response->get_error_message());
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    $is_hosted = isset($data['hosted']) && $data['hosted'] === true;

    if ($is_hosted) {
        error_log('Dominio alojado en Replanta.');
    } else {
        error_log('Dominio no alojado en Replanta.');
    }

    return $is_hosted;
}

// Añadir estilos en línea en el encabezado
add_action('wp_head', 'sello_replanta_inline_styles');

function sello_replanta_inline_styles()
{
    echo '<style>
        .sello-replanta-footer {
            display: block !important;
            text-align: center;
            padding: 15px 0;
            position: relative;
            z-index: 9999;
        }
        .sello-replanta-img {
            width: 110px;
            height: auto;
            transition: opacity 0.3s ease;
        }
        .sello-replanta-img:hover {
            opacity: 0.9;
        }
    </style>';
}

// Reemplazar la función de enqueue scripts por esta
add_action('wp_footer', 'sello_replanta_display_badge');

function sello_replanta_display_badge()
{
    $is_hosted = get_option('sello_replanta_is_hosted', false);
    $options = get_option('sello_replanta_options');
    $mode = isset($options['mode']) ? $options['mode'] : 'light';

    if ($is_hosted) {
        $image_file = ($mode === 'dark') ? 'carbon-negative-b.svg' : 'carbon-negative.svg';
        $image_url = plugin_dir_url(__FILE__) . 'imagenes/' . $image_file;
        $domain = parse_url(home_url(), PHP_URL_HOST);

        // Generar el div del sello
        echo '<div id="sello-replanta-container" style="display:none;">
                <div class="sello-replanta-footer">
                    <a href="https://replanta.net/web-hosting-ecologico/?dominio=' . esc_attr($domain) . '" target="_blank" rel="noopener">
                        <img src="' . esc_url($image_url) . '" alt="Alojamiento web ecológico" class="sello-replanta-img">
                    </a>
                </div>
              </div>';

        // JavaScript para mover el div antes del cierre del footer
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                var selloContainer = document.getElementById("sello-replanta-container");
                var footer = document.querySelector("footer");
                if (selloContainer && footer) {
                    footer.parentNode.insertBefore(selloContainer, footer);
                    selloContainer.style.display = "block";
                }
            });
        </script>';
    }
}
?>