<?php
/**
 * Main handler class for Replanta Republish AI
 */

if (!defined('ABSPATH')) {
    exit;
}

class Replanta_Republish_AI {
    
    /**
     * Handle new post publication
     */
    public static function handle_new_post($post_id, $post) {
        // Skip if not published
        if ($post->post_status !== 'publish') {
            return;
        }
        
        // Skip if already sent
        if (get_post_meta($post_id, '_rr_sent_to_ai', true)) {
            return;
        }
        
        // Check if auto-publish is enabled
        $options = get_option('replanta_republish_ai_options', []);
        if (!isset($options['auto_publish']) || $options['auto_publish'] != '1') {
            return;
        }
        
        // Send to AI service
        self::send_to_ai_service($post_id, $post);
    }
    
    /**
     * Send post to AI service
     */
    private static function send_to_ai_service($post_id, $post) {
        $options = get_option('replanta_republish_ai_options', []);
        
        if (empty($options['openai_api_key'])) {
            rr_ai_log('No OpenAI API key configured', 'error');
            return;
        }
        
        $urls = self::get_microservice_urls();
        if (empty($urls)) {
            rr_ai_log('No microservice URLs configured', 'error');
            return;
        }
        
        $payload = [
            'post_id' => $post_id,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'url' => get_permalink($post_id),
            'author' => get_the_author_meta('display_name', $post->post_author),
            'date' => $post->post_date,
            'openai_key' => $options['openai_api_key'],
            'medium_token' => isset($options['medium_integration_token']) ? $options['medium_integration_token'] : ''
        ];
        
        $success = false;
        $last_error = '';
        
        foreach ($urls as $url) {
            rr_ai_log("Attempting to send post $post_id to $url", 'info');
            
            $response = wp_remote_post($url, [
                'method' => 'POST',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Replanta Republish AI v' . RREPLANTA_AI_VERSION
                ],
                'body' => json_encode($payload),
                'timeout' => 60
            ]);
            
            if (!is_wp_error($response)) {
                $http_code = wp_remote_retrieve_response_code($response);
                $response_body = wp_remote_retrieve_body($response);
                
                rr_ai_log("Response from $url: HTTP $http_code", 'info');
                
                if ($http_code == 200) {
                    $data = json_decode($response_body, true);
                    if ($data && isset($data['success']) && $data['success']) {
                        update_post_meta($post_id, '_rr_sent_to_ai', current_time('mysql'));
                        if (isset($data['medium_url'])) {
                            update_post_meta($post_id, '_rr_ai_medium_url', $data['medium_url']);
                        }
                        if (isset($data['ai_title'])) {
                            update_post_meta($post_id, '_rr_ai_title', $data['ai_title']);
                        }
                        rr_ai_log("Post $post_id successfully sent to AI service", 'info');
                        $success = true;
                        break;
                    } else {
                        $last_error = isset($data['error']) ? $data['error'] : 'Unknown error';
                    }
                } else {
                    $last_error = "HTTP $http_code: $response_body";
                }
            } else {
                $last_error = $response->get_error_message();
            }
        }
        
        if (!$success) {
            update_post_meta($post_id, '_rr_ai_error', $last_error);
            rr_ai_log("Failed to send post $post_id: $last_error", 'error');
        }
    }
    
    /**
     * Get microservice URLs
     */
    public static function get_microservice_urls() {
        $options = get_option('replanta_republish_ai_options', []);
        
        if (isset($options['microservice_urls'])) {
            $urls = array_filter(array_map('trim', explode("\n", $options['microservice_urls'])));
            return $urls;
        }
        
        // Default URLs - Microservicio Python Flask
        return [
            'https://replanta.dev/medium-rr/',
            'https://replanta.net/medium-rr/'
        ];
    }
}