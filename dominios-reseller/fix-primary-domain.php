<?php
/**
 * Script para arreglar registros con primary_domain NULL
 * Ejecutar una sola vez desde wp-admin/admin.php?page=dominios-reseller
 */

// Este código debe ejecutarse desde WordPress
if (!defined('ABSPATH')) {
    die('Este script debe ejecutarse desde WordPress');
}

global $wpdb;
$tabla = $wpdb->prefix . 'dominios_reseller';

// Actualizar dominios principales (donde primary_domain es NULL y el dominio NO es addon)
$updated_primary = $wpdb->query("
    UPDATE $tabla 
    SET primary_domain = domain 
    WHERE primary_domain IS NULL 
    AND (status != 'Addon' OR status IS NULL)
");

echo "<div class='notice notice-success'><p>✅ Se actualizaron $updated_primary dominios principales con primary_domain = domain</p></div>";

// Verificar si quedan registros con primary_domain NULL
$remaining = $wpdb->get_var("SELECT COUNT(*) FROM $tabla WHERE primary_domain IS NULL");

if ($remaining > 0) {
    echo "<div class='notice notice-warning'><p>⚠️ Quedan $remaining registros con primary_domain NULL (probablemente addons sin dominio padre). Revisa manualmente.</p></div>";
    
    $nulls = $wpdb->get_results("SELECT domain, status FROM $tabla WHERE primary_domain IS NULL LIMIT 10");
    echo "<ul>";
    foreach ($nulls as $row) {
        echo "<li>{$row->domain} (Status: {$row->status})</li>";
    }
    echo "</ul>";
} else {
    echo "<div class='notice notice-success'><p>✅ Todos los registros tienen primary_domain correctamente asignado</p></div>";
}
