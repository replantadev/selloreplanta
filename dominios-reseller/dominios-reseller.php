<?php
/*
Plugin Name: Dominios Reseller
Description: Certifica dominios ecológicos desde WHM, muestra árboles plantados y CO2 evitado.
Version: 1.1.3
Author: Replanta
*/

define('DOMINIOS_RESELLER_VERSION', '1.1.3');

// Activar plugin: crear tabla si no existe
register_activation_hook(__FILE__, 'dominios_reseller_create_table');
function dominios_reseller_create_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'dominios_reseller';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        domain varchar(255) NOT NULL UNIQUE,
        trees_planted int(11) DEFAULT 0,
        co2_evaded float DEFAULT 0,
        fecha_emision DATE DEFAULT NULL,
        validez DATE DEFAULT NULL,
        status varchar(20) DEFAULT 'Activo',
        primary_domain varchar(255) DEFAULT NULL,
        startdate bigint(20) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Incluir archivos del plugin
foreach ([
    'includes/whm-functions.php',
    'includes/emisiones-functions.php',
    'includes/ajax-handlers.php',
    'includes/shortcodes.php',
    'includes/scripts.php'
] as $file) {
    $path = plugin_dir_path(__FILE__) . $file;
    if (file_exists($path)) require_once $path;
}

// Registrar página del plugin en el admin
add_action('admin_menu', function () {
    add_menu_page(
        'Dominios Reseller',
        'Dominios Reseller',
        'manage_options',
        'dominios-reseller',
        'dominios_reseller_admin_page',
        'dashicons-cloud',
        56
    );
});

// Cargar assets inline como fallback
function dominios_reseller_inline_assets() {
    ?>
    <style>
    /* Dominios Reseller Inline CSS v1.1.3 */
    .dominios-reseller-admin {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
        max-width: none;
        background: #f1f1f1;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin: 20px 0;
    }
    .header-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        text-align: center;
    }
    .header-section h1 {
        margin: 0 0 10px 0;
        font-size: 2.2em;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
    }
    .header-section .description {
        margin: 0;
        font-size: 1.1em;
        opacity: 0.9;
    }
    .server-tabs {
        background: white;
        min-height: 600px;
    }
    .tab-buttons {
        display: flex;
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        margin: 0;
        padding: 0;
    }
    .tab-button {
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        padding: 15px 25px;
        transition: all 0.3s ease;
        white-space: nowrap;
    }
    .tab-button:hover {
        background: rgba(0,123,255,0.1);
        color: #0056b3;
    }
    .tab-button.active {
        background: rgba(0,123,255,0.05);
        border-bottom-color: #007bff;
        color: #0056b3;
    }
    .tab-pane {
        display: none;
        min-height: 500px;
        padding: 30px;
    }
    .tab-pane.active {
        display: block;
    }
    .unified-domains-table,
    .domains-table {
        background: white;
        border-collapse: collapse;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
        width: 100%;
    }
    .unified-domains-table thead,
    .domains-table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .unified-domains-table th,
    .domains-table th {
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.5px;
        padding: 15px 12px;
        text-align: left;
        text-transform: uppercase;
    }
    .unified-domains-table td,
    .domains-table td {
        border-bottom: 1px solid #f1f3f4;
        padding: 12px;
        vertical-align: middle;
    }
    .suspended-row {
        background-color: #fff5f5 !important;
        border-left: 4px solid #ef4444 !important;
    }
    .server-badge {
        border-radius: 12px;
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 4px 10px;
        text-transform: uppercase;
    }
    .server-uk {
        background: #3b82f6;
        color: white;
    }
    .server-usa {
        background: #ef4444;
        color: white;
    }
    .status-badge {
        border-radius: 12px;
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 8px;
        text-transform: uppercase;
    }
    .status-active {
        background: #dcfce7;
        color: #166534;
    }
    .status-suspended {
        background: #fee2e2;
        color: #991b1b;
    }
    .filter-controls {
        align-items: center;
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }
    .filter-select {
        background: white;
        border: 2px solid #e1e5e9;
        border-radius: 6px;
        font-size: 14px;
        min-width: 150px;
        padding: 8px 12px;
    }
    .save-all-unified, .refresh-unified {
        border: none;
        border-radius: 6px;
        color: white;
        cursor: pointer;
        font-weight: 600;
        margin-right: 10px;
        padding: 12px 24px;
    }
    .save-all-unified {
        background: #3b82f6;
    }
    .refresh-unified {
        background: #6b7280;
    }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        console.log('Dominios Reseller Inline JS v1.1.3 loaded');
        
        // Tabs functionality
        $('.tab-button').on('click', function() {
            var targetTab = $(this).data('tab');
            $('.tab-button').removeClass('active');
            $('.tab-pane').removeClass('active');
            $(this).addClass('active');
            $('#' + targetTab + '-tab').addClass('active');
        });
        
        // Filter functionality
        $('#server-filter, #status-filter').on('change', function() {
            var serverFilter = $('#server-filter').val();
            var statusFilter = $('#status-filter').val();
            
            $('#unified-domains-table tbody tr').each(function() {
                var $row = $(this);
                var server = $row.data('server');
                var status = $row.data('status');
                var showRow = true;
                
                if (serverFilter && server !== serverFilter.toLowerCase()) {
                    showRow = false;
                }
                if (statusFilter && status !== statusFilter) {
                    showRow = false;
                }
                
                if (showRow) {
                    $row.show();
                } else {
                    $row.hide();
                }
            });
        });
        
        // Refresh functionality
        $('.refresh-unified').on('click', function() {
            location.reload();
        });
    });
    </script>
    <?php
}

