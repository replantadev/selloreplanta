/**
 * Sello Replanta - JavaScript mejorado para compatibilidad universal
 */
(function() {
    'use strict';

    // Esperar a que el DOM esté listo
    function initSelloReplanta() {
        var selloContainer = document.getElementById('sello-replanta-container');
        
        if (!selloContainer) {
            return;
        }

        // Aplicar color de fondo personalizado si se especifica
        if (typeof selloReplantaData !== 'undefined' && selloReplantaData.customBgColor) {
            selloContainer.style.backgroundColor = selloReplantaData.customBgColor;
        } else {
            // Detectar automáticamente el color de fondo
            detectAndApplyBackgroundColor(selloContainer);
        }

        // Insertar el sello en la posición más apropiada
        positionSello(selloContainer);
        
        // Mostrar el sello
        selloContainer.style.display = 'block';
        selloContainer.style.visibility = 'visible';
    }

    function detectAndApplyBackgroundColor(selloContainer) {
        try {
            // Buscar elementos potenciales del footer
            var footerElements = [
                document.querySelector('footer'),
                document.querySelector('.footer'),
                document.querySelector('#footer'),
                document.querySelector('[class*="footer"]'),
                document.querySelector('body > div:last-child'),
                document.body
            ];

            var backgroundColor = null;
            
            for (var i = 0; i < footerElements.length; i++) {
                var element = footerElements[i];
                if (element) {
                    var style = window.getComputedStyle(element);
                    var bgColor = style.backgroundColor;
                    
                    // Verificar si el color no es transparente
                    if (bgColor && bgColor !== 'rgba(0, 0, 0, 0)' && bgColor !== 'transparent') {
                        backgroundColor = bgColor;
                        break;
                    }
                }
            }

            // Aplicar el color detectado o un color por defecto
            if (backgroundColor) {
                selloContainer.style.backgroundColor = backgroundColor;
            } else {
                // Color por defecto si no se puede detectar
                selloContainer.style.backgroundColor = '#ffffff';
            }
        } catch (error) {
            console.log('Sello Replanta: Error detectando color de fondo, usando color por defecto');
            selloContainer.style.backgroundColor = '#ffffff';
        }
    }

    function positionSello(selloContainer) {
        try {
            // Estrategia 1: Buscar un footer existente
            var footer = document.querySelector('footer') || 
                        document.querySelector('.footer') || 
                        document.querySelector('#footer') ||
                        document.querySelector('[class*="footer"]');

            if (footer && footer.parentNode) {
                footer.appendChild(selloContainer);
                return;
            }

            // Estrategia 2: Añadir al final del body
            if (document.body) {
                document.body.appendChild(selloContainer);
                return;
            }

            // Estrategia 3: Añadir antes del cierre del HTML
            var lastDiv = document.querySelector('body > div:last-child');
            if (lastDiv && lastDiv.parentNode) {
                lastDiv.parentNode.insertBefore(selloContainer, lastDiv.nextSibling);
                return;
            }

        } catch (error) {
            console.log('Sello Replanta: Error posicionando el sello, usando posición por defecto');
            // Último recurso: añadir al body
            if (document.body) {
                document.body.appendChild(selloContainer);
            }
        }
    }

    // Múltiples puntos de entrada para asegurar la inicialización
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSelloReplanta);
    } else {
        initSelloReplanta();
    }

    // Fallback adicional
    window.addEventListener('load', function() {
        var selloContainer = document.getElementById('sello-replanta-container');
        if (selloContainer && selloContainer.style.display === 'none') {
            initSelloReplanta();
        }
    });

})();