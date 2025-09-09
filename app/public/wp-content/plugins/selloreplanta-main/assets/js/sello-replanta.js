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

            console.log('🌱 Sello Replanta PRO v2.0.1 - Iniciando detección inteligente');

            // Obtener configuración del contenedor
            const position = container.dataset.position || 'auto';
            const builders = (container.dataset.builders || '').split(',').filter(Boolean);
            const zindexValue = container.dataset.zindex || '9999';
            const margin = container.dataset.margin || '0';

            console.log('📊 Page builders detectados:', builders);
            console.log('📍 Posición configurada:', position);
            console.log('🔢 Z-index configurado:', zindexValue);
            console.log('📏 Margen inferior:', margin + 'px');

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
                console.log('💬 Chats detectados:', detectedChats);
                
                // Si el z-index es automático y hay chats, bajarlo
                const zindexClass = container.className.match(/sello-zindex-(\w+)/);
                if (zindexClass && zindexClass[1] === 'auto') {
                    console.log('🔧 Ajustando z-index automáticamente para evitar conflictos con chats');
                    container.style.zIndex = '99';
                }

                // Añadir margen adicional si no está configurado
                const currentMargin = parseInt(container.dataset.margin || '0');
                if (currentMargin === 0) {
                    console.log('📏 Añadiendo margen automático para chats');
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
                console.log('🎯 Target encontrado:', targetInfo.element.tagName, targetInfo.element.className || targetInfo.element.id);
                console.log('📍 Estrategia de inserción:', targetInfo.strategy);
                this.insertIntoTarget(container, targetInfo.element, targetInfo.strategy);
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
                    console.log('✅ Footer de tema encontrado:', selector);
                    return { element: element, strategy: 'after' };
                }
            }
            
            // Fallback a footers genéricos
            for (const selector of this.selectors.generic) {
                const element = document.querySelector(selector);
                if (element && this.isVisible(element)) {
                    console.log('✅ Footer genérico encontrado:', selector);
                    return { element: element, strategy: 'after' };
                }
            }
            return null;
        },

        findBestTarget: function(builders) {
            console.log('🔍 Buscando mejor target para inserción...');
            
            // PASO 1: PRIORIDAD MÁXIMA - Buscar footers de temas
            console.log('🎯 Paso 1: Buscando footers de temas (Astra, GeneratePress, etc.)');
            for (const selector of this.selectors.themeFooters) {
                const element = document.querySelector(selector);
                if (element && this.isVisible(element)) {
                    console.log('✅ Footer de tema encontrado:', selector);
                    return { element: element, strategy: 'after' };
                }
            }

            // PASO 2: Buscar footers de page builders específicos
            console.log('🎯 Paso 2: Buscando footers de page builders');
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
                    console.log('✅ Footer de page builder encontrado:', selector);
                    return { element: element, strategy: 'after' };
                }
            }
            
            // PASO 3: Fallbacks genéricos
            console.log('🎯 Paso 3: Buscando fallbacks genéricos');
            for (const selector of this.selectors.generic) {
                const element = document.querySelector(selector);
                if (element && this.isVisible(element)) {
                    console.log('✅ Fallback encontrado:', selector);
                    return { element: element, strategy: 'append' };
                }
            }

            console.log('⚠️ No se encontró target específico, usando body');
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
                        console.log('✅ Sello insertado DESPUÉS de:', target.tagName, target.className || target.id);
                        break;
                        
                    case 'append':
                        // Insertar DENTRO del elemento al final
                        target.appendChild(container);
                        console.log('✅ Sello insertado DENTRO de:', target.tagName, target.className || target.id);
                        break;
                        
                    case 'prepend':
                        // Insertar DENTRO del elemento al principio
                        target.insertBefore(container, target.firstChild);
                        console.log('✅ Sello insertado AL PRINCIPIO de:', target.tagName, target.className || target.id);
                        break;
                        
                    default:
                        target.appendChild(container);
                        break;
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