function dominios_reseller_admin_page() {
    // Inyectar CSS y JS inline como fallback si los archivos no existen
    dominios_reseller_inline_assets();

    echo '<div class="wrap dominios-reseller-admin">';
    echo '<div class="header-section">';
    echo '<h1><span class="dashicons dashicons-cloud"></span> Dominios Reseller</h1>';
    echo '<p class="description">Gestión de dominios certificados ecológicos en múltiples servidores WHM</p>';
    echo '</div>';

    // Manejar test de conexión
    if (isset($_POST['test_whm_connection'])) {
        $server = sanitize_text_field($_POST['server'] ?? 'uk');
        $options = get_option('dominios_reseller_options');
        $token_key = $server . '_whm_token';
        $token = $options[$token_key] ?? '';

        if (empty($token)) {
            echo '<div class="notice notice-error"><p>❌ Error: Debes configurar primero el API Token de WHM para el servidor ' . strtoupper($server) . '.</p></div>';
        } else {
            echo '<div class="notice notice-info"><p>🔄 Probando conexión con WHM (' . strtoupper($server) . ')...</p></div>';
            $test_result = test_whm_connection($token, $server);

            if ($test_result['success']) {
                echo '<div class="notice notice-success"><p>✅ Conexión exitosa! Se encontraron ' . $test_result['count'] . ' cuentas en WHM (' . strtoupper($server) . ').</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>❌ Error de conexión: ' . esc_html($test_result['error']) . '</p></div>';
            }
        }
    }

    // Interfaz con pestañas
    echo '<div class="server-tabs">';
    echo '<div class="tab-buttons">';
    echo '<button class="tab-button active" data-tab="all">📊 Todos los Dominios</button>';
    echo '<button class="tab-button" data-tab="uk">🇬🇧 Servidor UK</button>';
    echo '<button class="tab-button" data-tab="usa">🇺🇸 Servidor USA</button>';
    echo '<button class="tab-button" data-tab="settings">⚙️ Configuración</button>';
    echo '</div>';

    // Contenido de pestañas
    echo '<div class="tab-content">';

    // Pestaña Todos los Dominios
    echo '<div id="all-tab" class="tab-pane active">';
    mostrar_todos_los_dominios_unificados();
    echo '</div>';

    // Pestaña UK
    echo '<div id="uk-tab" class="tab-pane">';
    mostrar_servidor_dominios('uk', 'UK (Europa)', '77.95.113.38');
    echo '</div>';

    // Pestaña USA
    echo '<div id="usa-tab" class="tab-pane">';
    mostrar_servidor_dominios('usa', 'USA', '190.92.170.164');
    echo '</div>';

    // Pestaña Configuración
    echo '<div id="settings-tab" class="tab-pane">';
    echo '<form method="post" action="options.php">';
    settings_fields('dominios_reseller_options_group');
    do_settings_sections('dominios-reseller');
    submit_button('Guardar configuración');
    echo '</form>';
    echo '</div>';

    echo '</div>'; // Fin tab-content
    echo '</div>'; // Fin server-tabs
    echo '</div>'; // Fin wrap
}

