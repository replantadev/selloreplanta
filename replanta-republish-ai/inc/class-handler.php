<?php
/**
 * Clase principal para manejar la republishing AI con múltiples plataformas
 * Versión: 1.4.3
 * Soporte: Medium, Dev.to, Hashnode, LinkedIn, Forocoches, Menéame
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class Replanta_Republish_AI {
    
    // Constante con plataformas soportadas
    const SUPPORTED_PLATFORMS = [
        'medium' => [
            'name' => 'Medium',
            'endpoint' => '/medium',
            'icon' => '📝',
            'status' => 'active'
        ],
        'devto' => [
            'name' => 'Dev.to',
            'endpoint' => '/devto',
            'icon' => '👩‍💻',
            'status' => 'active'
        ],
        'hashnode' => [
            'name' => 'Hashnode',
            'endpoint' => '/hashnode',
            'icon' => '#️⃣',
            'status' => 'planned'
        ],
        'linkedin' => [
            'name' => 'LinkedIn',
            'endpoint' => '/linkedin',
            'icon' => '💼',
            'status' => 'planned'
        ],
        'forocoches' => [
            'name' => 'Forocoches',
            'endpoint' => '/forocoches',
            'icon' => '🚗',
            'status' => 'planned'
        ],
        'meneame' => [
            'name' => 'Menéame',
            'endpoint' => '/meneame',
            'icon' => '📰',
            'status' => 'planned'
        ]
    ];

    /**
     * Enviar post a todas las plataformas activas mediante el servicio AI
     */
    public static function send_to_ai_service($post_id, $platforms = null) {
        $post = get_post($post_id);
        if (!$post) {
            rr_ai_log("Post no encontrado: $post_id", 'error');
            return false;
        }

        // Si no se especifican plataformas, usar todas las activas
        if (!$platforms) {
            $platforms = array_keys(array_filter(self::SUPPORTED_PLATFORMS, function($platform) {
                return $platform['status'] === 'active';
            }));
        }

        rr_ai_log("Iniciando envío multi-plataforma para post $post_id a: " . implode(', ', $platforms), 'info');

        // Obtener configuración
        $options = get_option('replanta_republish_ai_options', []);
        $microservice_url = '';
        
        // Priorizar microservice_url individual si existe, si no usar microservice_urls
        if (isset($options['microservice_url']) && !empty($options['microservice_url'])) {
            $microservice_url = rtrim($options['microservice_url'], '/');
        } elseif (isset($options['microservice_urls']) && !empty($options['microservice_urls'])) {
            // Tomar la primera línea de las URLs configuradas
            $urls = explode("\n", $options['microservice_urls']);
            $microservice_url = rtrim(trim($urls[0]), '/');
        }
        
        // URL por defecto si no está configurada
        if (empty($microservice_url)) {
            $microservice_url = 'https://replanta.dev/medium-rr';
            rr_ai_log("Usando URL por defecto del microservicio: $microservice_url", 'info');
        }

        // Preparar datos del post
        $post_data = [
            'title' => $post->post_title,
            'content' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'url' => get_permalink($post_id),
            'platforms' => $platforms,
            'tags' => wp_get_post_tags($post_id, ['fields' => 'names']),
            'categories' => wp_get_post_categories($post_id, ['fields' => 'names'])
        ];

        // Configurar API keys según las plataformas
        $api_keys = [];
        if (in_array('medium', $platforms)) {
            $api_keys['medium_token'] = isset($options['medium_token']) ? $options['medium_token'] : '';
        }
        if (in_array('devto', $platforms)) {
            $api_keys['devto_api_key'] = isset($options['devto_api_key']) ? $options['devto_api_key'] : '';
        }

        $post_data = array_merge($post_data, $api_keys);

        // URLs de respaldo
        $urls_to_try = [$microservice_url];
        if (!empty($options['fallback_urls'])) {
            $fallback_urls = array_map('trim', explode("\n", $options['fallback_urls']));
            $urls_to_try = array_merge($urls_to_try, $fallback_urls);
        }

        // Intentar envío a cada URL con endpoints específicos por plataforma
        foreach ($urls_to_try as $base_url) {
            $base_url = rtrim($base_url, '/');
            
            // Enviar a cada plataforma por separado
            $platform_results = [];
            $all_success = true;
            
            foreach ($platforms as $platform) {
                // Determinar endpoint específico
                $endpoint = '';
                switch($platform) {
                    case 'medium':
                        $endpoint = '/replanta-medium';
                        break;
                    case 'devto':
                        $endpoint = '/replanta-devto';
                        break;
                    default:
                        rr_ai_log("Plataforma no soportada: $platform", 'error');
                        $all_success = false;
                        continue;
                }
                
                $full_url = $base_url . $endpoint;
                rr_ai_log("Intentando envío a $platform en: $full_url", 'info');
                
                // Preparar datos específicos para esta plataforma
                $platform_data = $post_data;
                $platform_data['platforms'] = [$platform];

                $response = wp_remote_post($full_url, [
                    'body' => json_encode($platform_data),
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'User-Agent' => 'Replanta-Republish-AI/1.4.3'
                    ],
                    'timeout' => 60,
                    'sslverify' => false
                ]);

                if (is_wp_error($response)) {
                    rr_ai_log("Error WP en $platform ($full_url): " . $response->get_error_message(), 'error');
                    $all_success = false;
                    continue;
                }

                $response_code = wp_remote_retrieve_response_code($response);
                $response_body = wp_remote_retrieve_body($response);

                if ($response_code === 200) {
                    $result = json_decode($response_body, true);
                    
                    if ($result && isset($result['success']) && $result['success']) {
                        rr_ai_log("Envío exitoso a $platform desde: $full_url", 'info');
                        $platform_results[$platform] = $result;
                        
                        // Guardar metadatos
                        update_post_meta($post_id, "_rr_sent_to_{$platform}", date('Y-m-d H:i:s'));
                        if (isset($result['url'])) {
                            update_post_meta($post_id, "_rr_{$platform}_url", $result['url']);
                        }
                        if (isset($result['title'])) {
                            update_post_meta($post_id, "_rr_{$platform}_title", $result['title']);
                        }
                    } else {
                        $error_msg = isset($result['error']) ? $result['error'] : 'Error desconocido';
                        rr_ai_log("Error en respuesta de $platform: $error_msg", 'error');
                        $all_success = false;
                    }
                } else {
                    rr_ai_log("Código de respuesta $platform: $response_code - $response_body", 'error');
                    $all_success = false;
                }
            }
            
            // Si al menos una plataforma fue exitosa desde esta URL
            if (!empty($platform_results)) {
                update_post_meta($post_id, '_rr_sent_to_ai', date('Y-m-d H:i:s'));
                return true;
            }
        }

        rr_ai_log("Falló envío multi-plataforma a todas las URLs", 'error');
        update_post_meta($post_id, '_rr_ai_error', 'No se pudo conectar al servicio AI');
        return false;
    }

    /**
     * Enviar post a una plataforma específica
     */
    public static function send_to_platform($post_id, $platform_key) {
        if (!isset(self::SUPPORTED_PLATFORMS[$platform_key])) {
            rr_ai_log("Plataforma no soportada: $platform_key", 'error');
            return false;
        }

        $platform = self::SUPPORTED_PLATFORMS[$platform_key];
        $post = get_post($post_id);
        
        if (!$post) {
            rr_ai_log("Post no encontrado: $post_id", 'error');
            return false;
        }

        rr_ai_log("Enviando post $post_id a {$platform['name']}", 'info');

        // Obtener configuración
        $options = get_option('replanta_republish_ai_options', []);
        $microservice_url = '';
        
        // Priorizar microservice_url individual si existe, si no usar microservice_urls
        if (isset($options['microservice_url']) && !empty($options['microservice_url'])) {
            $microservice_url = rtrim($options['microservice_url'], '/');
        } elseif (isset($options['microservice_urls']) && !empty($options['microservice_urls'])) {
            // Tomar la primera línea de las URLs configuradas
            $urls = explode("\n", $options['microservice_urls']);
            $microservice_url = rtrim(trim($urls[0]), '/');
        }
        
        // URL por defecto si no está configurada
        if (empty($microservice_url)) {
            $microservice_url = 'https://replanta.dev/medium-rr';
            rr_ai_log("Usando URL por defecto del microservicio: $microservice_url", 'info');
        }

        // Preparar datos específicos de la plataforma
        $post_data = [
            'title' => $post->post_title,
            'content' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'url' => get_permalink($post_id),
            'platform' => $platform_key,
            'tags' => wp_get_post_tags($post_id, ['fields' => 'names']),
            'categories' => wp_get_post_categories($post_id, ['fields' => 'names'])
        ];

        // Agregar API keys necesarios
        if ($platform_key === 'medium' && isset($options['medium_token'])) {
            $post_data['medium_token'] = $options['medium_token'];
        }
        if ($platform_key === 'devto' && isset($options['devto_api_key'])) {
            $post_data['devto_api_key'] = $options['devto_api_key'];
        }

        // URLs de respaldo
        $urls_to_try = [$microservice_url];
        if (!empty($options['fallback_urls'])) {
            $fallback_urls = array_map('trim', explode("\n", $options['fallback_urls']));
            $urls_to_try = array_merge($urls_to_try, $fallback_urls);
        }

        // Intentar envío a cada URL
        foreach ($urls_to_try as $base_url) {
            $base_url = rtrim($base_url, '/');
            $full_url = $base_url . $platform['endpoint'];

            rr_ai_log("Intentando envío a {$platform['name']}: $full_url", 'info');

            $response = wp_remote_post($full_url, [
                'body' => json_encode($post_data),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Replanta-Republish-AI/1.4.3'
                ],
                'timeout' => 60,
                'sslverify' => false
            ]);

            if (is_wp_error($response)) {
                rr_ai_log("Error WP en {$platform['name']} ($full_url): " . $response->get_error_message(), 'error');
                continue;
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);

            if ($response_code === 200) {
                $result = json_decode($response_body, true);
                
                if ($result && isset($result['success']) && $result['success']) {
                    rr_ai_log("Envío exitoso a {$platform['name']} desde: $full_url", 'info');
                    
                    // Guardar metadatos
                    update_post_meta($post_id, "_rr_sent_to_{$platform_key}", date('Y-m-d H:i:s'));
                    if (isset($result['url'])) {
                        update_post_meta($post_id, "_rr_{$platform_key}_url", $result['url']);
                    }
                    if (isset($result['title'])) {
                        update_post_meta($post_id, "_rr_{$platform_key}_title", $result['title']);
                    }
                    
                    return [
                        'success' => true,
                        'platform' => $platform_key,
                        'url' => isset($result['url']) ? $result['url'] : null,
                        'title' => isset($result['title']) ? $result['title'] : null,
                        'message' => "Enviado exitosamente a {$platform['name']}"
                    ];
                } else {
                    $error_msg = isset($result['error']) ? $result['error'] : 'Error desconocido en respuesta';
                    rr_ai_log("Error en respuesta de {$platform['name']}: $error_msg", 'error');
                }
            } else {
                rr_ai_log("Código de respuesta de {$platform['name']}: $response_code - $response_body", 'error');
            }
        }

        rr_ai_log("Falló envío a {$platform['name']} en todas las URLs", 'error');
        return [
            'success' => false,
            'platform' => $platform_key,
            'error' => "No se pudo conectar al servicio para {$platform['name']}"
        ];
    }

    /**
     * Obtener plataformas soportadas
     */
    public static function get_supported_platforms() {
        return self::SUPPORTED_PLATFORMS;
    }

    /**
     * Obtener plataformas activas
     */
    public static function get_active_platforms() {
        return array_filter(self::SUPPORTED_PLATFORMS, function($platform) {
            return $platform['status'] === 'active';
        });
    }

    /**
     * Verificar si una plataforma está activa
     */
    public static function is_platform_active($platform_key) {
        return isset(self::SUPPORTED_PLATFORMS[$platform_key]) && 
               self::SUPPORTED_PLATFORMS[$platform_key]['status'] === 'active';
    }
}

// Función de logging mejorada
if (!function_exists('rr_ai_log')) {
    function rr_ai_log($message, $level = 'info') {
        $logs = get_option('rr_ai_debug_logs', []);
        
        $logs[] = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message
        ];
        
        // Mantener solo los últimos 100 logs
        if (count($logs) > 100) {
            $logs = array_slice($logs, -100);
        }
        
        update_option('rr_ai_debug_logs', $logs);
    }
}
