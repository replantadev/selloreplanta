/**
 * Sello Replanta PRO - JavaScript avanzado para page builders
 * Detección inteligente de Elementor, Divi, Beaver Builder y más
 */
(function() {
    'use strict';

    // Configuración PRO
    const SelloReplantaPRO = {
        selectors: {
            // Footers de temas (MÁXIMA PRIORIDAD)
            themeFooters: [
                '#colophon',               // Astra Theme
                '.ast-footer-wrap',        // Astra variants
                '.site-footer',            // GeneratePress, Astra
                '.site-info',              // Twenty themes
                '#site-footer',            // Generic theme footer
                '.footer-bottom',          // OceanWP
                '.footer-widgets',         // Various themes
                '[role="contentinfo"]'     // Semantic footer
            ],
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
            // Fallbacks generales (BAJA PRIORIDAD)
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

            // Obtener configuración del contenedor
            const position = container.dataset.position || 'auto';
            const builders = (container.dataset.builders || '').split(',').filter(Boolean);
            const zindexValue = parseInt(container.dataset.zindex, 10) || 9999;
            const margin = container.dataset.margin || '0';

            // Detectar conflictos con chats y otros plugins
            this.detectAndFixConflicts(container);

            // Aplicar color de fondo si es necesario
            this.applyBackgroundColor(container);

            // Determinar estrategia de posicionamiento
            this.positionSello(container, position, builders);

            // Mostrar con animación
            this.showSello(container);
        },

        detectAndFixConflicts: function(container) {
            const chatSelectors = [
                '.crisp-client',           // Crisp
                '#intercom-frame',         // Intercom
                '.zEWidget-launcher',      // Zendesk
                '#tawk_5',                 // Tawk.to
                '.lc_cta',                 // LiveChat
                '[class*="whatsapp"]',     // WhatsApp buttons
                '[id*="whatsapp"]',
                '.floating-button',        // Generic floating
                '.float-button'
            ];

            let chatDetected = false;
            const detectedChats = [];

            for (const selector of chatSelectors) {
                if (document.querySelector(selector)) {
                    chatDetected = true;
                    detectedChats.push(selector);
                }
            }

            if (chatDetected) {
                // Respect the admin z-index — only auto-lower if user left the default 9999
                const adminZindex = parseInt(container.dataset.zindex, 10) || 9999;
                if (adminZindex === 9999) {
                    container.style.zIndex = '99';
                }
                // else: keep the z-index the admin explicitly chose

                // Añadir margen adicional si no está configurado
                const currentMargin = parseInt(container.dataset.margin || '0');
                if (currentMargin === 0) {
                    container.style.marginBottom = '70px';
                }

                // Añadir clase especial para chats
                container.classList.add('sello-chat-friendly');
            }
        },

        positionSello: function(container, position, builders) {
            let targetInfo = null;

            // Estrategias específicas según configuración
            switch (position) {
                case 'fixed_bottom':
                    // Ya tiene la clase CSS, solo mostramos
                    this.showSello(container);
                    return;

                case 'elementor_footer':
                    targetInfo = this.findElementorFooter();
                    break;

                case 'body_end':
                    targetInfo = { element: document.body, strategy: 'append' };
                    break;

                case 'footer_end':
                    targetInfo = this.findGenericFooter();
                    break;

                default: // 'auto'
                    targetInfo = this.findBestTarget(builders);
                    break;
            }

            // Si encontramos un target, posicionar ahí
            if (targetInfo && targetInfo.element) {
                this.insertIntoTarget(container, targetInfo.element, targetInfo.strategy);
            } else {
                document.body.appendChild(container);
            }
        },

        findElementorFooter: function() {
            // Buscar footer de Elementor con múltiples estrategias
            for (const selector of this.selectors.elementor) {
                const element = document.querySelector(selector);
                if (element) {
                    return { element: element, strategy: 'after' };
                }
            }
            return null;
        },

        findGenericFooter: function() {
            // Primero intentar con footers de temas
            for (const selector of this.selectors.themeFooters) {
                const element = document.querySelector(selector);
                if (element && this.isVisible(element)) {
                    return { element: element, strategy: 'after' };
                }
            }
            
            // Fallback a footers genéricos
            for (const selector of this.selectors.generic) {
                const element = document.querySelector(selector);
                if (element && this.isVisible(element)) {
                    return { element: element, strategy: 'after' };
                }
            }
            return null;
        },

        findBestTarget: function(builders) {
            // PASO 1: PRIORIDAD MÁXIMA - Buscar footers de temas
            for (const selector of this.selectors.themeFooters) {
                const element = document.querySelector(selector);
                if (element && this.isVisible(element)) {
                    return { element: element, strategy: 'after' };
                }
            }

            // PASO 2: Buscar footers de page builders específicos
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

            for (const selector of searchOrder) {
                const element = document.querySelector(selector);
                if (element && this.isVisible(element)) {
                    return { element: element, strategy: 'after' };
                }
            }
            
            // PASO 3: Fallbacks genéricos
            for (const selector of this.selectors.generic) {
                const element = document.querySelector(selector);
                if (element && this.isVisible(element)) {
                    return { element: element, strategy: 'append' };
                }
            }

            return { element: document.body, strategy: 'append' };
        },

        insertIntoTarget: function(container, target, strategy) {
            try {
                switch (strategy) {
                    case 'after':
                        // Insertar DESPUÉS del elemento (ideal para footers)
                        if (target.nextSibling) {
                            target.parentNode.insertBefore(container, target.nextSibling);
                        } else {
                            target.parentNode.appendChild(container);
                        }
                        break;
                        
                    case 'append':
                        // Insertar DENTRO del elemento al final
                        target.appendChild(container);
                        break;
                        
                    case 'prepend':
                        // Insertar DENTRO del elemento al principio
                        target.insertBefore(container, target.firstChild);
                        break;
                        
                    default:
                        target.appendChild(container);
                        break;
                }
            } catch (error) {
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
                            break;
                        }
                    }
                }

                // Aplicar color detectado o usar por defecto
                container.style.backgroundColor = backgroundColor || '#ffffff';
                
            } catch (error) {
                container.style.backgroundColor = '#ffffff';
            }
        },

        showSello: function(container) {
            // Mostrar con animación suave
            container.style.display = 'block';
            container.style.visibility = 'visible';
            container.classList.add('sello-animate');
        },

        isVisible: function(element) {
            if (!element) return false;
            const style = window.getComputedStyle(element);
            return style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0';
        },

        fallbackInit: function() {
            const container = document.getElementById('sello-replanta-container');
            if (container && (container.style.display === 'none' || !container.parentNode)) {
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