// Función para mostrar tabla unificada de todos los dominios
function mostrar_todos_los_dominios_unificados() {
    $options = get_option('dominios_reseller_options');
    $uk_token = $options['uk_whm_token'] ?? '';
    $usa_token = $options['usa_whm_token'] ?? '';

    $all_domains = [];

    // Obtener dominios del servidor UK
    if (!empty($uk_token)) {
        $uk_accounts = obtener_cuentas_whm($uk_token, 'uk');
        if ($uk_accounts && !empty($uk_accounts['data']['acct'])) {
            foreach ($uk_accounts['data']['acct'] as $cuenta) {
                $domain_data = [
                    'domain' => $cuenta['domain'],
                    'server' => 'UK',
                    'server_ip' => '77.95.113.38',
                    'status' => $cuenta['suspended'] ? 'Suspendido' : 'Activo',
                    'startdate' => $cuenta['unix_startdate'],
                    'user' => $cuenta['user']
                ];
                $all_domains[] = $domain_data;

                // Añadir addon domains
                $addons = obtener_addons_de_usuario($cuenta['user'], $uk_token, 'uk');
                if (is_array($addons)) {
                    foreach ($addons as $addon) {
                        if (is_array($addon) && isset($addon['domain'])) {
                            $all_domains[] = [
                                'domain' => $addon['domain'],
                                'server' => 'UK',
                                'server_ip' => '77.95.113.38',
                                'status' => 'Addon',
                                'startdate' => $cuenta['unix_startdate'],
                                'user' => $cuenta['user'],
                                'parent_domain' => $cuenta['domain']
                            ];
                        }
                    }
                }
            }
        }
    }

    // Obtener dominios del servidor USA
    if (!empty($usa_token)) {
        $usa_accounts = obtener_cuentas_whm($usa_token, 'usa');
        if ($usa_accounts && !empty($usa_accounts['data']['acct'])) {
            foreach ($usa_accounts['data']['acct'] as $cuenta) {
                $domain_data = [
                    'domain' => $cuenta['domain'],
                    'server' => 'USA',
                    'server_ip' => '190.92.170.164',
                    'status' => $cuenta['suspended'] ? 'Suspendido' : 'Activo',
                    'startdate' => $cuenta['unix_startdate'],
                    'user' => $cuenta['user']
                ];
                $all_domains[] = $domain_data;

                // Añadir addon domains
                $addons = obtener_addons_de_usuario($cuenta['user'], $usa_token, 'usa');
                if (is_array($addons)) {
                    foreach ($addons as $addon) {
                        if (is_array($addon) && isset($addon['domain'])) {
                            $all_domains[] = [
                                'domain' => $addon['domain'],
                                'server' => 'USA',
                                'server_ip' => '190.92.170.164',
                                'status' => 'Addon',
                                'startdate' => $cuenta['unix_startdate'],
                                'user' => $cuenta['user'],
                                'parent_domain' => $cuenta['domain']
                            ];
                        }
                    }
                }
            }
        }
    }

    if (empty($all_domains)) {
        echo '<div class="notice notice-warning"><p>⚠️ No se encontraron dominios en ningún servidor. Verifica que los tokens WHM estén configurados correctamente.</p></div>';
        return;
    }

    global $wpdb;
    $tabla = $wpdb->prefix . 'dominios_reseller';

    echo '<div class="domains-unified-container">';
    echo '<div class="unified-header">';
    echo '<h3>📊 Todos los Dominios (' . count($all_domains) . ' total)</h3>';
    echo '<div class="filter-controls">';
    echo '<select id="server-filter" class="filter-select">';
    echo '<option value="">Todos los servidores</option>';
    echo '<option value="UK">🇬🇧 UK (Europa)</option>';
    echo '<option value="USA">🇺🇸 USA (América)</option>';
    echo '</select>';
    echo '<select id="status-filter" class="filter-select">';
    echo '<option value="">Todos los estados</option>';
    echo '<option value="Activo">✅ Activos</option>';
    echo '<option value="Suspendido">❌ Suspendidos</option>';
    echo '<option value="Addon">🔗 Addon Domains</option>';
    echo '</select>';
    echo '</div>';
    echo '</div>';

    echo '<table class="widefat fixed striped unified-domains-table" id="unified-domains-table">';
    echo '<thead><tr>';
    echo '<th class="domain-col">Dominio</th>';
    echo '<th class="server-col">Servidor</th>';
    echo '<th class="status-col">Estado</th>';
    echo '<th class="startdate-col">Inicio WHM</th>';
    echo '<th class="registered-col">Alta en Replanta</th>';
    echo '<th class="trees-col">Árboles</th>';
    echo '<th class="co2-col">CO2 Evitado (g)</th>';
    echo '<th class="actions-col">Acciones</th>';
    echo '</tr></thead><tbody>';

    foreach ($all_domains as $domain_data) {
        $dominio = esc_html($domain_data['domain']);
        $server = $domain_data['server'];
        $server_lower = strtolower($server);
        $status = $domain_data['status'];
        $startdate = $domain_data['startdate'];

        // Obtener datos de la base de datos
        $existente = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabla WHERE domain = %s", $dominio));

        $fecha_emision_calculada = date('Y-m-d', $startdate);
        $validez_calculada = date('Y-m-d', strtotime("$fecha_emision_calculada +1 year"));

        if ($existente) {
            if ($existente->fecha_emision !== $fecha_emision_calculada || $existente->validez !== $validez_calculada) {
                $wpdb->update($tabla, [
                    'fecha_emision' => $fecha_emision_calculada,
                    'validez' => $validez_calculada,
                ], ['domain' => $dominio]);
                $existente->fecha_emision = $fecha_emision_calculada;
                $existente->validez = $validez_calculada;
            }
        }

        $trees = $existente->trees_planted ?? 0;
        $co2 = $existente->co2_evaded ?? 0;

        // Clases CSS para diferentes estados
        $status_class = '';
        $row_class = 'unified-domain-row';
        
        switch($status) {
            case 'Activo':
                $status_class = 'status-active';
                break;
            case 'Suspendido':
                $status_class = 'status-suspended';
                $row_class .= ' suspended-row';
                break;
            case 'Addon':
                $status_class = 'status-addon';
                $row_class .= ' addon-row';
                break;
        }

        echo '<tr class="' . $row_class . '" data-domain="' . esc_attr($dominio) . '" data-server="' . esc_attr($server_lower) . '" data-status="' . esc_attr($status) . '">';
        
        // Columna dominio
        if ($status === 'Addon' && isset($domain_data['parent_domain'])) {
            echo "<td class='domain-cell addon-domain'>└─ $dominio <small>(addon de {$domain_data['parent_domain']})</small></td>";
        } else {
            echo "<td class='domain-cell'><strong>$dominio</strong></td>";
        }
        
        echo "<td class='server-cell'><span class='server-badge server-{$server_lower}'>$server</span></td>";
        echo "<td class='status-cell'><span class='status-badge $status_class'>$status</span></td>";
        echo '<td class="startdate-cell">' . date('Y-m-d', $startdate) . '</td>';
        echo '<td class="registered-cell">' . esc_html($existente->fecha_emision ?? '(No reg.)') . '</td>';
        echo "<td class='trees-cell'><input type='number' class='trees-input' data-domain='$dominio' data-server='$server_lower' value='$trees' min='0' /></td>";
        echo "<td class='co2-cell'><input type='number' class='co2-input' data-domain='$dominio' data-server='$server_lower' value='$co2' step='0.01' /></td>";
        echo "<td class='actions-cell'><button class='button button-small calculate-emissions' data-domain='$dominio' data-server='$server_lower'>Calcular</button></td>";
        echo '</tr>';

        // Insertar en base de datos si no existe
        if (!$existente) {
            $wpdb->insert($tabla, [
                'domain' => $dominio,
                'startdate' => $startdate,
                'fecha_emision' => $fecha_emision_calculada,
                'validez' => $validez_calculada,
                'status' => $status,
                'trees_planted' => 0,
                'co2_evaded' => 0,
                'primary_domain' => isset($domain_data['parent_domain']) ? $domain_data['parent_domain'] : null,
                'is_primary' => $status !== 'Addon' ? 1 : 0
            ]);
        }
    }

    echo '</tbody></table>';
    echo '<div class="unified-actions">';
    echo '<button class="button button-primary save-all-unified" data-table="unified">💾 Guardar todos los cambios</button>';
    echo '<button class="button refresh-unified" data-table="unified">🔄 Actualizar datos</button>';
    echo '</div>';
    echo '</div>'; // Fin domains-unified-container
}

