<?php

/**
 * Plugin Name: Sello Replanta PRO
 * Description: Sello de carbono negativo inteligente que se adapta a cualquier page builder (Elementor, Divi, etc.). Versión PRO con detección avanzada.
 * Version: 2.0.2
 * Author: Replanta
 * Author URI: https://replanta.net
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sello-replanta
 * Domain Path: /langu    // Generar el HTML del sello con configuración PRO
    echo '<div id="sello-replanta-container" class="' . esc_attr($positioning_class) . ' sello-size-' . esc_attr($size) . ' sello-zindex-' . esc_attr($zindex) . '"' . $style_attr . ' data-position="' . esc_attr($position) . '" data-builders="' . esc_attr(implode(',', $page_builders)) . '" data-zindex="' . esc_attr($zindex_value) . '" data-margin="' . esc_attr($margin) . '">';es
 * GitHub Plugin URI: https://github.com/replantadev/selloreplanta
 */

// Evitar el acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

define('SR_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SR_VERSION', '2.0.1');

// Detectar page builders activos
add_action('init', 'sello_replanta_detect_page_builders');

function sello_replanta_detect_page_builders() {
    $page_builders = array();
    
    // Detectar Elementor
    if (defined('ELEMENTOR_VERSION') || class_exists('Elementor\Plugin')) {
        $page_builders[] = 'elementor';
    }
    
    // Detectar Divi
    if (function_exists('et_get_theme_version') || get_template() === 'Divi') {
        $page_builders[] = 'divi';
    }
    
    // Detectar Beaver Builder
    if (class_exists('FLBuilder')) {
        $page_builders[] = 'beaver';
    }
    
    // Detectar Visual Composer
    if (defined('WPB_VC_VERSION')) {
        $page_builders[] = 'visual_composer';
    }
    
    // Detectar Gutenberg/Block Editor
    if (function_exists('has_blocks')) {
        $page_builders[] = 'gutenberg';
    }
    
    update_option('sello_replanta_page_builders', $page_builders);
}

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

function sello_replanta_get_version() {
    if (!function_exists('get_file_data')) {
        require_once ABSPATH . 'wp-includes/functions.php';
    }
    $plugin_data = get_file_data(__FILE__, array('Version' => 'Version'));
    return isset($plugin_data['Version']) ? $plugin_data['Version'] : SR_VERSION;
}

add_action('wp_enqueue_scripts', 'sello_replanta_enqueue_assets');

