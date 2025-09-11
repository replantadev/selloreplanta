<?php
/**
 * Diagnóstico - Página de testing y verificación de microservicios
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

function replanta_republish_ai_diagnosis_page() {
    $options = get_option('replanta_republish_ai_options', []);
    $microservice_url = isset($options['microservice_url']) ? $options['microservice_url'] : '';
    $fallback_urls = isset($options['fallback_urls']) ? $options['fallback_urls'] : '';
    
    // Procesar test si se solicita
    $test_results = [];
    if (isset($_POST['run_test']) && wp_verify_nonce($_POST['diagnosis_nonce'], 'diagnosis_test')) {
        $test_results = run_microservice_diagnostics();
    }
    
    ?>
    <div class="wrap">
        <h1>🔍 Diagnóstico del Sistema</h1>
        
        <!-- Configuración Actual -->
        <div class="card" style="margin-bottom: 20px;">
            <h2>⚙️ Configuración Actual</h2>
            <table class="form-table">
                <tr>
                    <th>URL Principal:</th>
                    <td><code><?php echo esc_html($microservice_url ?: 'No configurada'); ?></code></td>
                </tr>
                <tr>
                    <th>URLs de Respaldo:</th>
                    <td><code><?php echo esc_html($fallback_urls ?: 'No configuradas'); ?></code></td>
                </tr>
                <tr>
                    <th>Versión Plugin:</th>
                    <td><strong><?php echo RREPLANTA_AI_VERSION; ?></strong></td>
                </tr>
                <tr>
                    <th>Auto-publicación:</th>
                    <td><?php echo isset($options['auto_publish']) && $options['auto_publish'] ? '✅ Habilitada' : '❌ Deshabilitada'; ?></td>
                </tr>
            </table>
        </div>

        <!-- Test de Servidor -->
        <div class="card">
            <h2>🌐 Test de Conectividad del Servidor</h2>
            <p>Prueba la conectividad básica con los servidores:</p>
            
            <div style="display: flex; gap: 10px; margin: 15px 0;">
                <button type="button" class="button" onclick="testServerConnectivity('https://replanta.dev')">Test replanta.dev</button>
                <button type="button" class="button" onclick="testServerConnectivity('https://replanta.net')">Test replanta.net</button>
                <button type="button" class="button" onclick="testServerConnectivity('https://replanta.dev/medium-rr/')">Test /medium-rr/</button>
            </div>
            
            <div id="server-test-results" style="margin-top: 15px; padding: 10px; background: #f1f1f1; border-radius: 5px; display: none;">
                <h4>Resultados del Test:</h4>
                <pre id="server-test-output"></pre>
            </div>
        </div>
            <h2>🌐 Test de Conectividad</h2>
            <form method="post">
                <?php wp_nonce_field('diagnosis_test', 'diagnosis_nonce'); ?>
                <p>Haz clic para probar la conectividad con los microservicios configurados:</p>
                <input type="submit" name="run_test" class="button button-primary" value="▶️ Ejecutar Diagnóstico">
            </form>
            
            <?php if (!empty($test_results)): ?>
                <div style="margin-top: 20px;">
                    <h3>📊 Resultados del Test</h3>
                    <?php foreach ($test_results as $result): ?>
                        <div style="padding: 10px; margin: 10px 0; border-left: 4px solid <?php echo $result['success'] ? '#46b450' : '#dc3232'; ?>; background: <?php echo $result['success'] ? '#d4edda' : '#f8d7da'; ?>;">
                            <h4><?php echo esc_html($result['url']); ?></h4>
                            <p><strong>Estado:</strong> <?php echo $result['success'] ? '✅ Éxito' : '❌ Error'; ?></p>
                            <p><strong>Tiempo:</strong> <?php echo $result['response_time']; ?>ms</p>
                            <p><strong>Código HTTP:</strong> <?php echo $result['http_code']; ?></p>
                            <p><strong>Método:</strong> <?php echo $result['method']; ?></p>
                            <?php if (!empty($result['recommendation'])): ?>
                                <p><strong>💡 Recomendación:</strong> <?php echo esc_html($result['recommendation']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($result['error'])): ?>
                                <p><strong>Error:</strong> <code><?php echo esc_html($result['error']); ?></code></p>
                            <?php endif; ?>
                            <?php if (!empty($result['response'])): ?>
                                <p><strong>Respuesta:</strong> <code><?php echo esc_html($result['response']); ?></code></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Errores Recientes -->
        <?php
        $recent_errors = get_recent_ai_errors(10);
        if (!empty($recent_errors)):
        ?>
        <div class="card" style="margin-top: 20px;">
            <h2>⚠️ Errores Recientes</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Post ID</th>
                        <th>Error</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_errors as $error): ?>
                        <tr>
                            <td><?php echo esc_html($error['date']); ?></td>
                            <td>
                                <a href="<?php echo get_edit_post_link($error['post_id']); ?>" target="_blank">
                                    #<?php echo $error['post_id']; ?>
                                </a>
                            </td>
                            <td><?php echo esc_html($error['error']); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=replanta-republish-ai-recovery&retry=' . $error['post_id']); ?>" class="button button-small">
                                    🔄 Reintentar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Información de Troubleshooting -->
        <div class="card" style="margin-top: 20px; background: #fff3cd; border: 1px solid #ffeaa7;">
            <h2>🔧 Guía de Solución de Problemas</h2>
            
            <h3>❌ Si ves HTTP 404:</h3>
            <ol>
                <li><strong>Verificar microservicio Python:</strong> El microservicio debe estar ejecutándose en <code>/home/replanta/public_html/medium-rr/</code></li>
                <li><strong>Comprobar app.py:</strong> El archivo principal debe estar en <code>/home/replanta/public_html/medium-rr/app.py</code></li>
                <li><strong>Verificar passenger_wsgi.py:</strong> Archivo de configuración para el servidor web</li>
                <li><strong>Probar URL directa:</strong> Acceder a <code>https://replanta.dev/medium-rr/</code> en el navegador</li>
            </ol>
            
            <h3>❌ Si ves HTTP 403:</h3>
            <ol>
                <li><strong>Permisos de archivo:</strong> Cambiar a 755 con <code>chmod 755 replanta-medium</code></li>
                <li><strong>Permisos de directorio:</strong> El directorio <code>medium-rr/</code> debe tener permisos 755</li>
                <li><strong>Configuración de .htaccess:</strong> Verificar que no bloquee el acceso al archivo</li>
            </ol>
            
            <h3>❌ Si ves HTTP 500:</h3>
            <ol>
                <li><strong>Verificar logs del servidor:</strong> Buscar errores en <code>/home/replanta/logs/</code></li>
                <li><strong>Errores de sintaxis:</strong> Verificar que el archivo PHP no tenga errores de sintaxis</li>
                <li><strong>Dependencias faltantes:</strong> Asegurarse de que todas las librerías requeridas estén instaladas</li>
            </ol>
            
            <h3>🛠️ Comandos útiles para diagnosticar el microservicio Python:</h3>
            <pre style="background: #f8f9fa; padding: 10px; border-radius: 5px;">
# Verificar que los archivos del microservicio existen
ls -la /home/replanta/public_html/medium-rr/

# Verificar el archivo principal Python
cat /home/replanta/public_html/medium-rr/app.py

# Verificar configuración del servidor web
cat /home/replanta/public_html/medium-rr/passenger_wsgi.py

# Verificar logs de errores del microservicio
tail -f /home/replanta/public_html/medium-rr/stderr.log

# Verificar logs de debug
tail -f /home/replanta/public_html/medium-rr/debug.log

# Verificar dependencias Python
cat /home/replanta/public_html/medium-rr/requirements.txt</pre>
        </div>
    </div>

    <script>
    function showTab(tabName) {
        // Ocultar todas las tabs
        var tabs = document.getElementsByClassName('tab-content');
        for (var i = 0; i < tabs.length; i++) {
            tabs[i].style.display = 'none';
        }
        
        // Remover clase activa de todas las nav-tabs
        var navTabs = document.getElementsByClassName('nav-tab');
        for (var i = 0; i < navTabs.length; i++) {
            navTabs[i].classList.remove('nav-tab-active');
        }
        
        // Mostrar tab seleccionada
        document.getElementById(tabName + '-tab').style.display = 'block';
        
        // Agregar clase activa al nav-tab
        event.target.classList.add('nav-tab-active');
    }
    
    function testServerConnectivity(url) {
        var resultsDiv = document.getElementById('server-test-results');
        var outputDiv = document.getElementById('server-test-output');
        
        resultsDiv.style.display = 'block';
        outputDiv.textContent = 'Probando ' + url + '...';
        
        // Crear una petición AJAX para probar la conectividad
        fetch(url, {
            method: 'HEAD',
            mode: 'no-cors'
        })
        .then(function(response) {
            outputDiv.textContent = '✅ ' + url + ' - Servidor responde (Status: ' + (response.status || 'Unknown') + ')';
        })
        .catch(function(error) {
            outputDiv.textContent = '❌ ' + url + ' - Error de conexión: ' + error.message;
        });
    }
    </script>

    <?php
}

function run_microservice_diagnostics() {
    $urls = Replanta_Republish_AI::get_microservice_urls();
    $results = [];
    
    if (empty($urls)) {
        return [
            [
                'url' => 'N/A',
                'success' => false,
                'error' => 'No hay URLs configuradas para probar',
                'response_time' => 0,
                'http_code' => 0
            ]
        ];
    }
    
    // URLs adicionales para diagnóstico completo
    $diagnostic_urls = [
        // URLs principales del microservicio Python Flask
        'https://replanta.dev/medium-rr/',
        'https://replanta.net/medium-rr/',
        
        // Verificar endpoints específicos
        'https://replanta.dev/medium-rr/health',
        'https://replanta.net/medium-rr/health',
        
        // Verificar dominios base
        'https://replanta.dev/',
        'https://replanta.net/',
        
        // Verificar archivos estáticos
        'https://replanta.dev/medium-rr/app.py',
        'https://replanta.net/medium-rr/app.py',
    ];
    
    $test_payload = [
        'title' => 'Test de conectividad - ' . date('Y-m-d H:i:s'),
        'url' => 'https://replanta.net/test-post',
        'content' => 'Este es un test automatizado del sistema de diagnóstico',
        'test' => true,
        'timestamp' => time()
    ];
    
    foreach ($diagnostic_urls as $url) {
        $start_time = microtime(true);
        
        // Probar con POST (método esperado por el microservicio)
        $response = wp_remote_post($url, [
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => 'Replanta Republish AI v' . RREPLANTA_AI_VERSION . ' (Advanced Diagnosis)'
            ],
            'body' => json_encode($test_payload),
            'timeout' => 10,
            'sslverify' => false,
            'redirection' => 5
        ]);
        
        $response_time = round((microtime(true) - $start_time) * 1000);
        $http_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $headers = wp_remote_retrieve_headers($response);
        
        if (is_wp_error($response)) {
            $results[] = [
                'url' => $url,
                'method' => 'POST',
                'success' => false,
                'error' => 'Error de conexión: ' . $response->get_error_message(),
                'response_time' => $response_time,
                'http_code' => 0,
                'response' => '',
                'recommendation' => 'Verificar conectividad de red y DNS'
            ];
        } else {
            // Verificar si es una respuesta válida del microservicio
            $is_valid_microservice = false;
            $recommendation = '';
            
            if ($http_code == 200) {
                $data = json_decode($response_body, true);
                if ($data && isset($data['success'])) {
                    $is_valid_microservice = true;
                    $recommendation = '✅ Microservicio funcionando correctamente';
                } elseif (strpos($response_body, 'replanta') !== false) {
                    $is_valid_microservice = true;
                    $recommendation = '⚠️ Respuesta del microservicio pero formato inesperado';
                } else {
                    $recommendation = '❌ Respuesta 200 pero no del microservicio esperado';
                }
            } elseif ($http_code == 404) {
                if (strpos($url, '/replanta-medium') !== false) {
                    $recommendation = '❌ Archivo replanta-medium no encontrado. Verificar deployment';
                } elseif (strpos($url, '/medium-rr/') !== false) {
                    $recommendation = '❌ Directorio /medium-rr/ no encontrado. Verificar estructura de archivos';
                } else {
                    $recommendation = '❌ Endpoint no encontrado';
                }
            } elseif ($http_code == 403) {
                $recommendation = '❌ Acceso denegado. Verificar permisos del archivo';
            } elseif ($http_code == 500) {
                $recommendation = '❌ Error interno del servidor. Verificar logs del servidor';
            } elseif ($http_code >= 300 && $http_code < 400) {
                $recommendation = '⚠️ Redirección detectada. Verificar configuración de redirecciones';
                } else {
                    $recommendation = "Código HTTP $http_code - Verificar configuración del microservicio Python";
                }
                
                $results[] = [
                    'url' => $url,
                    'method' => 'POST',
                    'success' => $is_valid_microservice,
                    'error' => $http_code != 200 ? "HTTP $http_code" : (!$is_valid_microservice ? 'Respuesta inválida' : ''),
                    'response_time' => $response_time,
                    'http_code' => $http_code,
                    'response' => substr($response_body, 0, 150),
                    'recommendation' => $recommendation
                ];
            }
            
            // Log del test
            rr_ai_log("Diagnóstico avanzado para $url - HTTP: $http_code - Tiempo: {$response_time}ms", 'info');
        }
        
        return $results;
    }function get_recent_ai_errors($limit = 5) {
    global $wpdb;
    
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID as post_id, p.post_title, pm.meta_value as error, p.post_modified as date
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE pm.meta_key = '_rr_ai_error'
        AND p.post_status = 'publish'
        ORDER BY p.post_modified DESC
        LIMIT %d
    ", $limit), 'ARRAY_A');
    
    foreach ($results as &$result) {
        $result['error'] = $result['error'];
        $result['date'] = date('Y-m-d H:i:s', strtotime($result['date']));
    }
    
    return $results;
}