add_action('admin_init', function () {
    register_setting('dominios_reseller_options_group', 'dominios_reseller_options', 'dominios_reseller_validate_options');

    // Configuración servidor UK (existente)
    add_settings_section('dominios_reseller_uk', 'Servidor UK (Europa)', function () {
        echo '<p>Configuración del servidor WHM en Reino Unido.</p>';
        echo '<p><strong>Servidor:</strong> 77.95.113.38:2087</p>';
    }, 'dominios-reseller');

    add_settings_field('uk_whm_token', 'API Token WHM (UK)', function () {
        $opts = get_option('dominios_reseller_options');
        $token = isset($opts['uk_whm_token']) ? esc_attr($opts['uk_whm_token']) : '';
        echo "<input type='password' name='dominios_reseller_options[uk_whm_token]' value='$token' class='regular-text' autocomplete='off' placeholder='Token del servidor UK...'>";
        echo "<p class='description'>Token de autenticación para el servidor WHM de UK</p>";
    }, 'dominios-reseller', 'dominios_reseller_uk');

    // Configuración servidor USA (nuevo)
    add_settings_section('dominios_reseller_usa', 'Servidor USA', function () {
        echo '<p>Configuración del servidor WHM en Estados Unidos.</p>';
        echo '<p><strong>Servidor:</strong> 190.92.170.164:2087</p>';
    }, 'dominios-reseller');

    add_settings_field('usa_whm_token', 'API Token WHM (USA)', function () {
        $opts = get_option('dominios_reseller_options');
        $token = isset($opts['usa_whm_token']) ? esc_attr($opts['usa_whm_token']) : '';
        echo "<input type='password' name='dominios_reseller_options[usa_whm_token]' value='$token' class='regular-text' autocomplete='off' placeholder='Token del servidor USA...'>";
        echo "<p class='description'>Token de autenticación para el servidor WHM de USA</p>";
    }, 'dominios-reseller', 'dominios_reseller_usa');

    // Sección para configuración de mensajes
    add_settings_section('dominios_reseller_messages', 'Mensajes para Shortcodes', function () {
        echo '<p>Configura los mensajes que se mostrarán cuando no se detecte un dominio específico</p>';
    }, 'dominios-reseller');

    add_settings_field('hero_title', 'Título Hero (H1)', function () {
        $opts = get_option('dominios_reseller_options');
        $title = isset($opts['hero_title']) ? esc_attr($opts['hero_title']) : 'Hosting Ecológico con Impacto Positivo';
        echo "<input type='text' name='dominios_reseller_options[hero_title]' value='$title' class='large-text' placeholder='Título principal cuando no hay dominio específico...'>";
        echo "<p class='description'>Este título aparecerá como H1 cuando no se pueda detectar el dominio</p>";
    }, 'dominios-reseller', 'dominios_reseller_messages');

    add_settings_field('hero_description', 'Descripción Hero', function () {
        $opts = get_option('dominios_reseller_options');
        $description = isset($opts['hero_description']) ? esc_textarea($opts['hero_description']) : 'Nuestro hosting funciona con energía 100% renovable y contribuye activamente a la reforestación del planeta. Cada sitio web alojado con nosotros ayuda a plantar árboles y reducir la huella de carbono.';
        echo "<textarea name='dominios_reseller_options[hero_description]' class='large-text' rows='4' placeholder='Descripción cuando no hay dominio específico...'>$description</textarea>";
        echo "<p class='description'>Esta descripción aparecerá debajo del título cuando no se pueda detectar el dominio</p>";
    }, 'dominios-reseller', 'dominios_reseller_messages');

    add_settings_field('new_domain_message', 'Mensaje para Dominios Nuevos', function () {
        $opts = get_option('dominios_reseller_options');
        $message = isset($opts['new_domain_message']) ? esc_textarea($opts['new_domain_message']) : '🌱 ¡Acabamos de comenzar este emocionante viaje juntos! Tu sitio web ya funciona con energía 100% renovable. Los árboles se plantan automáticamente cuando cumplimos nuestro primer año de colaboración. ¡Gracias por elegir un hosting que cuida el planeta!';
        echo "<textarea name='dominios_reseller_options[new_domain_message]' class='large-text' rows='3' placeholder='Mensaje para dominios sin árboles...'>$message</textarea>";
        echo "<p class='description'>Este mensaje aparece en lugar de '0 árboles plantados' para dominios nuevos</p>";
    }, 'dominios-reseller', 'dominios_reseller_messages');
});

