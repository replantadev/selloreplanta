<?php
// Seguridad: evitar acceso directo
if (!defined('ABSPATH')) exit;

/**
 * Shortcode: [mostrar_dominio]
 * Muestra info visual del dominio actual, si está en la base de datos
 */
add_shortcode('mostrar_dominio', function () {
    if (is_admin() && !wp_doing_ajax()) return '';

    $options = get_option('dominios_reseller_options');
    $datos = obtener_datos_dominio_actual();
    
    if (!$datos) {
        // Mostrar texto hero si no hay dominio específico
        $hero_title = $options['hero_title'] ?? 'Hosting Ecológico con Impacto Positivo';
        $hero_description = $options['hero_description'] ?? 'Nuestro hosting funciona con energía 100% renovable y contribuye activamente a la reforestación del planeta. Cada sitio web alojado con nosotros ayuda a plantar árboles y reducir la huella de carbono.';
        
        ob_start();
        ?>
        <div class="d-dominio hero-mode">
            <h1 class="hero-title"><?php echo esc_html($hero_title); ?></h1>
            <p class="hero-description"><?php echo esc_html($hero_description); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }

    $trees = intval($datos['trees_planted']);
    $co2 = floatval($datos['co2_evaded']);
    $tree_icon = 'https://replanta.net/wp-content/uploads/2024/02/treec3.svg';
    
    // Verificar si el dominio es nuevo (menos de un año o 0 árboles)
    $is_new_domain = ($trees === 0);
    $new_domain_message = $options['new_domain_message'] ?? '🌱 ¡Acabamos de comenzar este emocionante viaje juntos! Tu sitio web ya funciona con energía 100% renovable. Los árboles se plantan automáticamente cuando cumplimos nuestro primer año de colaboración. ¡Gracias por elegir un hosting que cuida el planeta!';

    ob_start();
    ?>
    <div class="d-dominio">
        <span><b class="do"><?php echo esc_html($datos['domain']); ?></b></span><br>
        <span class="dotxt">
            Está servido con energía verde, procedente de fuentes renovables.<br>
            <?php if ($is_new_domain): ?>
                <?php echo esc_html($new_domain_message); ?>
            <?php else: ?>
                Además, desde que trabajamos juntos, hemos plantado <b><?php echo $trees; ?> árboles</b> en proyectos de reforestación y evitado la emisión de <b><?php echo number_format($co2, 2); ?></b> Kg de CO<sub>2</sub>.<br>
                <small><a href="#co2">¿Cómo lo calculamos?</a></small>.
            <?php endif; ?>
        </span>
        <?php if (!$is_new_domain): ?>
        <div class="arboles">
            <?php for ($i = 0; $i < $trees; $i++): ?>
                <img src="<?php echo esc_url($tree_icon); ?>" alt="Árbol plantado" width="26px" style="margin-right:5px;">
            <?php endfor; ?>
        </div>
        <?php endif; ?>
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
 * Obtiene los datos del dominio actual, priorizando dominios activos sobre suspendidos
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

    // Buscar todos los registros del dominio (puede estar en ambos servidores)
    $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE domain = %s ORDER BY 
        CASE 
            WHEN status = 'Activo' THEN 1 
            WHEN status = 'Suspendido' THEN 2 
            ELSE 3 
        END", $host));

    if ($rows) {
        // Retornar el primer resultado (el activo si existe, sino el suspendido)
        $row = $rows[0];
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