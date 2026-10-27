=== Sello Replanta ===
Contributors: replantadev
Tags: hosting, footer, replanta
Requires at least: 5.0
Requires PHP: 7.4
Stable tag: 2.4.0
License: GPLv2 or later

Enlace al informe de alojamiento y programa de árboles de Replanta.

== Description ==
El pie muestra solo texto con enlace, sin icono ni sello: «Este website replanta» cuando el servicio tiene al menos un árbol real plantado con Tree-Nation, y «Esta web se aloja en Replanta» mientras no lo tiene. Lo decide replanta.net y el enlace lleva a la ficha pública del dominio (replanta.net/huella/). No es una certificación ambiental.

== Changelog ==

= 2.4.0 =
* Pie de solo texto (LR-01): «Este website replanta» solo con un árbol real; si no, «Esta web se aloja en Replanta». Sin icono ni sello.
* El texto y el enlace los da replanta.net (check_domain → footer); enlace a la ficha /huella/.
* Se guarda el último texto bueno: un fallo de red no hace desaparecer el pie. Consulta cada 12 h.
* Fuera las imágenes «carbon negative».

= 2.3.4 =
* Neutral gray caption to fit customer palettes; 35px inline icon and report link preserved.
= 2.3.3 =
* Icono SVG integrado de 35 × 35 px y texto horizontal pequeño.
* Visibilidad reforzada frente a estilos del tema y hover verde.

= 2.3.2 =
* SVG de 45 px con «este sitio web replanta» debajo para los tres planes identificados.
* Se conserva el color verde al pasar el cursor y el enlace al informe del dominio.
= 2.3.1 =
* Tamaño y visibilidad del icono corregidos.
