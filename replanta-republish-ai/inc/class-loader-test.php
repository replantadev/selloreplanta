<?php
/**
 * Verificador de carga de clases - Debug production
 * Archivo temporal para diagnóstico en producción
 */

// Prevenir acceso directo
if (!defined('ABSPATH')) {
    exit;
}

function test_class_loading() {
    echo "<h3>🔍 Test de Carga de Clases</h3>";
    
    // Información del entorno
    echo "<p><strong>Directorio actual:</strong> " . __DIR__ . "</p>";
    echo "<p><strong>Archivo actual:</strong> " . __FILE__ . "</p>";
    echo "<p><strong>Plugin dir:</strong> " . (function_exists('plugin_dir_path') ? plugin_dir_path(__FILE__) : 'función no disponible') . "</p>";
    
    // Rutas posibles para class-handler.php
    $possible_paths = [
        __DIR__ . '/class-handler.php',
        dirname(__FILE__) . '/class-handler.php',
        dirname(dirname(__FILE__)) . '/inc/class-handler.php',
        realpath(dirname(__FILE__) . '/class-handler.php'),
        realpath(dirname(__FILE__) . '/../inc/class-handler.php'),
    ];
    
    echo "<h4>📁 Verificación de Rutas:</h4>";
    echo "<ul>";
    foreach ($possible_paths as $i => $path) {
        $exists = $path && file_exists($path);
        $readable = $exists && is_readable($path);
        echo "<li>";
        echo "<strong>Ruta " . ($i + 1) . ":</strong> <code>" . esc_html($path) . "</code><br>";
        echo "Existe: " . ($exists ? "✅" : "❌") . " | ";
        echo "Legible: " . ($readable ? "✅" : "❌");
        if ($exists) {
            echo " | Tamaño: " . filesize($path) . " bytes";
        }
        echo "</li>";
    }
    echo "</ul>";
    
    // Test de carga de clase
    echo "<h4>🔧 Test de Carga de Clase:</h4>";
    echo "<p><strong>Clase existe antes:</strong> " . (class_exists('Replanta_Republish_AI') ? "✅ SÍ" : "❌ NO") . "</p>";
    
    // Intentar cargar la clase
    $loaded = false;
    foreach ($possible_paths as $path) {
        if ($path && file_exists($path) && is_readable($path)) {
            echo "<p>Intentando cargar desde: <code>" . esc_html($path) . "</code>";
            try {
                require_once $path;
                if (class_exists('Replanta_Republish_AI')) {
                    echo " → ✅ ÉXITO</p>";
                    $loaded = true;
                    break;
                } else {
                    echo " → ❌ Archivo cargado pero clase no disponible</p>";
                }
            } catch (Exception $e) {
                echo " → ❌ Error: " . esc_html($e->getMessage()) . "</p>";
            }
        }
    }
    
    echo "<p><strong>Clase existe después:</strong> " . (class_exists('Replanta_Republish_AI') ? "✅ SÍ" : "❌ NO") . "</p>";
    
    // Si la clase existe, verificar métodos
    if (class_exists('Replanta_Republish_AI')) {
        echo "<h4>🔍 Verificación de Métodos:</h4>";
        $methods_to_check = ['send_to_platform', 'get_supported_platforms', 'send_to_ai_service'];
        echo "<ul>";
        foreach ($methods_to_check as $method) {
            $exists = method_exists('Replanta_Republish_AI', $method);
            echo "<li><strong>$method:</strong> " . ($exists ? "✅ Disponible" : "❌ No encontrado") . "</li>";
        }
        echo "</ul>";
        
        // Mostrar constantes de la clase
        if (method_exists('Replanta_Republish_AI', 'get_supported_platforms')) {
            try {
                $platforms = Replanta_Republish_AI::get_supported_platforms();
                echo "<h4>🌐 Plataformas Soportadas:</h4>";
                echo "<pre>" . print_r($platforms, true) . "</pre>";
            } catch (Exception $e) {
                echo "<p>❌ Error al obtener plataformas: " . esc_html($e->getMessage()) . "</p>";
            }
        }
    }
    
    // Información adicional del sistema
    echo "<h4>💻 Información del Sistema:</h4>";
    echo "<ul>";
    echo "<li><strong>PHP Version:</strong> " . PHP_VERSION . "</li>";
    echo "<li><strong>WordPress Version:</strong> " . (defined('WP_VERSION') ? WP_VERSION : 'No disponible') . "</li>";
    echo "<li><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</li>";
    echo "<li><strong>Tiempo ejecución:</strong> " . ini_get('max_execution_time') . "s</li>";
    echo "</ul>";
}

// Si se accede directamente como página de prueba
if (isset($_GET['test_class_loading']) && current_user_can('manage_options')) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Test de Carga de Clases - Replanta Republish AI</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            code { background: #f0f0f0; padding: 2px 4px; border-radius: 3px; }
            pre { background: #f9f9f9; padding: 10px; border: 1px solid #ddd; overflow-x: auto; }
            .success { color: #28a745; }
            .error { color: #dc3545; }
            ul { margin: 10px 0; }
            li { margin: 5px 0; }
        </style>
    </head>
    <body>
        <h1>🔧 Verificador de Carga de Clases</h1>
        <?php test_class_loading(); ?>
        <hr>
        <p><small>Generado el <?php echo date('Y-m-d H:i:s'); ?></small></p>
    </body>
    </html>
    <?php
    exit;
}
