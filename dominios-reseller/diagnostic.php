<?php
/**
 * Script de Diagnóstico - Dominios Reseller
 * Ejecutar desde: wp-admin/admin.php?page=dominios-reseller-diagnostic
 */

// Seguridad
if (!defined('ABSPATH')) {
    die('Acceso directo no permitido');
}

// Añadir página de diagnóstico temporal
add_action('admin_menu', function() {
    add_submenu_page(
        null, // No mostrar en menú
        'Diagnóstico Dominios',
        'Diagnóstico',
        'manage_options',
        'dominios-reseller-diagnostic',
        'dominios_reseller_diagnostic_page'
    );
});

function dominios_reseller_diagnostic_page() {
    echo '<div class="wrap">';
    echo '<h1>🔍 Diagnóstico - Dominios Reseller</h1>';
    
    // 1. Versión del plugin
    echo '<h2>📌 Versión del Plugin</h2>';
    if (defined('DOMINIOS_RESELLER_VERSION')) {
        echo '<p><strong>Versión actual:</strong> ' . DOMINIOS_RESELLER_VERSION . '</p>';
    } else {
        echo '<p style="color:red;">⚠️ Constante DOMINIOS_RESELLER_VERSION no definida (versión muy antigua)</p>';
    }
    
    // 2. Estructura de la Base de Datos
    echo '<h2>💾 Estructura de Base de Datos</h2>';
    global $wpdb;
    $tabla = $wpdb->prefix . 'dominios_reseller';
    
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $tabla");
    if ($columns) {
        echo '<table class="widefat"><thead><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th></tr></thead><tbody>';
        foreach ($columns as $col) {
            $has_server = ($col->Field === 'server') ? ' style="background:#c6f6d5;"' : '';
            echo "<tr$has_server>";
            echo '<td>' . esc_html($col->Field) . '</td>';
            echo '<td>' . esc_html($col->Type) . '</td>';
            echo '<td>' . esc_html($col->Null) . '</td>';
            echo '<td>' . esc_html($col->Key) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        
        // Verificar campo server
        $has_server = false;
        foreach ($columns as $col) {
            if ($col->Field === 'server') {
                $has_server = true;
                break;
            }
        }
        
        if ($has_server) {
            echo '<p style="color:green;">✅ Campo <code>server</code> existe (versión 1.2.0+)</p>';
        } else {
            echo '<p style="color:red;">❌ Campo <code>server</code> NO existe (necesitas actualizar)</p>';
        }
    }
    
    // 3. Índices
    echo '<h2>🔑 Índices de la Tabla</h2>';
    $indexes = $wpdb->get_results("SHOW INDEX FROM $tabla");
    if ($indexes) {
        echo '<table class="widefat"><thead><tr><th>Nombre</th><th>Columna</th><th>Único</th></tr></thead><tbody>';
        foreach ($indexes as $idx) {
            echo '<tr>';
            echo '<td>' . esc_html($idx->Key_name) . '</td>';
            echo '<td>' . esc_html($idx->Column_name) . '</td>';
            echo '<td>' . ($idx->Non_unique ? 'No' : 'Sí') . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
    
    // 4. Contenido de la tabla
    echo '<h2>📊 Datos en la Tabla</h2>';
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $tabla");
    echo '<p><strong>Total de registros:</strong> ' . $total . '</p>';
    
    if ($has_server) {
        $uk_count = $wpdb->get_var("SELECT COUNT(*) FROM $tabla WHERE server = 'uk'");
        $usa_count = $wpdb->get_var("SELECT COUNT(*) FROM $tabla WHERE server = 'usa'");
        echo '<p>UK: ' . $uk_count . ' | USA: ' . $usa_count . '</p>';
    }
    
    // 5. Archivos del plugin
    echo '<h2>📁 Archivos del Plugin</h2>';
    $plugin_dir = plugin_dir_path(__FILE__);
    $files = [
        'dominios-reseller.php' => 'Archivo principal',
        'includes/shortcodes.php' => 'Shortcodes',
        'includes/whm-functions.php' => 'Funciones WHM',
        'includes/ajax-handlers.php' => 'Handlers AJAX',
        'includes/scripts.php' => 'Scripts'
    ];
    
    echo '<table class="widefat"><thead><tr><th>Archivo</th><th>Estado</th><th>Última modificación</th></tr></thead><tbody>';
    foreach ($files as $file => $desc) {
        $path = $plugin_dir . $file;
        if (file_exists($path)) {
            $mtime = filemtime($path);
            $date = date('Y-m-d H:i:s', $mtime);
            echo '<tr>';
            echo '<td><code>' . esc_html($file) . '</code><br><small>' . esc_html($desc) . '</small></td>';
            echo '<td style="color:green;">✅ Existe</td>';
            echo '<td>' . $date . '</td>';
            echo '</tr>';
        } else {
            echo '<tr>';
            echo '<td><code>' . esc_html($file) . '</code></td>';
            echo '<td style="color:red;">❌ No existe</td>';
            echo '<td>-</td>';
            echo '</tr>';
        }
    }
    echo '</tbody></table>';
    
    // 6. Funciones críticas
    echo '<h2>⚙️ Funciones Críticas</h2>';
    $functions = [
        'dominios_reseller_sync_from_whm' => 'Sincronización WHM (v1.2.0+)',
        'obtener_cuentas_whm' => 'Obtener cuentas WHM',
        'obtener_datos_dominio_actual' => 'Datos de dominio para shortcode'
    ];
    
    echo '<ul>';
    foreach ($functions as $func => $desc) {
        if (function_exists($func)) {
            echo '<li style="color:green;">✅ <code>' . esc_html($func) . '</code> - ' . esc_html($desc) . '</li>';
        } else {
            echo '<li style="color:red;">❌ <code>' . esc_html($func) . '</code> - ' . esc_html($desc) . ' <strong>(FALTA)</strong></li>';
        }
    }
    echo '</ul>';
    
    // 7. Recomendaciones
    echo '<h2>💡 Recomendaciones</h2>';
    
    if (!$has_server) {
        echo '<div class="notice notice-error"><p><strong>⚠️ ACCIÓN REQUERIDA:</strong></p>';
        echo '<ol>';
        echo '<li>Sube los nuevos archivos del plugin vía FTP</li>';
        echo '<li>Ve a Plugins → Desactivar "Dominios Reseller"</li>';
        echo '<li>Ve a Plugins → Activar "Dominios Reseller"</li>';
        echo '<li>Vuelve a esta página para verificar</li>';
        echo '</ol></div>';
    } else {
        echo '<div class="notice notice-success"><p>✅ La base de datos está actualizada. El plugin debería funcionar correctamente.</p></div>';
    }
    
    echo '</div>';
}