function dominios_reseller_validate_options($input) {
    return [
        'uk_whm_token' => sanitize_text_field($input['uk_whm_token'] ?? ''),
        'usa_whm_token' => sanitize_text_field($input['usa_whm_token'] ?? ''),
        'hero_title' => sanitize_text_field($input['hero_title'] ?? ''),
        'hero_description' => sanitize_textarea_field($input['hero_description'] ?? ''),
        'new_domain_message' => sanitize_textarea_field($input['new_domain_message'] ?? '')
    ];
}

// Función para probar conexión WHM
function test_whm_connection($token, $server = 'uk') {
    if (empty($token)) {
        return [
            'success' => false,
            'error' => 'Token WHM no configurado'
        ];
    }

    $servers = [
        'uk' => '77.95.113.38',
        'usa' => '190.92.170.164'
    ];

    $server_ip = $servers[$server] ?? $servers['uk'];
    $whm_url = 'https://' . $server_ip . ':2087/json-api/listaccts?api.version=1';

    $response = wp_remote_get($whm_url, [
        'headers' => [
            'Authorization' => 'whm replanta:' . $token,
            'Accept' => 'application/json',
            'User-Agent' => 'WordPress/Replanta-Plugin'
        ],
        'timeout' => 30,
        'sslverify' => false,
        'blocking' => true
    ]);

    if (is_wp_error($response)) {
        $error_msg = $response->get_error_message();
        error_log('[Dominios Reseller] WP Error: ' . $error_msg);
        return [
            'success' => false,
            'error' => 'Error de conexión: ' . $error_msg
        ];
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    error_log('[Dominios Reseller] Test Connection - Server: ' . $server . ' Status: ' . $status_code . ' Body: ' . substr($body, 0, 200));

    if ($status_code !== 200) {
        return [
            'success' => false,
            'error' => 'Código de respuesta HTTP: ' . $status_code . ' - ' . substr($body, 0, 100)
        ];
    }

    $data = json_decode($body, true);

    if (!$data || !isset($data['data']['acct'])) {
        return [
            'success' => false,
            'error' => 'Respuesta inválida del servidor WHM'
        ];
    }

    return [
        'success' => true,
        'count' => count($data['data']['acct']),
        'message' => 'Conexión exitosa con WHM'
    ];
}

// Función para mostrar dominios de un servidor específico
function mostrar_servidor_dominios($server, $server_name, $server_ip) {
    $options = get_option('dominios_reseller_options');
    $token_key = $server . '_whm_token';
    $token = $options[$token_key] ?? '';

    echo '<div class="server-section">';
    echo '<div class="server-header">';
    echo '<h3>' . esc_html($server_name) . ' <small>(' . esc_html($server_ip) . ')</small></h3>';

    if (!empty($token)) {
        echo '<form method="post" style="display: inline-block; margin-left: 10px;">';
        echo '<input type="hidden" name="test_whm_connection" value="1">';
        echo '<input type="hidden" name="server" value="' . esc_attr($server) . '">';
        submit_button('🔧 Probar Conexión', 'secondary', 'test_connection_' . $server, false);
        echo '</form>';
    } else {
        echo '<div class="notice notice-warning inline"><p>⚠️ Configura el token WHM en la pestaña de configuración</p></div>';
    }
    echo '</div>';

    if (empty($token)) {
        echo '<div class="no-config-message">';
        echo '<p>Configure el token WHM para este servidor en la pestaña de configuración.</p>';
        echo '</div>';
        echo '</div>';
        return;
    }

    $cuentas = obtener_cuentas_whm($token, $server);
    if (!$cuentas || empty($cuentas['data']['acct'])) {
        echo '<div class="error-message">';
        echo '<p>No se encontraron cuentas en WHM o hubo un error de conexión.</p>';
        echo '</div>';
        echo '</div>';
        return;
    }

    global $wpdb;
    $tabla = $wpdb->prefix . 'dominios_reseller';

    echo '<div class="domains-table-container">';
    echo '<table class="widefat fixed striped domains-table" id="domains-table-' . $server . '" data-server="' . $server . '">';
    echo '<thead><tr>';
    echo '<th class="domain-col">Dominio</th>';
    echo '<th class="status-col">Estado</th>';
    echo '<th class="startdate-col">Inicio WHM</th>';
    echo '<th class="registered-col">Alta en Replanta</th>';
    echo '<th class="traffic-col">Tráfico (GB)</th>';
    echo '<th class="trees-col">Árboles</th>';
    echo '<th class="co2-col">CO2 Evitado (g)</th>';
    echo '<th class="actions-col">Acciones</th>';
    echo '</tr></thead><tbody>';

    foreach ($cuentas['data']['acct'] as $cuenta) {
        $dominio = esc_html($cuenta['domain']);
        $startdate = intval($cuenta['unix_startdate']);
        $suspended = $cuenta['suspended'];
        $activo = $suspended ? 'Suspendido' : 'Activo';
        $status_class = $suspended ? 'status-suspended' : 'status-active';

        $existente = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabla WHERE domain = %s", $dominio));

        $fecha_emision_calculada = date('Y-m-d', $startdate);
        $validez_calculada = date('Y-m-d', strtotime("$fecha_emision_calculada +1 year"));

        $needs_update = false;
        if ($existente) {
            if ($existente->fecha_emision !== $fecha_emision_calculada || $existente->validez !== $validez_calculada) {
                $wpdb->update($tabla, [
                    'fecha_emision' => $fecha_emision_calculada,
                    'validez'       => $validez_calculada,
                ], ['domain' => $dominio]);
                $existente->fecha_emision = $fecha_emision_calculada;
                $existente->validez = $validez_calculada;
            }
        }

        $trees = $existente->trees_planted ?? 0;
        $co2 = $existente->co2_evaded ?? 0;

        echo '<tr class="domain-row" data-domain="' . esc_attr($dominio) . '" data-server="' . $server . '">';
        echo "<td class='domain-cell'><strong>$dominio</strong></td>";
        echo "<td class='status-cell'><span class='status-badge $status_class'>$activo</span></td>";
        echo '<td class="startdate-cell">' . date('Y-m-d', $startdate) . '</td>';
        echo '<td class="registered-cell">' . esc_html($existente->fecha_emision ?? '(No reg.)') . '</td>';

        $trafico_bytes = obtener_trafico_real($dominio, $token, $server);
        $trafico_gb = $trafico_bytes ? round($trafico_bytes / (1024 ** 3), 2) : 'N/A';
        echo "<td class='traffic-cell'>$trafico_gb</td>";

        echo "<td class='trees-cell'><input type='number' class='trees-input' data-domain='$dominio' data-server='$server' value='$trees' min='0' /></td>";
        echo "<td class='co2-cell'><input type='number' class='co2-input' data-domain='$dominio' data-server='$server' value='$co2' step='0.01' /></td>";
        echo "<td class='actions-cell'><button class='button button-small calculate-emissions' data-domain='$dominio' data-server='$server'>Calcular</button></td>";
        echo '</tr>';

        // Añadir dominios adicionales (addon domains)
        $addons = obtener_addons_de_usuario($cuenta['user'], $token, $server);

        if (!is_array($addons) || empty($addons)) {
            continue;
        }

        foreach ($addons as $addon) {
            if (!is_array($addon) || !isset($addon['domain']) || empty($addon['domain'])) {
                error_log("[Dominios Reseller] Addon inválido para usuario {$cuenta['user']}: " . print_r($addon, true));
                continue;
            }

            $addon_domain = esc_html($addon['domain']);
            $addon_existente = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabla WHERE domain = %s", $addon_domain));
            $trees_addon = $addon_existente->trees_planted ?? 0;
            $co2_addon = $addon_existente->co2_evaded ?? 0;
            $fecha_emision_addon = date('Y-m-d', $startdate);
            $validez_addon = date('Y-m-d', strtotime("$fecha_emision_addon +1 year"));

            if ($addon_existente) {
                if ($addon_existente->fecha_emision !== $fecha_emision_addon || $addon_existente->validez !== $validez_addon) {
                    $wpdb->update($tabla, [
                        'fecha_emision' => $fecha_emision_addon,
                        'validez' => $validez_addon
                    ], ['domain' => $addon_domain]);
                    $addon_existente->fecha_emision = $fecha_emision_addon;
                    $addon_existente->validez = $validez_addon;
                }
            }

            echo '<tr class="addon-row" data-domain="' . esc_attr($addon_domain) . '" data-server="' . $server . '">';
            echo "<td class='domain-cell addon-domain'>└─ $addon_domain</td>";
            echo "<td class='status-cell'><span class='status-badge status-addon'>Addon</span></td>";
            echo '<td class="startdate-cell">' . date('Y-m-d', $startdate) . '</td>';
            echo '<td class="registered-cell">' . esc_html($addon_existente->fecha_emision ?? '(No reg.)') . '</td>';
            echo "<td class='traffic-cell'>N/A</td>";
            echo "<td class='trees-cell'><input type='number' class='trees-input' data-domain='$addon_domain' data-server='$server' value='$trees_addon' min='0' /></td>";
            echo "<td class='co2-cell'><input type='number' class='co2-input' data-domain='$addon_domain' data-server='$server' value='$co2_addon' step='0.01' /></td>";
            echo "<td class='actions-cell'><button class='button button-small calculate-emissions' data-domain='$addon_domain' data-server='$server'>Calcular</button></td>";
            echo '</tr>';

            if (!$addon_existente) {
                $wpdb->insert($tabla, [
                    'domain' => $addon_domain,
                    'startdate' => $startdate,
                    'fecha_emision' => date('Y-m-d', $startdate),
                    'status' => 'Addon',
                    'trees_planted' => 0,
                    'co2_evaded' => 0,
                    'primary_domain' => $dominio
                ]);
            }
        }

        if (!$existente) {
            $wpdb->insert($tabla, [
                'domain'         => $dominio,
                'startdate'      => $startdate,
                'fecha_emision'  => $fecha_emision_calculada,
                'validez'        => $validez_calculada,
                'status'         => $activo,
                'trees_planted'  => 0,
                'co2_evaded'     => 0,
                'is_primary'     => 1
            ]);
        }
    }
    echo '</tbody></table>';
    echo '<div class="table-actions">';
    echo '<button class="button button-primary save-all-changes" data-server="' . $server . '">💾 Guardar todos los cambios</button>';
    echo '<button class="button refresh-data" data-server="' . $server . '">🔄 Actualizar datos</button>';
    echo '</div>';
    echo '</div>'; // Fin domains-table-container
    echo '</div>'; // Fin server-section
}