function sello_replanta_enqueue_assets()
{
    $is_hosted = get_option('sello_replanta_is_hosted', false);
    if (!$is_hosted) return;

    // Registrar y cargar el CSS
    wp_enqueue_style(
        'sello-replanta-styles',
        plugin_dir_url(__FILE__) . 'assets/css/sello-replanta.css',
        array(),
        sello_replanta_get_version()
    );

    // Obtener opciones
    $options = get_option('sello_replanta_options');
    $custom_bg_color = isset($options['bg_color']) ? trim($options['bg_color']) : '';

    // Cargar JavaScript siempre para manejar el posicionamiento
    wp_enqueue_script(
        'sello-replanta-scripts',
        plugin_dir_url(__FILE__) . 'assets/js/sello-replanta.js',
        array(),
        sello_replanta_get_version(),
        true
    );

    // Pasar datos al JavaScript
    wp_localize_script('sello-replanta-scripts', 'selloReplantaData', array(
        'customBgColor' => $custom_bg_color,
        'pluginUrl' => plugin_dir_url(__FILE__)
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
    add_settings_field('sello_replanta_position', 'Posición', 'sello_replanta_setting_position', 'sello-replanta', 'sello_replanta_main');
    add_settings_field('sello_replanta_size', 'Tamaño', 'sello_replanta_setting_size', 'sello-replanta', 'sello_replanta_main');
    add_settings_field('sello_replanta_opacity', 'Opacidad', 'sello_replanta_setting_opacity', 'sello-replanta', 'sello_replanta_main');
    add_settings_field('sello_replanta_zindex', 'Z-Index (Conflictos)', 'sello_replanta_setting_zindex', 'sello-replanta', 'sello_replanta_main');
    add_settings_field('sello_replanta_margin', 'Margen Inferior', 'sello_replanta_setting_margin', 'sello-replanta', 'sello_replanta_main');
}

function sello_replanta_section_text()
{
    $page_builders = get_option('sello_replanta_page_builders', array());
    echo '<p>Configure las opciones del Sello Replanta PRO.</p>';
    if (!empty($page_builders)) {
        echo '<p><strong>Page builders detectados:</strong> ' . implode(', ', array_map('ucfirst', $page_builders)) . '</p>';
    }
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

function sello_replanta_setting_position()
{
    $options = get_option('sello_replanta_options');
    $position = isset($options['position']) ? $options['position'] : 'auto';
    echo "<select id='sello_replanta_position' name='sello_replanta_options[position]'>
            <option value='auto'" . selected($position, 'auto', false) . ">Automático (Detectar)</option>
            <option value='footer_end'" . selected($position, 'footer_end', false) . ">Final del Footer</option>
            <option value='body_end'" . selected($position, 'body_end', false) . ">Final del Body</option>
            <option value='fixed_bottom'" . selected($position, 'fixed_bottom', false) . ">Fijo Abajo</option>
            <option value='elementor_footer'" . selected($position, 'elementor_footer', false) . ">Footer Elementor</option>
          </select>";
    echo "<p class='description'>Elige dónde mostrar el sello. 'Automático' detecta la mejor posición.</p>";
}

function sello_replanta_setting_size()
{
    $options = get_option('sello_replanta_options');
    $size = isset($options['size']) ? $options['size'] : 'normal';
    echo "<select id='sello_replanta_size' name='sello_replanta_options[size]'>
            <option value='small'" . selected($size, 'small', false) . ">Pequeño (88x32px)</option>
            <option value='normal'" . selected($size, 'normal', false) . ">Normal (110x40px)</option>
            <option value='large'" . selected($size, 'large', false) . ">Grande (132x48px)</option>
          </select>";
}

function sello_replanta_setting_opacity()
{
    $options = get_option('sello_replanta_options');
    $opacity = isset($options['opacity']) ? $options['opacity'] : '1.0';
    echo "<input type='range' id='sello_replanta_opacity' name='sello_replanta_options[opacity]' value='" . esc_attr($opacity) . "' min='0.3' max='1.0' step='0.1' />";
    echo "<span id='opacity_value'>" . esc_html($opacity) . "</span>";
    echo "<p class='description'>Ajusta la transparencia del sello (0.3 = muy transparente, 1.0 = opaco).</p>";
    echo "<script>
        document.getElementById('sello_replanta_opacity').addEventListener('input', function() {
            document.getElementById('opacity_value').textContent = this.value;
        });
    </script>";
}

function sello_replanta_setting_zindex()
{
    $options = get_option('sello_replanta_options');
    $zindex = isset($options['zindex']) ? $options['zindex'] : 'auto';
    echo "<select id='sello_replanta_zindex' name='sello_replanta_options[zindex]'>
            <option value='auto'" . selected($zindex, 'auto', false) . ">Automático (9999)</option>
            <option value='low'" . selected($zindex, 'low', false) . ">Bajo (100) - Debajo de chats</option>
            <option value='medium'" . selected($zindex, 'medium', false) . ">Medio (1000)</option>
            <option value='high'" . selected($zindex, 'high', false) . ">Alto (9999)</option>
            <option value='higher'" . selected($zindex, 'higher', false) . ">Muy Alto (99999)</option>
          </select>";
    echo "<p class='description'>Controla si el sello aparece por encima o debajo de chats y otros elementos flotantes.</p>";
}

function sello_replanta_setting_margin()
{
    $options = get_option('sello_replanta_options');
    $margin = isset($options['margin']) ? $options['margin'] : '0';
    echo "<input type='number' id='sello_replanta_margin' name='sello_replanta_options[margin]' value='" . esc_attr($margin) . "' min='0' max='200' step='5' />";
    echo " px";
    echo "<p class='description'>Añade margen inferior para evitar conflictos con chats flotantes (recomendado: 60-80px si tienes problemas).</p>";
}

function sello_replanta_options_validate($input)
{
    $newinput = array();
    $newinput['mode'] = sanitize_text_field($input['mode']);
    $newinput['bg_color'] = sanitize_hex_color($input['bg_color']);
    $newinput['position'] = in_array($input['position'], ['auto', 'footer_end', 'body_end', 'fixed_bottom', 'elementor_footer']) 
        ? sanitize_text_field($input['position']) : 'auto';
    $newinput['size'] = in_array($input['size'], ['small', 'normal', 'large']) 
        ? sanitize_text_field($input['size']) : 'normal';
    $newinput['opacity'] = floatval($input['opacity']) >= 0.3 && floatval($input['opacity']) <= 1.0 
        ? floatval($input['opacity']) : 1.0;
    $newinput['zindex'] = in_array($input['zindex'], ['auto', 'low', 'medium', 'high', 'higher']) 
        ? sanitize_text_field($input['zindex']) : 'auto';
    $newinput['margin'] = intval($input['margin']) >= 0 && intval($input['margin']) <= 200 
        ? intval($input['margin']) : 0;
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
    
    if (!$is_hosted) return;
    
    $options = get_option('sello_replanta_options');
    $mode = isset($options['mode']) ? $options['mode'] : 'light';
    $custom_bg_color = isset($options['bg_color']) ? trim($options['bg_color']) : '';
    $position = isset($options['position']) ? $options['position'] : 'auto';
    $size = isset($options['size']) ? $options['size'] : 'normal';
    $opacity = isset($options['opacity']) ? $options['opacity'] : 1.0;
    $zindex = isset($options['zindex']) ? $options['zindex'] : 'auto';
    $margin = isset($options['margin']) ? $options['margin'] : 0;

    // Configurar tamaños según la opción
    $sizes = array(
        'small' => array('width' => 88, 'height' => 32),
        'normal' => array('width' => 110, 'height' => 40),
        'large' => array('width' => 132, 'height' => 48)
    );
    $current_size = $sizes[$size];

    $image_file = ($mode === 'dark') ? 'carbon-negative-b.svg' : 'carbon-negative.svg';
    $image_url = plugin_dir_url(__FILE__) . 'imagenes/' . $image_file;
    $domain = wp_parse_url(home_url(), PHP_URL_HOST);

    // Detectar page builders para posicionamiento inteligente
    $page_builders = get_option('sello_replanta_page_builders', array());
    
    // Determinar z-index según configuración
    $zindex_value = 9999; // Por defecto
    switch ($zindex) {
        case 'low':
            $zindex_value = 100;
            break;
        case 'medium':
            $zindex_value = 1000;
            break;
        case 'high':
            $zindex_value = 9999;
            break;
        case 'higher':
            $zindex_value = 99999;
            break;
        default: // 'auto'
            $zindex_value = 9999;
            break;
    }

    // Determinar la estrategia de posicionamiento
    $positioning_class = 'sello-position-auto';
    if ($position === 'fixed_bottom') {
        $positioning_class = 'sello-position-fixed';
    } elseif ($position === 'elementor_footer' && in_array('elementor', $page_builders)) {
        $positioning_class = 'sello-position-elementor';
    } elseif ($position === 'body_end') {
        $positioning_class = 'sello-position-body-end';
    }

    // Estilo inline para personalizaciones
    $inline_styles = array();
    if (!empty($custom_bg_color)) {
        $inline_styles[] = 'background-color: ' . esc_attr($custom_bg_color);
    }
    if ($opacity < 1.0) {
        $inline_styles[] = 'opacity: ' . esc_attr($opacity);
    }
    if ($margin > 0) {
        $inline_styles[] = 'margin-bottom: ' . esc_attr($margin) . 'px';
    }
    // Aplicar z-index personalizado
    $inline_styles[] = 'z-index: ' . esc_attr($zindex_value);
    
    $style_attr = !empty($inline_styles) ? ' style="' . implode('; ', $inline_styles) . ';"' : '';

    // Generar el HTML del sello con configuración PRO
    echo '<div id="sello-replanta-container" class="' . esc_attr($positioning_class) . ' sello-size-' . esc_attr($size) . '"' . $style_attr . ' data-position="' . esc_attr($position) . '" data-builders="' . esc_attr(implode(',', $page_builders)) . '">
        <div class="sello-replanta-footer">
            <div class="sello-replanta-wrapper" aria-label="Certificado hosting ecológico">
                <a href="https://replanta.net/web-hosting-ecologico/?utm_source=' . esc_attr($domain) . '&utm_medium=badge&utm_campaign=seal&domain=' . esc_attr($domain) . '" 
                   target="_blank" 
                   rel="noopener sponsored" 
                   class="replanta-seal-link">
                    <img src="' . esc_url($image_url) . '" 
                         alt="Certificado Hosting Ecológico Replanta - ' . esc_attr($domain) . '" 
                         width="' . esc_attr($current_size['width']) . '" 
                         height="' . esc_attr($current_size['height']) . '"
                         loading="lazy"
                         class="sello-replanta-img">
                </a>
            </div>
        </div>
    </div>';

    // Schema markup para SEO (mantenemos igual)
    $certification_id = 'REP-' . md5($domain);
    $issue_date = date('c', strtotime('-1 month'));
    $expiry_date = date('c', strtotime('+1 year'));

    echo '<script type="application/ld+json">' . json_encode([
        "@context" => "https://schema.org",
        "@type" => "CreativeWork",
        "name" => "Certificación de Hosting Ecológico",
        "award" => "Sello Replanta",
        "description" => "Distintivo ecológico otorgado por Replanta a sitios web alojados en servidores con huella de carbono negativa.",
        "url" => "https://replanta.net/certificacion?sitio=" . $domain,
        "image" => $image_url,
        "datePublished" => $issue_date,
        "author" => [
            "@type" => "Organization",
            "name" => "Replanta",
            "url" => "https://replanta.net"
        ],
        "publisher" => [
            "@type" => "Organization",
            "name" => "Replanta Certification Authority",
            "url" => "https://replanta.net/certificacion-ecologica"
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';

    echo '<script type="application/ld+json">' . json_encode([
        "@context" => "https://schema.org",
        "@type" => "Organization",
        "name" => get_bloginfo('name'),
        "url" => home_url(),
        "logo" => $image_url,
        "memberOf" => [
            "@type" => "Organization",
            "name" => "Replanta Green Hosting Certificación de Carbono Negativo",
            "url" => "https://replanta.net/certificacion-ecologica"
        ]
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}
