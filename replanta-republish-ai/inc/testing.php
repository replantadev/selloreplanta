<?php
/**
 * Sistema de Testing Automático para Replanta Republish AI
 * Ejecuta una serie de tests para verificar el funcionamiento del plugin
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class Replanta_Republish_AI_Test {
    
    private $results = [];
    
    public function run_all_tests() {
        echo "<div class='wrap'>";
        echo "<h1>🧪 Sistema de Testing - Replanta Republish AI</h1>";
        
        $this->test_class_loading();
        $this->test_configuration();
        $this->test_database_functions();
        $this->test_platform_support();
        $this->test_microservice_connection();
        
        $this->show_summary();
        echo "</div>";
    }
    
    private function test_class_loading() {
        echo "<div class='card' style='margin: 10px 0;'>";
        echo "<h2>🔧 Test de Carga de Clases</h2>";
        
        $test_name = "Class Loading";
        $success = true;
        $messages = [];
        
        // Verificar que la clase principal existe
        if (class_exists('Replanta_Republish_AI')) {
            $messages[] = "✅ Clase Replanta_Republish_AI cargada correctamente";
            
            // Verificar métodos principales
            $required_methods = ['send_to_ai_service', 'send_to_platform', 'get_supported_platforms'];
            foreach ($required_methods as $method) {
                if (method_exists('Replanta_Republish_AI', $method)) {
                    $messages[] = "✅ Método $method disponible";
                } else {
                    $messages[] = "❌ Método $method no encontrado";
                    $success = false;
                }
            }
        } else {
            $messages[] = "❌ Clase Replanta_Republish_AI no encontrada";
            $success = false;
        }
        
        $this->show_test_result($test_name, $success, $messages);
        echo "</div>";
    }
    
    private function test_configuration() {
        echo "<div class='card' style='margin: 10px 0;'>";
        echo "<h2>⚙️ Test de Configuración</h2>";
        
        $test_name = "Configuration";
        $success = true;
        $messages = [];
        
        $options = get_option('replanta_republish_ai_options', []);
        
        // Verificar URLs del microservicio
        if (isset($options['microservice_urls']) && !empty($options['microservice_urls'])) {
            $messages[] = "✅ URLs del microservicio configuradas";
            $urls = explode("\n", $options['microservice_urls']);
            $messages[] = "📝 URLs encontradas: " . count($urls);
        } else {
            $messages[] = "⚠️ URLs del microservicio no configuradas (se usará por defecto)";
        }
        
        // Verificar tokens de API
        $tokens = ['medium_integration_token', 'devto_api_key', 'openai_api_key'];
        foreach ($tokens as $token) {
            if (isset($options[$token]) && !empty($options[$token])) {
                $messages[] = "✅ $token configurado";
            } else {
                $messages[] = "⚠️ $token no configurado";
            }
        }
        
        $this->show_test_result($test_name, $success, $messages);
        echo "</div>";
    }
    
    private function test_database_functions() {
        echo "<div class='card' style='margin: 10px 0;'>";
        echo "<h2>🗄️ Test de Funciones de Base de Datos</h2>";
        
        $test_name = "Database Functions";
        $success = true;
        $messages = [];
        
        try {
            // Test get_sent_posts
            if (function_exists('get_sent_posts')) {
                $sent_posts = get_sent_posts(1);
                $messages[] = "✅ Función get_sent_posts funciona - " . count($sent_posts) . " posts encontrados";
            } else {
                $messages[] = "❌ Función get_sent_posts no encontrada";
                $success = false;
            }
            
            // Test get_pending_posts
            if (function_exists('get_pending_posts')) {
                $pending_posts = get_pending_posts(1);
                $messages[] = "✅ Función get_pending_posts funciona - " . count($pending_posts) . " posts encontrados";
            } else {
                $messages[] = "❌ Función get_pending_posts no encontrada";
                $success = false;
            }
            
            // Test get_error_posts
            if (function_exists('get_error_posts')) {
                $error_posts = get_error_posts(1);
                $messages[] = "✅ Función get_error_posts funciona - " . count($error_posts) . " posts encontrados";
            } else {
                $messages[] = "❌ Función get_error_posts no encontrada";
                $success = false;
            }
            
        } catch (Exception $e) {
            $messages[] = "❌ Error en funciones de base de datos: " . $e->getMessage();
            $success = false;
        }
        
        $this->show_test_result($test_name, $success, $messages);
        echo "</div>";
    }
    
    private function test_platform_support() {
        echo "<div class='card' style='margin: 10px 0;'>";
        echo "<h2>🌐 Test de Soporte de Plataformas</h2>";
        
        $test_name = "Platform Support";
        $success = true;
        $messages = [];
        
        try {
            if (class_exists('Replanta_Republish_AI')) {
                $platforms = Replanta_Republish_AI::get_supported_platforms();
                
                if (!empty($platforms)) {
                    $messages[] = "✅ " . count($platforms) . " plataformas soportadas";
                    
                    foreach ($platforms as $key => $platform) {
                        $status = isset($platform['status']) ? $platform['status'] : 'unknown';
                        $icon = isset($platform['icon']) ? $platform['icon'] : '❓';
                        $name = isset($platform['name']) ? $platform['name'] : $key;
                        
                        $messages[] = "$icon $name - Status: $status";
                    }
                    
                    // Contar activas
                    $active = array_filter($platforms, function($p) { return isset($p['status']) && $p['status'] === 'active'; });
                    $messages[] = "✅ " . count($active) . " plataformas activas";
                    
                } else {
                    $messages[] = "❌ No se encontraron plataformas soportadas";
                    $success = false;
                }
            } else {
                $messages[] = "❌ No se pudo verificar plataformas - clase no disponible";
                $success = false;
            }
        } catch (Exception $e) {
            $messages[] = "❌ Error verificando plataformas: " . $e->getMessage();
            $success = false;
        }
        
        $this->show_test_result($test_name, $success, $messages);
        echo "</div>";
    }
    
    private function test_microservice_connection() {
        echo "<div class='card' style='margin: 10px 0;'>";
        echo "<h2>🌐 Test de Conexión al Microservicio</h2>";
        
        $test_name = "Microservice Connection";
        $success = true;
        $messages = [];
        
        $options = get_option('replanta_republish_ai_options', []);
        $microservice_url = '';
        
        // Obtener URL del microservicio
        if (isset($options['microservice_url']) && !empty($options['microservice_url'])) {
            $microservice_url = rtrim($options['microservice_url'], '/');
        } elseif (isset($options['microservice_urls']) && !empty($options['microservice_urls'])) {
            $urls = explode("\n", $options['microservice_urls']);
            $microservice_url = rtrim(trim($urls[0]), '/');
        } else {
            $microservice_url = 'https://replanta.dev/medium-rr';
        }
        
        $messages[] = "🔗 Usando URL: $microservice_url";
        
        // Test básico de conectividad
        $test_url = $microservice_url . '/test';
        $response = wp_remote_get($test_url, ['timeout' => 10]);
        
        if (is_wp_error($response)) {
            $messages[] = "❌ Error de conexión: " . $response->get_error_message();
            $success = false;
        } else {
            $status_code = wp_remote_retrieve_response_code($response);
            $messages[] = "📡 Código de respuesta: $status_code";
            
            if ($status_code >= 200 && $status_code < 400) {
                $messages[] = "✅ Conexión al microservicio exitosa";
            } else {
                $messages[] = "⚠️ Microservicio responde pero con código $status_code";
            }
        }
        
        $this->show_test_result($test_name, $success, $messages);
        echo "</div>";
    }
    
    private function show_test_result($test_name, $success, $messages) {
        $this->results[$test_name] = $success;
        
        $status_color = $success ? '#46b450' : '#dc3232';
        $status_icon = $success ? '✅' : '❌';
        $status_text = $success ? 'PASSED' : 'FAILED';
        
        echo "<div style='padding: 10px; margin: 10px 0; border-left: 4px solid $status_color;'>";
        echo "<h3 style='margin: 0; color: $status_color;'>$status_icon $test_name - $status_text</h3>";
        echo "<ul>";
        foreach ($messages as $message) {
            echo "<li>$message</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    private function show_summary() {
        $total = count($this->results);
        $passed = count(array_filter($this->results));
        $failed = $total - $passed;
        
        $overall_status = $failed === 0 ? 'SUCCESS' : 'PARTIAL';
        $color = $failed === 0 ? '#46b450' : ($failed < $total ? '#ffb900' : '#dc3232');
        
        echo "<div class='card' style='margin: 20px 0; border: 2px solid $color;'>";
        echo "<h2 style='color: $color;'>📊 Resumen de Tests - $overall_status</h2>";
        echo "<div style='padding: 20px; font-size: 16px;'>";
        echo "<p><strong>Total de tests:</strong> $total</p>";
        echo "<p style='color: #46b450;'><strong>✅ Exitosos:</strong> $passed</p>";
        if ($failed > 0) {
            echo "<p style='color: #dc3232;'><strong>❌ Fallidos:</strong> $failed</p>";
        }
        
        if ($failed === 0) {
            echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h3 style='color: #155724; margin: 0;'>🎉 ¡Plugin Completamente Funcional!</h3>";
            echo "<p style='color: #155724; margin: 5px 0;'>Todos los tests pasaron exitosamente. El plugin está listo para usar.</p>";
            echo "</div>";
        } else {
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h3 style='color: #856404; margin: 0;'>⚠️ Plugin Funcionando con Advertencias</h3>";
            echo "<p style='color: #856404; margin: 5px 0;'>Algunos tests fallaron, pero la funcionalidad básica está disponible.</p>";
            echo "</div>";
        }
        
        echo "</div>";
        echo "</div>";
    }
}

// Función para mostrar la página de testing
function replanta_testing_page() {
    if (!current_user_can('manage_options')) return;
    
    $tester = new Replanta_Republish_AI_Test();
    $tester->run_all_tests();
}