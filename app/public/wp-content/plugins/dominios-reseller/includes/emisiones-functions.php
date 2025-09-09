<?php
// Seguridad: Evita el acceso directo
if (!defined('ABSPATH')) exit;

/**
 * Calcula el CO2 evitado en gramos.
 *
 * @param int $trafico_bytes Total de tráfico en bytes.
 * @param float $dias_activo Número de días activo desde creación.
 * @return float CO2 evitado en gramos.
 */
function calcular_co2_ev($trafico_bytes, $dias_activo)
{
    // Parámetros
    $co2_base_por_dia = 0.015;       // g CO2 por día (energía en espera)
    $co2_por_gb_trafico = 0.075;     // g CO2 por GB transferido

    $trafico_gb = $trafico_bytes / (1024 * 1024 * 1024); // Convertir bytes a GB
    $emisiones_base = $dias_activo * $co2_base_por_dia;
    $emisiones_trafico = $trafico_gb * $co2_por_gb_trafico;

    return round($emisiones_base + $emisiones_trafico, 3); // gramos de CO2 evitados
}

/**
 * Recalcula y guarda el CO2 evitado de un dominio.
 *
 * @param string $domain
 * @param string $api_token
 * @return float|false Nuevo CO2 calculado o false si error.
 */
function recalcular_co2_para_dominio($domain, $api_token)
{
    global $wpdb;
    $table = $wpdb->prefix . 'dominios_reseller';

    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE domain = %s", $domain));

    if (!$row) return false;

    $fecha_emision = $row->fecha_emision ? strtotime($row->fecha_emision) : strtotime('-30 days');
    $dias_activo = max(1, round((time() - $fecha_emision) / DAY_IN_SECONDS));

    $trafico = obtener_trafico_real($domain, $api_token);

    if ($trafico === false) return false;

    $co2_ev = calcular_co2_ev($trafico, $dias_activo);

    $wpdb->update($table, ['co2_evaded' => $co2_ev], ['domain' => $domain]);

    return $co2_ev;
}

function obtener_trafico_real($domain, $token) {
    $mes = date('n'); // mes actual
    $anio = date('Y');

    $url = "https://s708.lon1.mysecurecloudhost.com:2087/json-api/showbw?api.version=1&searchtype=domain&search=$domain";

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
        error_log("[Dominios Reseller] Error al obtener tráfico para $domain: " . curl_error($curl));
        curl_close($curl);
        return false;
    }
    
    if ($http_code !== 200) {
        error_log("[Dominios Reseller] HTTP Error $http_code para tráfico de $domain");
        curl_close($curl);
        return false;
    }

    curl_close($curl);
    $data = json_decode($resp, true);

    if (!is_array($data) || empty($data['data']['acct'])) {
        error_log("[Dominios Reseller] WHM: Sin datos de tráfico para $domain. Respuesta: " . print_r($data, true));
        return false;
    }

    foreach ($data['data']['acct'] as $acct) {
        if (!empty($acct['bwusage']) && is_array($acct['bwusage'])) {
            foreach ($acct['bwusage'] as $entry) {
                if (isset($entry['domain']) && $entry['domain'] === $domain && isset($entry['usage'])) {
                    return intval($entry['usage']); // en bytes
                }
            }
        }
    }

    error_log("[Dominios Reseller] WHM: No se encontró tráfico específico para $domain en respuesta");
    return false;
}