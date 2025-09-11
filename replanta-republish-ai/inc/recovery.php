<?php
/**
 * Recovery - Página de recuperación y reintento manual de posts
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

function replanta_recovery_page() {
    // Manejar acciones
    if (isset($_GET['action']) && isset($_GET['post_id']) && wp_verify_nonce($_GET['_wpnonce'], 'recovery_action')) {
        $post_id = intval($_GET['post_id']);
        
        if ($_GET['action'] == 'retry') {
            retry_post_publication($post_id);
            echo '<div class="notice notice-success"><p>✅ Post reenviado para procesamiento.</p></div>';
        } elseif ($_GET['action'] == 'mark_sent') {
            update_post_meta($post_id, '_rr_sent_to_ai', current_time('mysql'));
            delete_post_meta($post_id, '_rr_ai_error');
            echo '<div class="notice notice-success"><p>✅ Post marcado como enviado.</p></div>';
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
        <h1>🔄 Recuperación de Posts</h1>
        
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
                                <td><?php echo esc_html($post['sent_date']); ?></td>
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

function retry_post_publication($post_id) {
    rr_ai_log("Reintento manual iniciado para post ID: $post_id", 'info');
    
    // Limpiar meta anterior
    delete_post_meta($post_id, '_rr_sent_to_ai');
    delete_post_meta($post_id, '_rr_ai_error');
    
    // Obtener el post
    $post = get_post($post_id);
    if (!$post) {
        rr_ai_log("Post ID: $post_id no encontrado para reintento", 'error');
        return false;
    }
    
    // Llamar al handler principal
    Replanta_Republish_AI::handle_new_post($post_id, $post);
    
    return true;
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
    ", $limit), ARRAY_A);
    
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
    
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID, p.post_title, pm.meta_value as sent_date
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE pm.meta_key = '_rr_sent_to_ai'
        AND p.post_status = 'publish'
        AND p.post_type = 'post'
        ORDER BY pm.meta_value DESC
        LIMIT %d
    ", $limit), ARRAY_A);
    
    return $results;
}
