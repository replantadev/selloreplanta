<?php
// includes/whm-functions.php

if (!defined('ABSPATH')) exit;

function mostrar_lista_dominios()
{
    $options = get_option('dominios_reseller_options');
    $token = $options['whm_token'] ?? '';

    if (empty($token)) {
        echo '<p>Por favor, introduce el API Token para obtener los dominios.</p>';
        return;
    }

    $cuentas = obtener_cuentas_whm($token);
    if (current_user_can('manage_options')) {
        // echo '<pre>Respuesta WHM: ' . esc_html(print_r($cuentas, true)) . '</pre>';
    }


    if (!$cuentas || empty($cuentas['data']['acct'])) {
        echo '<pre>Token actual: ' . esc_html($token) . '</pre>';

        echo '<p>No se encontraron cuentas en WHM o hubo un error.</p>';
        return;
    }

    global $wpdb;
    $tabla = $wpdb->prefix . 'dominios_reseller';

    echo '<table class="widefat fixed striped" id="dominios-table">';
    echo '<thead><tr><th>Dominio</th><th>Inicio WHM</th><th>Alta en Replanta</th><th>Tráfico (GB)</th><th>Árboles</th><th>CO2 Evitado (g)</th><th>Estado</th><th>Acción</th></tr></thead><tbody>';

    foreach ($cuentas['data']['acct'] as $cuenta) {
        $dominio = esc_html($cuenta['domain']);
        $startdate = intval($cuenta['unix_startdate']);
        $activo = $cuenta['suspended'] ? 'Suspendido' : 'Activo';

        $existente = $wpdb->get_row($wpdb->prepare("SELECT * FROM $tabla WHERE domain = %s", $dominio));
        // Insertar si no existía
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


        echo '<tr>';
        echo "<td>$dominio</td>";
        echo '<td>' . date('Y-m-d', $startdate) . '</td>';
        echo '<td>' . esc_html($existente->fecha_emision ?? '(No reg.)') . '</td>';

        $trafico_bytes = obtener_trafico_real($dominio, $token);
        $trafico_gb = $trafico_bytes ? round($trafico_bytes / (1024 ** 3), 2) : 'N/A';
        echo "<td>$trafico_gb</td>";

        echo "<td><input type='number' class='trees-input' data-domain='$dominio' value='$trees' min='0' /></td>";
        echo "<td><input type='number' class='co2-input' data-domain='$dominio' value='$co2' step='0.01' /></td>";
        echo "<td>$activo</td>";
        echo "<td><button class='button calcular-emisiones' data-domain='$dominio'>Calcular</button></td>";
        echo '</tr>';
        // Añadir dominios adicionales (addon domains)
        $addons = obtener_addons_de_usuario($cuenta['user'], $token);
        
        // VALIDACIÓN: Verificar que $addons sea un array válido
        if (!is_array($addons) || empty($addons)) {
            continue; // Saltar si no hay addons o hay error
        }
        
        foreach ($addons as $addon) {
            // VALIDACIÓN: Verificar que $addon sea array y tenga 'domain'
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

            echo '<tr>';
            echo "<td>&nbsp;&nbsp;&nbsp;&nbsp;&rarr; $addon_domain</td>";
            echo '<td>' . date('Y-m-d', $startdate) . '</td>';
            echo '<td>' . esc_html($addon_existente->fecha_emision ?? '(No reg.)') . '</td>';



            $trafico_bytes = obtener_trafico_real($addon_domain, $token);
            $trafico_gb = $trafico_bytes ? round($trafico_bytes / (1024 ** 3), 2) : 'N/A';
            echo "<td>$trafico_gb</td>";

            echo "<td><input type='number' class='trees-input' data-domain='$addon_domain' value='$trees_addon' min='0' /></td>";
            echo "<td><input type='number' class='co2-input' data-domain='$addon_domain' value='$co2_addon' step='0.01' /></td>";
            echo "<td>Addon</td>";
            echo "<td><button class='button calcular-emisiones' data-domain='$addon_domain'>Calcular</button></td>";
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
            // Insertar si no existía
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
            // actualizar objeto local
            $existente = (object)[
                'fecha_emision' => $fecha_emision_calculada,
                'validez'       => $validez_calculada
            ];
        }
    }
    echo '</tbody></table>';
    echo '<button id="guardar-cambios" class="button button-primary">Guardar cambios</button>';
}

function obtener_cuentas_whm($token)
{
    // Usar IP directa para evitar problemas con Cloudflare
    $url = 'https://77.95.113.38:2087/json-api/listaccts?api.version=1';

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => ["Authorization: whm replanta:$token"],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10
    ]);

    $resp = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    if ($resp === false) {
        error_log('[Dominios Reseller] Error WHM: ' . curl_error($curl));
        curl_close($curl);
        return false;
    }
    
    if ($http_code !== 200) {
        error_log("[Dominios Reseller] HTTP Error $http_code en listaccts. Respuesta: " . substr($resp, 0, 500));
        curl_close($curl);
        return false;
    }
    
    curl_close($curl);

    $response_array = json_decode($resp, true);

    if (!is_array($response_array)) {
        error_log('[Dominios Reseller] Respuesta no es array. Original: ' . substr($resp, 0, 300));
        return false;
    }

    if (!isset($response_array['data']['acct']) || !is_array($response_array['data']['acct'])) {
        error_log('[Dominios Reseller] WHM: No se encontró data[acct] válido en la respuesta: ' . print_r($response_array, true));
        return false;
    }

    return $response_array;
}

function obtener_addons_de_usuario($cpanel_user, $token)
{
    $query = "https://77.95.113.38:2087/json-api/cpanel?cpanel_jsonapi_user={$cpanel_user}&cpanel_jsonapi_apiversion=2&cpanel_jsonapi_module=AddonDomain&cpanel_jsonapi_func=listaddondomains";

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $query,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => ["Authorization: whm replanta:$token"],
        CURLOPT_TIMEOUT => 30, // Timeout de 30 segundos
        CURLOPT_CONNECTTIMEOUT => 10 // Timeout de conexión
    ]);

    $resp = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    if ($resp === false) {
        error_log("[Dominios Reseller] Error en obtener_addons_de_usuario para $cpanel_user: " . curl_error($curl));
        curl_close($curl);
        return [];
    }
    
    if ($http_code !== 200) {
        error_log("[Dominios Reseller] HTTP Error $http_code para addons de $cpanel_user. Respuesta: " . substr($resp, 0, 500));
        curl_close($curl);
        return [];
    }

    curl_close($curl);
    $data = json_decode($resp, true);
    
    // VALIDACIÓN ROBUSTA: Verificar estructura de respuesta
    if (!is_array($data)) {
        error_log("[Dominios Reseller] Respuesta no es array para addons de $cpanel_user: " . substr($resp, 0, 200));
        return [];
    }
    
    if (!isset($data['cpanelresult']['data']) || !is_array($data['cpanelresult']['data'])) {
        error_log("[Dominios Reseller] Estructura inválida en addons para $cpanel_user: " . print_r($data, true));
        return [];
    }
    
    return $data['cpanelresult']['data'];
}