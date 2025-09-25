<?php
/**
 * Recovery - Página de recuperación y reintento manual de posts
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

function replanta_republish_ai_recovery_page() {
    // Enhanced class loading with diagnostic logging
    if (!class_exists('Replanta_Republish_AI')) {
        $possible_paths = [
            dirname(__FILE__) . '/class-handler.php',
            dirname(dirname(__FILE__)) . '/inc/class-handler.php',
            plugin_dir_path(dirname(__FILE__)) . 'inc/class-handler.php'
        ];
        
        $loaded = false;
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                if (class_exists('Replanta_Republish_AI')) {
                    rr_ai_log("Clase cargada exitosamente desde: $path", 'info');
                    $loaded = true;
                    break;
                } else {
                    rr_ai_log("Archivo existe pero clase no se cargó desde: $path", 'warning');
                }
            } else {
                rr_ai_log("Archivo no encontrado en: $path", 'warning');
            }
        }
        
        if (!$loaded) {
            rr_ai_log("ERROR CRÍTICO: No se pudo cargar la clase Replanta_Republish_AI desde ninguna ruta", 'error');
        }
    }
    
    // Get platform filter
    $platform_filter = isset($_GET['platform']) ? sanitize_text_field($_GET['platform']) : 'all';
    $supported_platforms = class_exists('Replanta_Republish_AI') ? 
        Replanta_Republish_AI::get_supported_platforms() : 
        [];
    
    // Fallback platforms if class not available
    if (empty($supported_platforms)) {
        $supported_platforms = [
            'medium' => ['name' => 'Medium', 'icon' => '📰', 'status' => 'active'],
            'devto' => ['name' => 'Dev.to', 'icon' => '💻', 'status' => 'active'],
            'hashnode' => ['name' => 'Hashnode', 'icon' => '📝', 'status' => 'planned'],
            'linkedin' => ['name' => 'LinkedIn', 'icon' => '💼', 'status' => 'planned']
        ];
        rr_ai_log("Usando plataformas fallback debido a clase no disponible", 'warning');
    }
    
    // Manejar acciones
    if (isset($_GET['action']) && isset($_GET['post_id']) && wp_verify_nonce($_GET['_wpnonce'], 'recovery_action')) {
        $post_id = intval($_GET['post_id']);
        $specific_platform = isset($_GET['platform']) ? sanitize_text_field($_GET['platform']) : null;
        
        if ($_GET['action'] == 'retry') {
            if ($specific_platform && $specific_platform !== 'all') {
                retry_post_publication($post_id, [$specific_platform]);
                echo '<div class="notice notice-success"><p>✅ Post reenviado para procesamiento en ' . $supported_platforms[$specific_platform]['name'] . '.</p></div>';
            } else {
                retry_post_publication($post_id);
                echo '<div class="notice notice-success"><p>✅ Post reenviado para procesamiento en todas las plataformas.</p></div>';
            }
        } elseif ($_GET['action'] == 'mark_sent') {
            if ($specific_platform && $specific_platform !== 'all') {
                update_post_meta($post_id, "_rr_sent_to_{$specific_platform}", current_time('mysql'));
                echo '<div class="notice notice-success"><p>✅ Post marcado como enviado en ' . $supported_platforms[$specific_platform]['name'] . '.</p></div>';
            } else {
                update_post_meta($post_id, '_rr_sent_to_ai', current_time('mysql'));
                echo '<div class="notice notice-success"><p>✅ Post marcado como enviado.</p></div>';
            }
        } elseif ($_GET['action'] == 'clear_error') {
            delete_post_meta($post_id, '_rr_ai_error');
            echo '<div class="notice notice-success"><p>✅ Error limpiado.</p></div>';
        }
    }
    
    // Manejar reintento masivo
    if (isset($_POST['bulk_retry']) && wp_verify_nonce($_POST['recovery_nonce'], 'bulk_recovery')) {
        $post_ids = isset($_POST['post_ids']) ? array_map('intval', $_POST['post_ids']) : [];
        $processed = 0;
        
        foreach ($post_ids as $post_id) {
            retry_post_publication($post_id);
            $processed++;
        }
        
        echo '<div class="notice notice-success"><p>✅ ' . $processed . ' posts reenviados para procesamiento.</p></div>';
    }
    
    ?>
    <div class="wrap">
        <h1>🔄 Recuperación de Posts - Multi-plataforma</h1>
        
        <!-- Platform Filter -->
        <div class="platform-filter" style="margin: 20px 0; padding: 15px; background: #f0f0f1; border-radius: 5px;">
            <h3>🎯 Filtrar por Plataforma</h3>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="<?php echo admin_url('admin.php?page=replanta-republish-ai-recovery&platform=all'); ?>" 
                   class="button <?php echo $platform_filter === 'all' ? 'button-primary' : ''; ?>">
                    🌐 Todas las Plataformas
                </a>
                <?php foreach ($supported_platforms as $key => $platform): ?>
                    <a href="<?php echo admin_url('admin.php?page=replanta-republish-ai-recovery&platform=' . $key); ?>" 
                       class="button <?php echo $platform_filter === $key ? 'button-primary' : ''; ?>">
                        <?php echo $platform['icon']; ?> <?php echo $platform['name']; ?>
                        <?php if (isset($platform['status']) && $platform['status'] !== 'active'): ?>
                            <span style="font-size: 10px; opacity: 0.7;">(<?php echo ucfirst($platform['status']); ?>)</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Tabs -->
        <h2 class="nav-tab-wrapper">
            <a href="#errors" class="nav-tab nav-tab-active" onclick="showTab('errors')">⚠️ Posts con Errores</a>
            <a href="#pending" class="nav-tab" onclick="showTab('pending')">📝 Posts No Enviados</a>
            <a href="#sent" class="nav-tab" onclick="showTab('sent')">✅ Posts Enviados</a>
        </h2>

        <!-- Tab: Posts con Errores -->
        <div id="errors-tab" class="tab-content">
            <?php
            $posts_with_errors = get_posts_with_errors();
            if (!empty($posts_with_errors)):
            ?>
                <form method="post">
                    <?php wp_nonce_field('bulk_recovery', 'recovery_nonce'); ?>
                    <div class="tablenav top">
                        <input type="submit" name="bulk_retry" class="button action" value="🔄 Reintentar Seleccionados">
                        <script>
                        function selectAllErrors(source) {
                            checkboxes = document.getElementsByName('post_ids[]');
                            for(var i=0, n=checkboxes.length; i<n; i++) {
                                checkboxes[i].checked = source.checked;
                            }
                        }
                        </script>
                    </div>
                    
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <td class="check-column">
                                    <input type="checkbox" onclick="selectAllErrors(this)">
                                </td>
                                <th>Post</th>
                                <th>Error</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts_with_errors as $post): ?>
                                <tr>
                                    <th class="check-column">
                                        <input type="checkbox" name="post_ids[]" value="<?php echo $post['ID']; ?>">
                                    </th>
                                    <td>
                                        <strong>
                                            <a href="<?php echo get_edit_post_link($post['ID']); ?>" target="_blank">
                                                <?php echo esc_html($post['post_title']); ?>
                                            </a>
                                        </strong>
                                        <br>
                                        <small>ID: <?php echo $post['ID']; ?> | 
                                        <a href="<?php echo get_permalink($post['ID']); ?>" target="_blank">Ver post</a></small>
                                    </td>
                                    <td>
                                        <code style="background: #f8d7da; padding: 5px; border-radius: 3px;">
                                            <?php echo esc_html($post['error']); ?>
                                        </code>
                                    </td>
                                    <td><?php echo esc_html($post['date']); ?></td>
                                    <td>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=replanta-republish-ai-recovery&action=retry&post_id=' . $post['ID']), 'recovery_action'); ?>" 
                                           class="button button-small">🔄 Reintentar</a>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=replanta-republish-ai-recovery&action=clear_error&post_id=' . $post['ID']), 'recovery_action'); ?>" 
                                           class="button button-small">🗑️ Limpiar Error</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </form>
            <?php else: ?>
                <div class="notice notice-info">
                    <p>🎉 ¡Excelente! No hay posts con errores.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab: Posts No Enviados -->
        <div id="pending-tab" class="tab-content" style="display: none;">
            <?php
            $pending_posts = get_pending_posts();
            if (!empty($pending_posts)):
            ?>
                <p>Posts publicados que aún no han sido enviados a los microservicios:</p>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Post</th>
                            <th>Fecha Publicación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_posts as $post): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <a href="<?php echo get_edit_post_link($post->ID); ?>" target="_blank">
                                            <?php echo esc_html($post->post_title); ?>
                                        </a>
                                    </strong>
                                    <br>
                                    <small>ID: <?php echo $post->ID; ?> | 
                                    <a href="<?php echo get_permalink($post->ID); ?>" target="_blank">Ver post</a></small>
                                </td>
                                <td><?php echo $post->post_date; ?></td>
                                <td>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=replanta-republish-ai-recovery&action=retry&post_id=' . $post->ID), 'recovery_action'); ?>" 
                                       class="button button-primary button-small">📤 Enviar Ahora</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="notice notice-info">
                    <p>✅ Todos los posts han sido enviados.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab: Posts Enviados -->
        <div id="sent-tab" class="tab-content" style="display: none;">
            <?php
            $sent_posts = get_sent_posts(20);
            if (!empty($sent_posts)):
            ?>
                <p>Últimos 20 posts enviados exitosamente:</p>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Post</th>
                            <th>Plataformas Enviadas</th>
                            <th>Fecha Envío</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sent_posts as $post): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <a href="<?php echo get_edit_post_link($post['ID']); ?>" target="_blank">
                                            <?php echo esc_html($post['post_title']); ?>
                                        </a>
                                    </strong>
                                    <br>
                                    <small>ID: <?php echo $post['ID']; ?> | 
                                    <a href="<?php echo get_permalink($post['ID']); ?>" target="_blank">Ver post</a></small>
                                </td>
                                <td>
                                    <?php 
                                    $platforms = explode(', ', $post['platforms'] ?? '');
                                    $supported = Replanta_Republish_AI::get_supported_platforms();
                                    foreach ($platforms as $platform): 
                                        if (isset($supported[trim($platform)])):
                                            echo $supported[trim($platform)]['icon'] . ' ' . $supported[trim($platform)]['name'] . '<br>';
                                        endif;
                                    endforeach; 
                                    ?>
                                </td>
                                <td><?php echo esc_html($post['post_date']); ?></td>
                                <td>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=replanta-republish-ai-recovery&action=retry&post_id=' . $post['ID']), 'recovery_action'); ?>" 
                                       class="button button-small">🔄 Reenviar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="notice notice-info">
                    <p>📝 Aún no se han enviado posts.</p>
                </div>
            <?php endif; ?>
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
    </script>
    <?php
}

function retry_post_publication($post_id, $platforms = null) {
    // Enhanced class loading with diagnostic logging
    if (!class_exists('Replanta_Republish_AI')) {
        $possible_paths = [
            dirname(__FILE__) . '/class-handler.php',
            dirname(dirname(__FILE__)) . '/inc/class-handler.php',
            __DIR__ . '/class-handler.php',
            realpath(dirname(__FILE__) . '/../inc/class-handler.php'),
        ];
        
        $loaded = false;
        foreach ($possible_paths as $path) {
            if ($path && file_exists($path)) {
                require_once $path;
                if (class_exists('Replanta_Republish_AI')) {
                    rr_ai_log("Clase cargada exitosamente para retry desde: $path", 'info');
                    $loaded = true;
                    break;
                } else {
                    rr_ai_log("Archivo retry existe pero clase no se cargó desde: $path", 'warning');
                }
            } else {
                rr_ai_log("Archivo retry no encontrado en: $path", 'warning');
            }
        }
        
        if (!$loaded) {
            rr_ai_log("ERROR CRÍTICO EN RETRY: No se pudo cargar la clase Replanta_Republish_AI", 'error');
        }
    }
    
    rr_ai_log("Reintento manual iniciado para post ID: $post_id", 'info');
    
    // If specific platforms are specified, only clear those
    if ($platforms && is_array($platforms)) {
        foreach ($platforms as $platform) {
            delete_post_meta($post_id, "_rr_sent_to_{$platform}");
            delete_post_meta($post_id, "_rr_{$platform}_url");
            delete_post_meta($post_id, "_rr_{$platform}_title");
        }
        rr_ai_log("Limpiando metadata para plataformas: " . implode(', ', $platforms), 'info');
    } else {
        // Limpiar meta anterior para todas las plataformas
        delete_post_meta($post_id, '_rr_sent_to_ai');
        delete_post_meta($post_id, '_rr_ai_error');
        
        // Clear platform-specific metadata if class is available
        if (class_exists('Replanta_Republish_AI')) {
            $supported_platforms = Replanta_Republish_AI::get_supported_platforms();
            foreach ($supported_platforms as $key => $platform) {
                delete_post_meta($post_id, "_rr_sent_to_{$key}");
                delete_post_meta($post_id, "_rr_{$key}_url");
                delete_post_meta($post_id, "_rr_{$key}_title");
            }
        } else {
            // Fallback: clear known platforms
            $fallback_platforms = ['medium', 'devto', 'hashnode', 'linkedin'];
            foreach ($fallback_platforms as $key) {
                delete_post_meta($post_id, "_rr_sent_to_{$key}");
                delete_post_meta($post_id, "_rr_{$key}_url");
                delete_post_meta($post_id, "_rr_{$key}_title");
            }
        }
    }
    
    // Obtener el post
    $post = get_post($post_id);
    if (!$post) {
        rr_ai_log("Post ID: $post_id no encontrado para reintento", 'error');
        return false;
    }
    
    // Send to specific platforms or use the main handler
    if ($platforms && is_array($platforms) && class_exists('Replanta_Republish_AI')) {
        $results = [];
        foreach ($platforms as $platform_key) {
            $result = Replanta_Republish_AI::send_to_platform($post_id, $platform_key);
            $results[$platform_key] = $result;
        }
        return $results;
    } else {
        // Llamar al handler principal para todas las plataformas habilitadas
        if (class_exists('Replanta_Republish_AI')) {
            return Replanta_Republish_AI::send_to_ai_service($post_id, $platforms);
        } else {
            rr_ai_log("Clase Replanta_Republish_AI no disponible para reintento de post $post_id", 'error');
            return false;
        }
    }
}

function get_posts_with_errors($limit = 50) {
    global $wpdb;
    
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID, p.post_title, pm.meta_value as error, p.post_modified as date
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE pm.meta_key = '_rr_ai_error'
        AND p.post_status = 'publish'
        AND p.post_type = 'post'
        ORDER BY p.post_modified DESC
        LIMIT %d
    ", $limit), 'ARRAY_A');
    
    return $results;
}

function get_pending_posts($limit = 50) {
    global $wpdb;
    
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID, p.post_title, p.post_date, p.post_modified
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm1 ON (p.ID = pm1.post_id AND pm1.meta_key = '_rr_sent_to_ai')
        LEFT JOIN {$wpdb->postmeta} pm2 ON (p.ID = pm2.post_id AND pm2.meta_key = '_rr_ai_error')
        WHERE p.post_status = 'publish'
        AND p.post_type = 'post'
        AND pm1.meta_value IS NULL
        AND pm2.meta_value IS NULL
        ORDER BY p.post_date DESC
        LIMIT %d
    ", $limit));
    
    return $results;
}

function get_sent_posts($limit = 20) {
    global $wpdb;
    
    // Buscar posts que han sido enviados a cualquier plataforma
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT p.ID, p.post_title, p.post_date, 
               GROUP_CONCAT(DISTINCT REPLACE(pm.meta_key, '_rr_sent_to_', '') SEPARATOR ', ') as platforms
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE (pm.meta_key = '_rr_sent_to_ai' OR pm.meta_key LIKE '_rr_sent_to_%')
        AND pm.meta_key != '_rr_sent_to_ai_error'
        AND p.post_status = 'publish'
        AND p.post_type = 'post'
        GROUP BY p.ID
        ORDER BY p.post_date DESC
        LIMIT %d
    ", $limit), 'ARRAY_A');
    
    return $results;
}
