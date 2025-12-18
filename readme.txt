=== Sello Replanta ===
Contributors: replantadev
Tags: footer, sello, ecológico, carbono negativo
Requires at least: 5.0
Tested up to: 6.8.1
Stable tag: 2.0.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Añade un sello de carbono negativo en el footer del sitio web si está alojado en Replanta.

== Description ==

Este plugin añade un sello de carbono negativo en el footer del sitio web si el dominio está alojado en Replanta. Puedes elegir entre un modo claro u oscuro para el sello y configurar un color de fondo personalizado.

Incluye verificación automática mejorada con reintentos y botón de re-verificación manual.

== Installation ==

1. Sube la carpeta `sello-replanta` al directorio `/wp-content/plugins/`.
2. Activa el plugin a través del menú 'Plugins' en WordPress.
3. Ve a Ajustes > Sello Replanta para configurar el plugin.
4. Si el dominio no se verifica automáticamente, usa el botón "Re-verificar ahora"

== Frequently Asked Questions ==

= ¿Qué pasa si no configuro un color de fondo? =
El plugin detectará automáticamente el color de fondo del último elemento visible del footer.

= ¿Por qué dice "El dominio no está en Replanta"? =
Esto puede ocurrir si:
- Tu dominio no está alojado en Replanta
- Hubo un problema temporal de conexión
- El plugin aún no verificó correctamente

Solución: Haz clic en "Re-verificar ahora" en Ajustes > Sello Replanta

== Changelog ==

= 2.0.4 =
* Verificación mejorada con reintentos automáticos
* Botón de re-verificación manual en configuración
* Mejor manejo de errores de conexión
* Interfaz mejorada mostrando estado del dominio

= 2.0.3 =
* Añadida opción para configurar un color de fondo personalizado.
* Mejorada la detección automática del color de fondo.
* Cumplimiento de las buenas prácticas del repositorio de WordPress.

== Upgrade Notice ==

= 1.0.6 =
Actualiza para obtener la nueva funcionalidad de configuración de color de fondo.