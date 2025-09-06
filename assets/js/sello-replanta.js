/**
 * Sello Replanta PRO - JavaScript avanzado para page builders
 * Detección inteligente de Elementor, Divi, Beaver Builder y más
 */
(function() {
    'use strict';

    // Configuración PRO
    const SelloReplantaPRO = {
        selectors: {
            // Elementor
            elementor: [
                '.elementor-location-footer',
                '.elementor-footer-bottom',
                '[data-elementor-type="footer"]',
                '.elementor-widget-footer'
            ],
            // Divi
            divi: [
                '#et-footer-nav',
                '.et_pb_section_footer',
                '#footer-bottom',
                '.et-db #et-main'
            ],
            // Beaver Builder
            beaver: [
                '.fl-builder-content .fl-row:last-child',
                '.fl-page-footer-wrap'
            ],
            // Temas populares
            astra: [
                '.ast-footer-bottom-inner',
                '.site-footer'
            ],
            genesis: [
                '.site-footer'
            ],
            // Fallbacks generales
            generic: [
                'footer',
                '.footer',
                '#footer',
                '[class*="footer"]',
                'body > div:last-child',
                'main + div',
                '#page'
            ]
        },

        init: function() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', this.initSello.bind(this));
            } else {
                this.initSello();
            }

            // Fallback adicional para page builders que cargan tarde
            window.addEventListener('load', this.fallbackInit.bind(this));
            
            // Detectar cambios dinámicos de Elementor
            if (window.elementorFrontend) {
                window.elementorFrontend.hooks.addAction('frontend/element_ready/widget', this.handleElementorWidget.bind(this));
            }
        },

        initSello: function() {
            const container = document.getElementById('sello-replanta-container');
            if (!container) return;

            console.log('🌱 Sello Replanta PRO - Iniciando detección inteligente');

            // Obtener configuración del contenedor
            const position = container.dataset.position || 'auto';
            const builders = (container.dataset.builders || '').split(',').filter(Boolean);

            console.log('📊 Page builders detectados:', builders);
            console.log('📍 Posición configurada:', position);

            // Aplicar color de fondo si es necesario
            this.applyBackgroundColor(container);

            // Determinar estrategia de posicionamiento
            this.positionSello(container, position, builders);

            // Mostrar con animación
            this.showSello(container);
        },

        positionSello: function(container, position, builders) {
            let target = null;
            let strategy = 'append';

            // Estrategias específicas según configuración
            switch (position) {
                case 'fixed_bottom':
                    // Ya tiene la clase CSS, solo mostramos
                    this.showSello(container);
                    return;

                case 'elementor_footer':
                    target = this.findElementorFooter();
                    break;

                case 'body_end':
                    target = document.body;
                    break;

                case 'footer_end':
                    target = this.findGenericFooter();
                    break;

                default: // 'auto'
                    target = this.findBestTarget(builders);
                    break;
            }

            // Si encontramos un target, posicionar ahí
            if (target) {
                console.log('🎯 Target encontrado:', target.tagName, target.className);
                this.insertIntoTarget(container, target, strategy);
            } else {
                console.log('⚠️ No se encontró target específico, usando body');
                document.body.appendChild(container);
            }
        },

        findElementorFooter: function() {
            // Buscar footer de Elementor con múltiples estrategias
            for (const selector of this.selectors.elementor) {
                const element = document.querySelector(selector);
                if (element) {
                    console.log('✅ Footer Elementor encontrado:', selector);
                    return element;
                }
            }
            return null;
        },

        findGenericFooter: function() {
            for (const selector of this.selectors.generic) {
                const element = document.querySelector(selector);
                if (element && this.isVisible(element)) {
                    console.log('✅ Footer genérico encontrado:', selector);
                    return element;
                }
            }
            return null;
        },

        findBestTarget: function(builders) {
            // Priorizar según page builders detectados
            const searchOrder = [];

            if (builders.includes('elementor')) {
                searchOrder.push(...this.selectors.elementor);
            }
            if (builders.includes('divi')) {
                searchOrder.push(...this.selectors.divi);
            }
            if (builders.includes('beaver')) {
                searchOrder.push(...this.selectors.beaver);
            }

            // Añadir selectores de temas populares
            searchOrder.push(...this.selectors.astra);
            searchOrder.push(...this.selectors.genesis);
            
            // Fallbacks genéricos
            searchOrder.push(...this.selectors.generic);

            // Buscar el primer elemento válido
            for (const selector of searchOrder) {
                const element = document.querySelector(selector);
                if (element && this.isVisible(element)) {
                    console.log('🎯 Mejor target encontrado:', selector);
                    return element;
                }
            }

            return document.body;
        },

        insertIntoTarget: function(container, target, strategy) {
            try {
                if (strategy === 'append') {
                    target.appendChild(container);
                } else {
                    target.insertBefore(container, target.firstChild);
                }
            } catch (error) {
                console.warn('⚠️ Error insertando sello, usando body:', error);
                document.body.appendChild(container);
            }
        },

        applyBackgroundColor: function(container) {
            // Si ya tiene color inline, no hacer nada
            if (container.style.backgroundColor) return;

            // Solo aplicar si no hay color personalizado definido
            if (typeof selloReplantaData !== 'undefined' && selloReplantaData.customBgColor) {
                container.style.backgroundColor = selloReplantaData.customBgColor;
                return;
            }

            // Detectar color automáticamente
            this.detectAndApplyBackgroundColor(container);
        },

        detectAndApplyBackgroundColor: function(container) {
            try {
                // Buscar elementos de referencia para el color
                const referenceElements = [
                    document.querySelector('.elementor-location-footer'),
                    document.querySelector('footer'),
                    document.querySelector('.footer'),
                    document.querySelector('#footer'),
                    document.body
                ];

                let backgroundColor = null;
                
                for (const element of referenceElements) {
                    if (element) {
                        const style = window.getComputedStyle(element);
                        const bgColor = style.backgroundColor;
                        
                        if (bgColor && bgColor !== 'rgba(0, 0, 0, 0)' && bgColor !== 'transparent') {
                            backgroundColor = bgColor;
                            console.log('🎨 Color detectado desde:', element.tagName, bgColor);
                            break;
                        }
                    }
                }

                // Aplicar color detectado o usar por defecto
                container.style.backgroundColor = backgroundColor || '#ffffff';
                
            } catch (error) {
                console.warn('⚠️ Error detectando color, usando blanco:', error);
                container.style.backgroundColor = '#ffffff';
            }
        },

        showSello: function(container) {
            // Mostrar con animación suave
            container.style.display = 'block';
            container.style.visibility = 'visible';
            container.classList.add('sello-animate');

            console.log('✅ Sello Replanta PRO mostrado correctamente');
        },

        isVisible: function(element) {
            if (!element) return false;
            const style = window.getComputedStyle(element);
            return style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0';
        },

        fallbackInit: function() {
            const container = document.getElementById('sello-replanta-container');
            if (container && (container.style.display === 'none' || !container.parentNode)) {
                console.log('🔄 Ejecutando fallback de inicialización');
                this.initSello();
            }
        },

        handleElementorWidget: function(widget) {
            // Manejar widgets de Elementor que se cargan dinámicamente
            if (widget && widget.closest('.elementor-location-footer')) {
                setTimeout(() => {
                    this.fallbackInit();
                }, 100);
            }
        }
    };

    // Inicializar cuando el script se carga
    SelloReplantaPRO.init();

    // Exponer para debugging
    window.SelloReplantaPRO = SelloReplantaPRO;

})();