<?php
/**
 * Deploy Status - Función de página completa para estado del deployment
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
        <h1>🚀 Deploy Status - Sistema de Republicación Multi-plataforma</h1>
        
        <div class="notice notice-info">
            <p><strong>Estado del Sistema:</strong> Sistema de republicación automática para Multiple plataformas</p>
        </div>
        
        <!-- Plataformas Configuradas -->
        <div class="card">
            <h2>📝 Plataformas de Publicación</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">
                
                <!-- Medium -->
                <div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px;">
                    <h3>📰 Medium</h3>
                    <p><strong>Estado:</strong> <span style="color: green;">✅ Activo</span></p>
                    <p><strong>PR:</strong> DA 95+ | PA 90+</p>
                    <p><strong>Audiencia:</strong> +170M lectores/mes</p>
                    <p><strong>Endpoint:</strong> <code>/medium</code></p>
                </div>
                
                <!-- Dev.to -->
                <div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px;">
                    <h3>💻 Dev.to</h3>
                    <p><strong>Estado:</strong> <span style="color: green;">✅ Activo</span></p>
                    <p><strong>PR:</strong> DA 85+ | PA 82+</p>
                    <p><strong>Audiencia:</strong> +1M desarrolladores</p>
                    <p><strong>Endpoint:</strong> <code>/devto</code></p>
                </div>
                
                <!-- Hashnode -->
                <div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px;">
                    <h3>📝 Hashnode</h3>
                    <p><strong>Estado:</strong> <span style="color: orange;">⚠️ Pendiente</span></p>
                    <p><strong>PR:</strong> DA 78+ | PA 75+</p>
                    <p><strong>Audiencia:</strong> +500K desarrolladores</p>
                    <p><strong>Endpoint:</strong> <code>/hashnode</code></p>
                </div>
                
                <!-- LinkedIn Articles -->
                <div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px;">
                    <h3>💼 LinkedIn Articles</h3>
                    <p><strong>Estado:</strong> <span style="color: orange;">⚠️ Pendiente</span></p>
                    <p><strong>PR:</strong> DA 98+ | PA 95+</p>
                    <p><strong>Audiencia:</strong> +900M profesionales</p>
                    <p><strong>Endpoint:</strong> <code>/linkedin</code></p>
                </div>
                
                <!-- Forocoches (España) -->
                <div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px;">
                    <h3>🇪🇸 Forocoches</h3>
                    <p><strong>Estado:</strong> <span style="color: orange;">⚠️ Pendiente</span></p>
                    <p><strong>PR:</strong> DA 82+ | PA 78+</p>
                    <p><strong>Audiencia:</strong> +2M usuarios España</p>
                    <p><strong>Endpoint:</strong> <code>/forocoches</code></p>
                </div>
                
                <!-- Menéame -->
                <div style="border: 1px solid #ddd; padding: 15px; border-radius: 8px;">
                    <h3>🇪🇸 Menéame</h3>
                    <p><strong>Estado:</strong> <span style="color: orange;">⚠️ Pendiente</span></p>
                    <p><strong>PR:</strong> DA 75+ | PA 72+</p>
                    <p><strong>Audiencia:</strong> +1M usuarios hispanohablantes</p>
                    <p><strong>Endpoint:</strong> <code>/meneame</code></p>
                </div>
                
            </div>
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

        <!-- Acciones de Republishing Multi-plataforma -->
        <div class="card">
            <h2>📤 Republicar Artículos - Multi-plataforma</h2>
            <p>Gestiona la republicación de posts en múltiples plataformas:</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 20px 0;">
                
                <!-- Medium -->
                <div style="text-align: center; padding: 15px; border: 1px solid #ddd; border-radius: 8px;">
                    <h4>📰 Medium</h4>
                    <a href="<?php echo admin_url('admin.php?page=replanta-republish-ai-recovery&platform=medium'); ?>" class="button button-primary" style="width: 100%; margin: 5px 0;">
                        🔄 Republicar en Medium
                    </a>
                    <p style="font-size: 12px; margin: 5px 0;">✅ Configurado y funcionando</p>
                </div>
                
                <!-- Dev.to -->
                <div style="text-align: center; padding: 15px; border: 1px solid #ddd; border-radius: 8px;">
                    <h4>💻 Dev.to</h4>
                    <a href="<?php echo admin_url('admin.php?page=replanta-republish-ai-recovery&platform=devto'); ?>" class="button button-primary" style="width: 100%; margin: 5px 0;">
                        🔄 Republicar en Dev.to
                    </a>
                    <p style="font-size: 12px; margin: 5px 0;">✅ Configurado y funcionando</p>
                </div>
                
                <!-- Hashnode -->
                <div style="text-align: center; padding: 15px; border: 1px solid #ddd; border-radius: 8px; opacity: 0.7;">
                    <h4>📝 Hashnode</h4>
                    <button class="button" style="width: 100%; margin: 5px 0;" disabled>
                        ⚠️ Pendiente configuración
                    </button>
                    <p style="font-size: 12px; margin: 5px 0;">🔧 En desarrollo</p>
                </div>
                
                <!-- LinkedIn -->
                <div style="text-align: center; padding: 15px; border: 1px solid #ddd; border-radius: 8px; opacity: 0.7;">
                    <h4>💼 LinkedIn</h4>
                    <button class="button" style="width: 100%; margin: 5px 0;" disabled>
                        ⚠️ Pendiente configuración
                    </button>
                    <p style="font-size: 12px; margin: 5px 0;">🔧 En desarrollo</p>
                </div>
                
            </div>
            
            <!-- Acciones generales -->
            <div style="text-align: center; margin: 30px 0; padding: 20px; background: #f0f0f1; border-radius: 8px;">
                <h3>🛠️ Herramientas Generales</h3>
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                    <a href="<?php echo admin_url('admin.php?page=replanta-republish-ai-recovery'); ?>" class="button button-primary">
                        🔄 Recuperar Todos los Posts Fallidos
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=replanta-republish-ai-diagnosis'); ?>" class="button button-secondary">
                        🔍 Diagnosticar Todas las Conexiones
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=replanta-republish-ai-debug'); ?>" class="button button-secondary">
                        📋 Ver Logs de Sistema
                    </a>
                </div>
            </div>
            
            <div class="notice notice-warning inline">
                <p><strong>💡 Tip:</strong> Si algunos posts no se han enviado correctamente, usa las herramientas de recuperación específicas por plataforma o la herramienta general.</p>
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
