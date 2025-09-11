<?php
/*
Plugin Name: Trustpilot Replanta (Static Cache)
Description: Carga reseñas de Trustpilot una vez al día y las muestra como HTML estático sin fetch ni JS.
Version: 2.2
Author: Lucho Van Edgar
*/

// Shortcode que muestra el HTML cacheado
add_shortcode('replanta_trustpilot', function ($atts = []) {
    $atts = shortcode_atts(['color' => 'black'], $atts);
    $key = $atts['color'] === 'white' ? 'trustpilot_widget_cache_html_white' : 'trustpilot_widget_cache_html';
    $html = get_option($key, '');

    if (!$html) return '<p>Cargando reseñas...</p>';

    $class = $atts['color'] === 'white' ? 'trustpilot-widget-container blanco' : 'trustpilot-widget-container negro';
    $style = $atts['color'] === 'white' ? 'color: #E0E0E0;' : '';

    return '<div class="' . esc_attr($class) . '" style="' . esc_attr($style) . '">' . $html . '</div>';
});


// Hook para actualizar la caché (normal y blanco)
add_action('trustpilot_replanta_daily_update', 'trustpilot_replanta_update_cache');
function trustpilot_replanta_update_cache()
{
    $trustpilot_url = 'https://www.trustpilot.com/review/replanta.es';
    $response = wp_remote_get($trustpilot_url, [
        'timeout' => 10,
        'user-agent' => 'Mozilla/5.0',
    ]);

    if (is_wp_error($response)) return;

    $html_content = wp_remote_retrieve_body($response);
    if (empty($html_content)) return;

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html_content);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $total_reviews = $xpath->query('//p[contains(@class, "styles_reviewCount__NXlel")]');
    $trust_score   = $xpath->query('//p[@data-rating-typography="true"]');

    $reviews = $total_reviews->length > 0 ? trim($total_reviews->item(0)->nodeValue) : 'N/A';
    $score   = $trust_score->length > 0 ? trim($trust_score->item(0)->nodeValue) : 'N/A';

    $base = plugin_dir_url(__FILE__);

    foreach (['black' => '', 'white' => '-b'] as $mode => $suffix) {
        $html = '<a class="tp-widget-profile-link" target="_blank" href="https://es.trustpilot.com/review/replanta.es">
            <span class="tp-widget-logo">
                <img src="' . esc_url($base . 'img/trustpi' . $suffix . '.png') . '" alt="Trustpilot logo" style="max-width:80px;">
            </span>
            <span class="tp-widget-stars">
                <img src="' . esc_url($base . 'img/stars-4.5' . $suffix . '.png') . '" alt="Estrellas Trustpilot" style="max-width:170px;">
            </span>
            <p>TrustScore <strong>' . esc_html($score) . '</strong> <strong>' . esc_html($reviews) . '</strong></p>
        </a>';
    
        $key = $mode === 'white' ? 'trustpilot_widget_cache_html_white' : 'trustpilot_widget_cache_html';
        update_option($key, $html);
    }
    
}

// Activación del plugin: registrar cron y ejecutar una vez
register_activation_hook(__FILE__, function () {
    if (!wp_next_scheduled('trustpilot_replanta_daily_update')) {
        wp_schedule_event(time(), 'daily', 'trustpilot_replanta_daily_update');
    }
    do_action('trustpilot_replanta_daily_update'); // Ejecutar inmediatamente al activar
});
