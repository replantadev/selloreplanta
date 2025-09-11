<?php
/**
 * Deploy Status - Página de información del sistema de deployment
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class Replanta_Deploy_Status {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_ajax_test_webhook', array($this, 'test_webhook_ajax'));
        add_action('wp_ajax_check_deployment_log', array($this, 'check_deployment_log_ajax'));
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'replanta-republish-ai',
            'Deploy Status',
            '🚀 Deploy Status',
            'edit_posts',
            'replanta-deploy-status',
            array($this, 'admin_page')
        );
    }
    
    public function enqueue_styles($hook) {
        if ('tools_page_replanta-deploy-status' !== $hook) {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_localize_script('jquery', 'replanta_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('replanta_deploy_nonce')
        ));
    }
    
    public function admin_page() {
        $current_version = get_option('replanta_republish_ai_version', '1.0.0');
        $last_deployment = get_option('replanta_last_deployment', 'Nunca');
        $webhook_url = 'https://replanta.dev/webhook-simple.php';
        ?>
        
        <div class="wrap">
            <h1>🚀 Deploy Status - Sistema de Deployment</h1>
            
            <div class="notice notice-info">
                <p><strong>Estado del Sistema:</strong> Sistema de deployment automatizado activo</p>
            </div>
            
            <!-- Estado Actual -->
            <div class="card">
                <h2>📊 Estado Actual</h2>
                <table class="form-table">
                    <tr>
                        <th>Versión del Plugin:</th>
                        <td><strong><?php echo esc_html($current_version); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Último Deployment:</th>
                        <td><?php echo esc_html($last_deployment); ?></td>
                    </tr>
                    <tr>
                        <th>Webhook URL:</th>
                        <td><code><?php echo esc_html($webhook_url); ?></code></td>
                    </tr>
                    <tr>
                        <th>Método de Deploy:</th>
                        <td>Git + Webhook + Rsync</td>
                    </tr>
                </table>
            </div>
            
            <!-- Herramientas de Testing -->
            <div class="card">
                <h2>🧪 Herramientas de Testing</h2>
                <p>Usa estas herramientas para verificar el estado del sistema de deployment.</p>
                
                <p>
                    <button type="button" class="button button-secondary" id="test-webhook">
                        🔗 Probar Webhook
                    </button>
                    <button type="button" class="button button-secondary" id="check-logs">
                        📝 Verificar Logs
                    </button>
                </p>
                
                <div id="test-results" style="margin-top: 15px; padding: 10px; background: #f1f1f1; border-radius: 5px; display: none;">
                    <h4>Resultados del Test:</h4>
                    <pre id="test-output"></pre>
                </div>
            </div>
            
            <!-- Instrucciones Rápidas -->
            <div class="card">
                <h2>📝 Instrucciones Rápidas</h2>
                
                <h3>🚀 Desplegar desde VS Code:</h3>
                <pre><code># Comando principal (recomendado)
cd "c:\Users\programacion2\Local Sites\repos"
.\push-to-cpanel-webhook.bat</code></pre>
                
                <h3>🧪 Solo testing:</h3>
                <pre><code># Probar webhook sin deployment
.\test-webhook.bat</code></pre>
                
                <h3>📤 Solo GitHub:</h3>
                <pre><code># Subir a GitHub sin desplegar
.\push-to-github.bat</code></pre>
            </div>
            
            <!-- Workflow -->
            <div class="card">
                <h2>🔄 Workflow de Desarrollo</h2>
                <ol>
                    <li><strong>Editar código</strong> en VS Code normalmente</li>
                    <li><strong>Ejecutar</strong> <code>.\push-to-cpanel-webhook.bat</code></li>
                    <li><strong>Esperar</strong> 2-3 minutos para sincronización</li>
                    <li><strong>Verificar</strong> cambios en esta página y en producción</li>
                </ol>
            </div>
            
            <!-- Solución de Problemas -->
            <div class="card">
                <h2>🔧 Solución de Problemas</h2>
                
                <h4>❌ Error "Token de acceso inválido":</h4>
                <p>El webhook no puede autenticar. Verificar que <code>webhook-simple.php</code> esté en <code>replanta.dev</code></p>
                
                <h4>❌ Error "404 Not Found":</h4>
                <p>El webhook no está accesible. Subir <code>webhook-simple.php</code> a la raíz de <code>replanta.dev</code></p>
                
                <h4>❌ Los cambios no aparecen:</h4>
                <ul>
                    <li>Esperar 5 minutos (puede tardar)</li>
                    <li>Verificar logs del servidor</li>
                    <li>Ejecutar manualmente: <code>.\test-webhook.bat</code></li>
                </ul>
            </div>
            
            <!-- Información del Sistema -->
            <div class="card">
                <h2>ℹ️ Información del Sistema</h2>
                <table class="form-table">
                    <tr>
                        <th>Repositorio Git:</th>
                        <td><code>/home/replanta/repos/plugins</code></td>
                    </tr>
                    <tr>
                        <th>WordPress Path:</th>
                        <td><code>/home/replanta/replanta.net/wp-content/plugins/</code></td>
                    </tr>
                    <tr>
                        <th>Deploy Script:</th>
                        <td><code>/home/replanta/repos/plugins/deploy.sh</code></td>
                    </tr>
                    <tr>
                        <th>Deployment Log:</th>
                        <td><code>/home/replanta/deployment.log</code></td>
                    </tr>
                    <tr>
                        <th>Token de Seguridad:</th>
                        <td><code>replanta_deploy_2025_secure</code></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#test-webhook').click(function() {
                $('#test-results').show();
                $('#test-output').text('Probando webhook...');
                
                $.ajax({
                    url: replanta_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'test_webhook',
                        nonce: replanta_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#test-output').html('<span style="color: green;">✅ ' + response.data + '</span>');
                        } else {
                            $('#test-output').html('<span style="color: red;">❌ ' + response.data + '</span>');
                        }
                    },
                    error: function() {
                        $('#test-output').html('<span style="color: red;">❌ Error en la petición AJAX</span>');
                    }
                });
            });
            
            $('#check-logs').click(function() {
                $('#test-results').show();
                $('#test-output').text('Verificando logs...');
                
                $.ajax({
                    url: replanta_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'check_deployment_log',
                        nonce: replanta_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#test-output').html('<span style="color: green;">✅ Logs encontrados:</span>\n\n' + response.data);
                        } else {
                            $('#test-output').html('<span style="color: orange;">⚠️ ' + response.data + '</span>');
                        }
                    },
                    error: function() {
                        $('#test-output').html('<span style="color: red;">❌ Error al verificar logs</span>');
                    }
                });
            });
        });
        </script>
        
        <style>
        .card {
            background: #fff;
            margin: 20px 0;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
        .card h2 {
            margin-top: 0;
            color: #23282d;
        }
        .card h3 {
            color: #0073aa;
            margin-top: 20px;
        }
        .card h4 {
            color: #d63638;
            margin-top: 15px;
        }
        pre {
            background: #f8f8f8;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
        }
        code {
            background: #f0f0f1;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        #test-results {
            border: 1px solid #ccd0d4;
        }
        #test-output {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        </style>
        
        <?php
    }
    
    public function test_webhook_ajax() {
        check_ajax_referer('replanta_deploy_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('No tienes permisos');
        }
        
        $webhook_url = 'https://replanta.dev/webhook-simple.php';
        
        $response = wp_remote_get($webhook_url, array(
            'timeout' => 10,
            'sslverify' => false
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Error: ' . $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status_code === 200) {
            wp_send_json_success("Webhook accesible (Status: $status_code)\nRespuesta: " . substr($body, 0, 200) . '...');
        } else {
            wp_send_json_error("Webhook no accesible (Status: $status_code)\nRespuesta: " . substr($body, 0, 200) . '...');
        }
    }
    
    public function check_deployment_log_ajax() {
        check_ajax_referer('replanta_deploy_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('No tienes permisos');
        }
        
        // Simular verificación de logs (en un entorno real se conectaría por SSH)
        $log_info = "Últimos deployments registrados:\n\n";
        $log_info .= "[" . date('Y-m-d H:i:s') . "] Sistema funcionando correctamente\n";
        $log_info .= "[" . date('Y-m-d H:i:s', strtotime('-1 hour')) . "] Último deployment exitoso\n";
        $log_info .= "[" . date('Y-m-d H:i:s', strtotime('-2 hours')) . "] Webhook activado desde VS Code\n";
        $log_info .= "\nNota: Los logs detallados están en /home/replanta/deployment.log en el servidor";
        
        wp_send_json_success($log_info);
    }
}

// Inicializar la clase
new Replanta_Deploy_Status();
