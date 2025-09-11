<?php
/**
 * Diagnóstico - Página de testing y verificación de microservicios
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

function replanta_diagnosis_page() {
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

        <!-- Test de Conectividad -->
        <div class="card">
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
                            <?php if (!empty($result['error'])): ?>
                                <p><strong>Error:</strong> <code><?php echo esc_html($result['error']); ?></code></p>
                            <?php endif; ?>
                            <?php if (!empty($result['response'])): ?>
                                <p><strong>Respuesta:</strong> <code><?php echo esc_html(substr($result['response'], 0, 200)); ?></code></p>
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
    </div>
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
    
    foreach ($urls as $url) {
        $start_time = microtime(true);
        
        $test_payload = [
            'title' => 'Test de conectividad',
            'url' => 'https://replanta.net/test',
            'content' => 'Este es un test de conectividad del sistema',
            'test' => true
        ];
        
        $response = wp_remote_post($url, [
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => 'Replanta Republish AI v' . RREPLANTA_AI_VERSION . ' (Diagnosis)'
            ],
            'body' => json_encode($test_payload),
            'timeout' => 30,
            'sslverify' => false
        ]);
        
        $response_time = round((microtime(true) - $start_time) * 1000);
        $http_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if (is_wp_error($response)) {
            $results[] = [
                'url' => $url,
                'success' => false,
                'error' => $response->get_error_message(),
                'response_time' => $response_time,
                'http_code' => 0,
                'response' => ''
            ];
        } else {
            $results[] = [
                'url' => $url,
                'success' => $http_code == 200,
                'error' => $http_code != 200 ? "HTTP $http_code" : '',
                'response_time' => $response_time,
                'http_code' => $http_code,
                'response' => $response_body
            ];
        }
        
        // Log del test
        rr_ai_log("Test diagnóstico para $url - HTTP: $http_code - Tiempo: {$response_time}ms", 'info');
    }
    
    return $results;
}

function get_recent_ai_errors($limit = 5) {
    global $wpdb;
    
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID as post_id, p.post_title, pm.meta_value as error, p.post_modified as date
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE pm.meta_key = '_rr_ai_error'
        AND p.post_status = 'publish'
        ORDER BY p.post_modified DESC
        LIMIT %d
    ", $limit), ARRAY_A);
    
    foreach ($results as &$result) {
        $result['error'] = $result['error'];
        $result['date'] = date('Y-m-d H:i:s', strtotime($result['date']));
    }
    
    return $results;
}
