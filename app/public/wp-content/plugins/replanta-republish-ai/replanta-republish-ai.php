<?php

/**
 * Plugin Name: Replanta Republish AI
 * Description: Genera versiones para Medium (y otros) usando OpenAI al publicar un post.
 * Version: 0.1
 * Author: Replanta
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'inc/class-handler.php';
require_once plugin_dir_path(__FILE__) . 'admin-page.php';
// Hook a publicación de posts
add_action('publish_post', ['Replanta_Republish_AI', 'handle_new_post'], 10, 2);

add_action('admin_menu', function () {
    add_menu_page(
        'Replanta Republish AI',
        'Republish AI',
        'manage_options',
        'replanta-republish-ai',
        'replanta_republish_ai_admin_page'
    );
});


add_action('add_meta_boxes', function () {
    add_meta_box('rr_ai_meta', '🧠 Republish AI Info', function ($post) {
        echo '<p><strong>📝 Título generado:</strong><br>' . esc_html(get_post_meta($post->ID, '_rr_ai_title', true)) . '</p>';
        echo '<p><strong>📌 Resumen:</strong><br>' . esc_html(get_post_meta($post->ID, '_rr_ai_summary', true)) . '</p>';
        echo '<p><strong>🔗 Medium:</strong><br>';
        echo '<a href="' . esc_url(get_post_meta($post->ID, '_rr_ai_medium_url', true)) . '" target="_blank">Ver publicación</a></p>';
        echo '<p><strong>📄 Tags:</strong> ' . esc_html(get_post_meta($post->ID, '_rr_ai_tags', true)) . '</p>';
        echo '<p><strong>📂 Categoría:</strong> ' . esc_html(implode(', ', (array)get_post_meta($post->ID, '_rr_ai_category', true))) . '</p>';
        echo '<hr>';
        echo '<p><strong>🔗 Dev.to:</strong><br>';
        $devto_url = get_post_meta($post->ID, '_rr_ai_devto_url', true);
        if ($devto_url) {
            echo '<a href="' . esc_url($devto_url) . '" target="_blank">Ver publicación en Dev.to</a></p>';
        } else {
            echo '<span style="color:gray;">No enviado</span></p>';
        }
    }, 'post', 'side', 'high');
});

add_filter('manage_posts_columns', function ($columns) {
    $columns['rr_sent_ai'] = 'Republish AI';
    return $columns;
});

add_action('manage_posts_custom_column', function ($column, $post_id) {
    if ($column === 'rr_sent_ai') {
        if ($column === 'rr_sent_ai') {
            $medium = get_post_meta($post_id, '_rr_ai_medium_url', true);
            $devto  = get_post_meta($post_id, '_rr_ai_devto_url', true);

            if ($medium || $devto) {
                if ($medium) echo '<a href="' . esc_url($medium) . '" target="_blank">Medium</a><br>';
                if ($devto)  echo '<a href="' . esc_url($devto)  . '" target="_blank">Dev.to</a>';
            } else {
                echo '<span style="color:gray;">No</span>';
            }
        }
    }
}, 10, 2);
