=== Sello Replanta ===
Contributors: replantadev
Tags: footer, sello, ecológico, carbono negativo
Requires at least: 5.0
Tested up to: 6.8.1
Stable tag: 1.0.20
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Añade un sello de carbono negativo en el footer del sitio web si está alojado en Replanta.

== Description ==

Este plugin añade un sello de carbono negativo en el footer del sitio web si el dominio está alojado en Replanta. Puedes elegir entre un modo claro u oscuro para el sello y configurar un color de fondo personalizado.

== Installation ==

1. Sube la carpeta `sello-replanta` al directorio `/wp-content/plugins/`.
2. Activa el plugin a través del menú 'Plugins' en WordPress.
3. Ve a Ajustes > Sello Replanta para configurar el plugin.

== Frequently Asked Questions ==

= ¿Qué pasa si no configuro un color de fondo? =
El plugin detectará automáticamente el color de fondo del último elemento visible del footer.

== Changelog ==

= 1.0.20 =
* CORREGIDO: Problema principal donde el sello no se mostraba en algunos temas
* MEJORADO: JavaScript más robusto que funciona independientemente de la estructura del tema  
* MEJORADO: CSS con especificidad alta para evitar conflictos con temas
* AÑADIDO: Múltiples estrategias de posicionamiento para máxima compatibilidad
* AÑADIDO: Detección automática de color de fondo mejorada
* AÑADIDO: Sistema de fallback para posicionamiento
* AÑADIDO: Soporte responsive mejorado

= 1.0.19 =
* Versión estable anterior
* Funcionalidad básica de mostrar sello

= 1.0.6 =
* Añadida opción para configurar un color de fondo personalizado.
* Mejorada la detección automática del color de fondo.
* Cumplimiento de las buenas prácticas del repositorio de WordPress.

== Upgrade Notice ==

= 1.0.20 =
Actualización crítica que corrige problemas de visualización en múltiples temas. Se recomienda actualizar inmediatamente para asegurar que el sello se muestre correctamente.