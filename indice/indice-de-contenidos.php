<?php
/**
 * Plugin Name: Índice de Contenidos
 * Description: Genera un índice enlazable de títulos en la página a través del shortcode [indice].
 * Version: 1.4
 * Text Domain: indice-de-contenidos
 */                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        
/**
* Note: This file may contain artifacts of previous malicious infection.
* However, the dangerous code has been removed, and the file is now safe to use.
*/


if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('indice', 'generar_indice');
add_filter('the_posts', 'verificar_shortcode_en_posts'); // Verifica la presencia del shortcode.

function generar_indice() {
    ob_start();
    ?>
    <div id="indice-de-contenidos" class="lista-indice"><span>Índice</span></div>
    <?php
    return ob_get_clean();
}

function indice_condicional_recursos() {
    wp_enqueue_script('indice-js', plugin_dir_url(__FILE__) . 'indice.js', array(), '1.0', true);
    wp_enqueue_style('indice-css', plugin_dir_url(__FILE__) . 'indice.css');
}

function verificar_shortcode_en_posts($posts) {
    if (empty($posts)) {
        return $posts;
    }

    $shortcode_presente = false;

    // Busca el shortcode [indice] en cada post.
    foreach ($posts as $post) {
        if (has_shortcode($post->post_content, 'indice')) {
            $shortcode_presente = true;
            break; // Si encuentra el shortcode, no necesita buscar más.
        }
    }

    // Si el shortcode está presente, encola los scripts y estilos.
    if ($shortcode_presente) {
        add_action('wp_enqueue_scripts', 'indice_condicional_recursos');
    }

    return $posts;
}