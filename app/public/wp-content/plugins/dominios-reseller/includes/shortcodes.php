<?php
// Seguridad: evitar acceso directo
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [mostrar_dominio]
 * Muestra info visual del dominio actual, si está en la base de datos
 */
add_shortcode('mostrar_dominio', function () {
    if (is_admin() && !wp_doing_ajax()) return '';

    $datos = obtener_datos_dominio_actual();
    if (!$datos) return '';

    $trees = intval($datos['trees_planted']);
    $co2 = floatval($datos['co2_evaded']);
    $tree_icon = 'https://replanta.net/wp-content/uploads/2024/02/treec3.svg';

    ob_start();
    ?>
    <div class="d-dominio">
        <span><b class="do"><?php echo esc_html($datos['domain']); ?></b></span><br>
        <span class="dotxt">
            Está servido con energía verde, procedente de fuentes renovables.<br>
            Además, desde que trabajamos juntos, hemos plantado <b><?php echo $trees; ?> árboles</b> en proyectos de reforestación y evitado la emisión de <b><?php echo number_format($co2, 2); ?></b> Kg de CO<sub>2</sub>.<br>
            <small><a href="#co2">¿Cómo lo calculamos?</a></small>.
        </span>
        <div class="arboles">
            <?php for ($i = 0; $i < $trees; $i++): ?>
                <img src="<?php echo esc_url($tree_icon); ?>" alt="Árbol plantado" width="26px" style="margin-right:5px;">
            <?php endfor; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
});

/**
 * Shortcode: [mostrar_datos_dominio]
 * Muestra datos simples (nombre del dominio y árboles plantados)
 */
add_shortcode('mostrar_datos_dominio', function () {
    $datos = obtener_datos_dominio_actual();
    if (!$datos) return '';

    return '<div class="datos-dominio">
        <p><b class="dominio">' . esc_html($datos['domain']) . '</b></p>
        <p>Árboles Plantados: <b>' . esc_html($datos['trees_planted']) . '</b></p>
    </div>';
});


/**
 * Obtiene los datos del dominio actual (basado en la URL)
 */
function obtener_datos_dominio_actual() {
    global $wpdb;
    $table = $wpdb->prefix . 'dominios_reseller';

    // Si viene por ?domain=... en la URL, úsalo
    if (!empty($_GET['domain'])) {
        $host = sanitize_text_field($_GET['domain']);
    } else {
        $host = parse_url(home_url(), PHP_URL_HOST);
    }
    $host = preg_replace('/^www\./', '', strtolower($host));

    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE domain = %s", $host));

    if ($row) {
        return [
            'domain'         => $row->domain,
            'trees_planted'  => $row->trees_planted,
            'co2_evaded'     => $row->co2_evaded,
            'status'         => $row->status,
            'primary_domain' => $row->primary_domain,
            'startdate'      => $row->startdate,
            'fecha_emision'  => $row->fecha_emision,
            'validez'        => $row->validez
        ];
    }

    return null;
}