<?php
/*
Plugin Name: Sello Replanta
Description: Añade un sello de carbono negativo en el footer del sitio web si está alojado en Replanta.
Version: 1.5
Author: Replanta
*/

if (!defined('ABSPATH')) {
    exit;
}

// Añadir menú en el administrador
add_action('admin_menu', 'sello_replanta_menu');

function sello_replanta_menu() {
    add_options_page('Sello Replanta', 'Sello Replanta', 'manage_options', 'sello-replanta', 'sello_replanta_options_page');
}

// Crear la página de opciones
function sello_replanta_options_page() {
    ?>
    <div class="wrap">
        <h1>Sello Replanta</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('sello_replanta_options_group');
            do_settings_sections('sello-replanta');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Registrar configuraciones
add_action('admin_init', 'sello_replanta_settings');

function sello_replanta_settings() {
    register_setting('sello_replanta_options_group', 'sello_replanta_options', 'sello_replanta_options_validate');
    add_settings_section('sello_replanta_main', 'Configuración Principal', 'sello_replanta_section_text', 'sello-replanta');
    add_settings_field('sello_replanta_mode', 'Modo', 'sello_replanta_setting_mode', 'sello-replanta', 'sello_replanta_main');
    add_settings_field('sello_replanta_check', 'Verificar Dominio', 'sello_replanta_setting_check', 'sello-replanta', 'sello_replanta_main');
}

function sello_replanta_section_text() {
    echo '<p>Configure las opciones del Sello Replanta.</p>';
}

function sello_replanta_setting_mode() {
    $options = get_option('sello_replanta_options');
    $mode = isset($options['mode']) ? $options['mode'] : 'light';
    echo "<select id='sello_replanta_mode' name='sello_replanta_options[mode]'>
            <option value='light'" . selected($mode, 'light', false) . ">Claro</option>
            <option value='dark'" . selected($mode, 'dark', false) . ">Oscuro</option>
          </select>";
}

function sello_replanta_setting_check() {
    $options = get_option('sello_replanta_options');
    $domain = parse_url(home_url(), PHP_URL_HOST);
    $is_hosted = verificar_dominio_replanta($domain);
    echo $is_hosted ? '<p>El dominio está alojado en Replanta.</p>' : '<p>El dominio no está alojado en Replanta.</p>';
}

function sello_replanta_options_validate($input) {
    $newinput['mode'] = trim($input['mode']);
    return $newinput;
}

// Función para verificar si el dominio está alojado en Replanta
function verificar_dominio_replanta($domain) {
    $url = 'https://replanta.dev/wp-json/replanta/v1/check_domain';
    $response = wp_remote_post($url, array(
        'body' => json_encode(array('domain' => $domain)),
        'headers' => array(
            'Content-Type' => 'application/json'
        )
    ));

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return isset($data['hosted']) && $data['hosted'] === true;
}

// Añadir estilos en línea en el encabezado
add_action('wp_head', 'sello_replanta_inline_styles');

function sello_replanta_inline_styles() {
    echo '<style>
        .sello-replanta-footer {
            text-align: center;
            margin-top: 0px;
            padding: 10px 0;
            background: transparent;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .sello-replanta-footer a {
            display: inline-block;
        }
        .sello-replanta-img {
            width: 110px;
            height: auto;
        }
    </style>';
}

// Añadir sello al footer
add_action('wp_footer', 'sello_replanta_footer');

function sello_replanta_footer() {
    $options = get_option('sello_replanta_options');
    $mode = isset($options['mode']) ? $options['mode'] : 'light';
    $domain = parse_url(home_url(), PHP_URL_HOST);

    if (verificar_dominio_replanta($domain)) {
        $image_url = 'https://replanta.dev/wp-content/uploads/2024/02/carbon-negative.svg';
        if ($mode === 'dark') {
            $image_url = 'https://replanta.dev/wp-content/uploads/2024/02/carbon-negative-b.svg';
        }

        // Insertar en el footer
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                var footer = document.querySelector("footer");
                if (!footer) {
                    footer = document.querySelector(".footer") || document.querySelector("#footer") || document.querySelector(".site-footer") || document.querySelector(".main-footer") || document.querySelector(".elementor-location-footer");
                }

                function getBackgroundColor(element) {
                    if (!element) return "transparent";
                    var style = getComputedStyle(element);
                    var bgColor = style.backgroundColor;
                    if (bgColor && bgColor !== "rgba(0, 0, 0, 0)" && bgColor !== "transparent") {
                        return bgColor;
                    }
                    for (var i = 0; i < element.children.length; i++) {
                        bgColor = getBackgroundColor(element.children[i]);
                        if (bgColor && bgColor !== "rgba(0, 0, 0, 0)" && bgColor !== "transparent") {
                            return bgColor;
                        }
                    }
                    return "transparent";
                }

                if (footer) {
                    var footerBgColor = getBackgroundColor(footer);

                    var sello = document.createElement("div");
                    sello.className = "sello-replanta-footer";
                    sello.style.backgroundColor = footerBgColor;
                    sello.innerHTML = \'<a href="https://replanta.dev/web-hosting-ecologico/?dominio=' . esc_attr($domain) . '" target="_blank"><img src="' . esc_url($image_url) . '" alt="Web hosting ecológico" class="sello-replanta-img"></a>\';
                    footer.appendChild(sello);
                }
            });
        </script>';
    }
}
