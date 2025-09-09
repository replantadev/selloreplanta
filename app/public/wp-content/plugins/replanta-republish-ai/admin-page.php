<?php

function replanta_republish_ai_admin_page()
{
    if (!current_user_can('manage_options')) return;

    echo '<div class="wrap"><h1>Replanta Republish AI</h1>';

    // Función auxiliar para Dev.to
    function sanitize_devto_tags($tags) {
        return array_map(function ($tag) {
            return strtolower(preg_replace('/[^a-z0-9]/', '', str_replace(' ', '', $tag)));
        }, $tags);
    }

    if (isset($_POST['replanta_post_id'])) {
        $post_id = intval($_POST['replanta_post_id']);
        $post = get_post($post_id);

        // Detectar destino
        $is_devto = isset($_POST['submit_devto']);
        $endpoint = $is_devto 
            ? 'https://replanta.dev/medium-rr/replanta-devto' 
            : 'https://replanta.dev/medium-rr/replanta-medium';

        $tags_raw = wp_get_post_tags($post_id, ['fields' => 'names']);
        $tags = $is_devto ? sanitize_devto_tags($tags_raw) : $tags_raw;

        $payload = [
            'title'      => get_the_title($post_id),
            'url'        => get_permalink($post_id),
            'excerpt'    => get_the_excerpt($post_id),
            'content'    => apply_filters('the_content', $post->post_content),
            'categories' => wp_get_post_categories($post_id, ['fields' => 'names']),
            'tags'       => $tags,
            'image'      => get_the_post_thumbnail_url($post_id, 'full'),
            'publish'    => false
        ];

        $response = wp_remote_post($endpoint, [
            'method'  => 'POST',
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode($payload),
            'timeout' => 60,
        ]);

        echo '<div style="margin-top:20px;">';

        if (!is_wp_error($response)) {
            $result = json_decode(wp_remote_retrieve_body($response), true);
            if (is_array($result)) {
                if (!empty($result['contenido']) || !empty($result['devto_url'])) {
                    echo '<h2>📝 Contenido generado por IA</h2>';
                    echo '<textarea style="width:100%; height:300px;">' . esc_textarea($result['contenido'] ?? '') . '</textarea>';
                    
                    if (!empty($result['medium_url'])) {
                        echo '<p style="color:green;">✅ Publicado en Medium: <a href="' . esc_url($result['medium_url']) . '" target="_blank">Ver artículo</a></p>';
                        update_post_meta($post_id, '_rr_ai_medium_url', esc_url_raw($result['medium_url']));
                    }

                    if (!empty($result['devto_url'])) {
                        echo '<p style="color:green;">✅ Publicado en Dev.to: <a href="' . esc_url($result['devto_url']) . '" target="_blank">Ver artículo</a></p>';
                        update_post_meta($post_id, '_rr_ai_devto_url', esc_url_raw($result['devto_url']));
                    }

                    update_post_meta($post_id, '_rr_sent_to_ai', 1);
                    update_post_meta($post_id, '_rr_ai_title', $result['titulo'] ?? '');
                    update_post_meta($post_id, '_rr_ai_summary', $result['resumen'] ?? '');
                    update_post_meta($post_id, '_rr_ai_tags', implode(', ', $tags_raw));
                    update_post_meta($post_id, '_rr_ai_category', $payload['categories']);
                } elseif (!empty($result['error'])) {
                    echo '<p style="color:red;">⚠️ Error: ' . esc_html($result['error']) . '</p>';
                    if (!empty($result['details'])) {
                        echo '<pre>' . esc_html($result['details']) . '</pre>';
                    }
                } else {
                    echo '<p style="color:red;">⚠️ Respuesta inesperada del microservicio (estructura no esperada).</p>';
                    echo '<pre>' . print_r($result, true) . '</pre>';
                }
            } else {
                echo '<p style="color:red;">⚠️ Error: respuesta no válida o JSON malformado.</p>';
                echo '<pre>' . esc_html(wp_remote_retrieve_body($response)) . '</pre>';
            }
        } else {
            echo '<p style="color:red;">❌ Error de conexión: ' . esc_html($response->get_error_message()) . '</p>';
        }

        echo '</div>';
    }

    // Mostrar formulario
    $recent_posts = wp_get_recent_posts([
        'numberposts' => 5,
        'post_status' => 'publish',
    ]);

    echo '<form method="post"><label for="replanta_post_id">Selecciona un post:</label><br>';
    echo '<select name="replanta_post_id">';
    foreach ($recent_posts as $post) {
        echo '<option value="' . esc_attr($post['ID']) . '">' . esc_html($post['post_title']) . '</option>';
    }
    echo '</select><br><br>
    <input type="submit" class="button button-primary" name="submit_medium" value="Enviar a Medium">
    <input type="submit" class="button" name="submit_devto" value="Enviar a Dev.to"></form>';

    echo '</div>';
}

