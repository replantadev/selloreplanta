<?php
/**
 * Deploy Status - Función de página simple para estado del deployment
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

function replanta_deploy_status_page() {
    $current_version = get_option('replanta_republish_ai_version', '1.4.3');
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
        
        <!-- Estado del Microservicio -->
        <div class="card">
            <h2>🔧 Estado del Microservicio</h2>
            <p>Estado actual del microservicio Python Flask:</p>
            <ul>
                <li>🐍 <strong>Microservicio Python:</strong> <code>https://replanta.dev/medium-rr/</code></li>
                <li>📁 <strong>Archivo principal:</strong> <code>app.py</code> (13,547 bytes)</li>
                <li>⚙️ <strong>Configuración WSGI:</strong> <code>passenger_wsgi.py</code></li>
                <li>📋 <strong>Logs disponibles:</strong> <code>debug.log</code> y <code>stderr.log</code></li>
            </ul>
            
            <p><a href="<?php echo admin_url('admin.php?page=replanta-republish-ai-diagnosis'); ?>" class="button button-primary">🔍 Probar Microservicio</a></p>
        </div>

        <!-- Acciones de Republishing -->
        <div class="card">
            <h2>📤 Republicar Artículos</h2>
            <p>Gestiona la republicación de posts en Medium:</p>
            
            <div style="display: flex; gap: 15px; margin: 20px 0;">
                <a href="<?php echo admin_url('admin.php?page=replanta-republish-ai-recovery'); ?>" class="button button-primary">
                    🔄 Recuperar Posts Fallidos
                </a>
                <a href="<?php echo admin_url('admin.php?page=replanta-republish-ai-diagnosis'); ?>" class="button button-secondary">
                    🔍 Diagnosticar Conexión
                </a>
                <a href="<?php echo admin_url('admin.php?page=replanta-republish-ai-debug'); ?>" class="button button-secondary">
                    📋 Ver Logs
                </a>
            </div>
            
            <div class="notice notice-warning inline">
                <p><strong>💡 Tip:</strong> Si algunos posts no se han enviado a Medium, usa "Recuperar Posts Fallidos" para reintentarlos.</p>
            </div>
        </div>

        <!-- Instrucciones Rápidas -->
        <div class="card">
            <h2>📝 Instrucciones de Desarrollo</h2>
            
            <h3>🚀 Desplegar desde VS Code:</h3>
            <pre><code># Comando principal (recomendado)
cd "c:\Users\programacion2\Local Sites\repos"
.\push-to-cpanel-webhook.bat</code></pre>
            
            <h3>🧪 Solo testing:</h3>
            <pre><code># Probar webhook sin deployment
.\test-webhook.bat</code></pre>
            
            <h3>🔧 Comandos útiles:</h3>
            <pre><code># Ver estado del microservicio
curl https://replanta.dev/medium-rr/

# Ver logs del microservicio
tail -f /home/replanta/public_html/medium-rr/debug.log</code></pre>
        </div>

        <!-- Problema Detectado -->
        <div class="card" style="border-left: 4px solid #dc3232;">
            <h2>⚠️ Problema Detectado en Logs</h2>
            <p>Los logs muestran que el microservicio está devolviendo 404:</p>
            <ul>
                <li>❌ <code>https://replanta.dev/medium-rr/</code> - HTTP 404</li>
                <li>❌ <code>https://replanta.net/medium-rr/</code> - HTTP 404</li>
                <li>✅ <code>https://replanta.dev/medium-rr/app.py</code> - HTTP 200 (archivo existe)</li>
            </ul>
            
            <div class="notice notice-error inline">
                <p><strong>🔧 Acción necesaria:</strong> El microservicio Python no está configurado correctamente en el servidor. 
                Es necesario verificar la configuración WSGI en cPanel.</p>
            </div>
            
            <h3>📋 Pasos para solucionar:</h3>
            <ol>
                <li>Verificar que <code>passenger_wsgi.py</code> esté en la raíz de <code>/medium-rr/</code></li>
                <li>Comprobar que el directorio <code>medium-rr</code> esté configurado como aplicación Python en cPanel</li>
                <li>Revisar los logs de errores del servidor</li>
                <li>Verificar que todas las dependencias Python estén instaladas</li>
            </ol>
        </div>
    </div>
    
    <?php
}
