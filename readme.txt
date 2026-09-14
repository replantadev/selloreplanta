=== Sello Replanta ===
Contributors: replantadev
Tags: hosting, footer, replanta
Requires at least: 5.0
Requires PHP: 7.4
Stable tag: 2.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Identifica el alojamiento Replanta y el plan registrado mediante un enlace informativo.

== Description ==
Muestra la marca Replanta con una indicación de infraestructura: hosting con LiteSpeed, servidor en Alemania o hosting con Cloudflare cuando se confirma su red. El enlace dirige a la información de alojamiento del dominio. No es una certificación ambiental ni una declaración de neutralidad o balance negativo de carbono.
La identificación requiere una respuesta válida de la API para el dominio exacto. Ante una respuesta no válida o un dominio no reconocido, no se muestra. Conserva las opciones de posición del plugin.

== Installation ==
1. Instala y activa el plugin en WordPress.
2. Configura la posición en Ajustes > Sello Replanta.

== Changelog ==
= 2.2.0 =
* Sustituye el sello ambiental por un identificador textual de alojamiento y plan.
* Verifica dominio exacto y contrato de API versión 2, con caché limitada.
* Retira los gráficos de carbono negativo y los datos estructurados de certificación.
* Conserva las preferencias existentes de posición.

== Upgrade Notice ==
= 2.2.0 =
El distintivo pasa a identificar el alojamiento y el plan. Actualiza y vacía la caché de página para retirar la imagen anterior.
