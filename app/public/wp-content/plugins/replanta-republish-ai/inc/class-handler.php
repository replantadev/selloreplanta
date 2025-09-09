<?php

class Replanta_Republish_AI
{

    public static function handle_new_post($post_ID, $post)
    {
        // Evitar repeticiones
        if (get_post_meta($post_ID, '_rr_sent_to_ai', true)) return;

        $payload = [
            'title'     => get_the_title($post_ID),
            'url'       => get_permalink($post_ID),
            'excerpt'   => get_the_excerpt($post_ID),
            'content'   => apply_filters('the_content', $post->post_content),
            'categories' => wp_get_post_categories($post_ID, ['fields' => 'names']),
            'tags'      => wp_get_post_tags($post_ID, ['fields' => 'names']),
            'image'     => get_the_post_thumbnail_url($post_ID, 'full'),
        ];

        $response = wp_remote_post('https://replanta.net/medium-rr/replanta-medium', [
            'method'  => 'POST',
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode($payload),
            'timeout' => 20,
        ]);

        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $result = json_decode(wp_remote_retrieve_body($response), true);

            if ($result && isset($result['titulo'])) {
                update_post_meta($post_ID, '_rr_sent_to_ai', 1);
                update_post_meta($post_ID, '_rr_ai_title', $result['titulo']);
                update_post_meta($post_ID, '_rr_ai_content', $result['contenido']);
                update_post_meta($post_ID, '_rr_ai_summary', $result['resumen']);
                update_post_meta($post_ID, '_rr_ai_tags', implode(', ', $result['tags']));
                update_post_meta($post_ID, '_rr_ai_category', $result['categoria']);
                if (isset($result['medium_url'])) {
                    update_post_meta($post_ID, '_rr_ai_medium_url', esc_url_raw($result['medium_url']));
                }
            }
        }
    }

    public static function handle_new_post_devto($post_ID, $post)
{
    // Evita duplicación
    if (get_post_meta($post_ID, '_rr_sent_to_devto', true)) return;

    $payload = [
        'title'     => get_the_title($post_ID),
        'url'       => get_permalink($post_ID),
        'excerpt'   => get_the_excerpt($post_ID),
        'content'   => apply_filters('the_content', $post->post_content),
        'categories' => wp_get_post_categories($post_ID, ['fields' => 'names']),
        'tags'      => wp_get_post_tags($post_ID, ['fields' => 'names']),
        'image'     => get_the_post_thumbnail_url($post_ID, 'full'),
        'publish'   => false
    ];

    $response = wp_remote_post('https://replanta.dev/medium-rr/replanta-devto', [
        'method'  => 'POST',
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => json_encode($payload),
        'timeout' => 60,
    ]);

    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $result = json_decode(wp_remote_retrieve_body($response), true);

        if ($result && isset($result['titulo'])) {
            update_post_meta($post_ID, '_rr_sent_to_devto', 1);
            update_post_meta($post_ID, '_rr_ai_devto_title', $result['titulo']);
            update_post_meta($post_ID, '_rr_ai_devto_summary', $result['resumen']);
            update_post_meta($post_ID, '_rr_ai_devto_tags', implode(', ', $result['tags']));
            if (isset($result['devto_url'])) {
                update_post_meta($post_ID, '_rr_ai_devto_url', esc_url_raw($result['devto_url']));
            }
        }
    }
}


}
