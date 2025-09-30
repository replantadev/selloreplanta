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
 * @param string $server
 * @return float|false Nuevo CO2 calculado o false si error.
 */
function recalcular_co2_para_dominio($domain, $api_token, $server = 'uk')
{
    global $wpdb;
    $table = $wpdb->prefix . 'dominios_reseller';

    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE domain = %s", $domain));

    if (!$row) return false;

    $fecha_emision = $row->fecha_emision ? strtotime($row->fecha_emision) : strtotime('-30 days');
    $dias_activo = max(1, round((time() - $fecha_emision) / 86400));

    $trafico = obtener_trafico_real($domain, $api_token, $server);

    if ($trafico === false) return false;

    $co2_ev = calcular_co2_ev($trafico, $dias_activo);

    $wpdb->update($table, ['co2_evaded' => $co2_ev], ['domain' => $domain]);

    return $co2_ev;
}