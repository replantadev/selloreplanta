// assets/js/admin.js - JavaScript para la interfaz moderna de Dominios Reseller v1.1.3

jQuery(document).ready(function($) {
    'use strict';
    
    console.log('Dominios Reseller Admin JS v1.1.3 loaded successfully');

    // Manejo de pestañas (compatible con ambos estilos)
    $('.nav-tab, .tab-button').on('click', function(e) {
        e.preventDefault();

        let targetTab;
        
        // Para botones de tab-button
        if ($(this).hasClass('tab-button')) {
            const tabName = $(this).data('tab');
            targetTab = '#' + tabName + '-tab';
            
            // Cambiar pestaña activa
            $('.tab-button').removeClass('active');
            $(this).addClass('active');
            
            // Mostrar contenido de pestaña
            $('.tab-pane').removeClass('active');
            $(targetTab).addClass('active');
        } else {
            // Para nav-tab (estilo WordPress)
            targetTab = $(this).attr('href');
            
            // Cambiar pestaña activa
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // Mostrar contenido de pestaña
            $('.tab-content').hide();
            $(targetTab).show();
        }

        // Actualizar URL hash
        window.location.hash = targetTab;
    });

    // Cargar pestaña desde hash URL
    if (window.location.hash) {
        const hash = window.location.hash;
        const tabButton = $(`.tab-button[data-tab="${hash.replace('#', '').replace('-tab', '')}"]`);
        const navTab = $(`.nav-tab[href="${hash}"]`);
        
        if (tabButton.length) {
            tabButton.trigger('click');
        } else if (navTab.length) {
            navTab.trigger('click');
        }
    }

    // Probar conexión WHM
    $(document).on('submit', 'form[action*="test_whm_connection"]', function(e) {
        e.preventDefault();

        const form = $(this);
        const server = form.find('input[name="server"]').val();
        const button = form.find('input[type="submit"]');
        const originalText = button.val();

        button.val('🔄 Probando...').prop('disabled', true);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'test_whm_connection',
                server: server,
                nonce: dominios_reseller_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('✅ ' + response.data.message + ' (' + response.data.count + ' cuentas)', 'success');
                } else {
                    showNotice('❌ ' + response.data.error, 'error');
                }
            },
            error: function() {
                showNotice('❌ Error de conexión con el servidor', 'error');
            },
            complete: function() {
                button.val(originalText).prop('disabled', false);
            }
        });
    });

    // Calcular emisiones para un dominio
    $(document).on('click', '.calculate-emissions', function() {
        const button = $(this);
        const domain = button.data('domain');
        const server = button.data('server');
        const row = button.closest('tr');
        const treesInput = row.find('.trees-input');
        const co2Input = row.find('.co2-input');

        const trees = parseInt(treesInput.val()) || 0;
        const co2 = parseFloat(co2Input.val()) || 0;

        if (trees === 0 && co2 === 0) {
            showNotice('⚠️ Ingresa valores para árboles o CO2 antes de calcular', 'warning');
            return;
        }

        button.text('⏳ Calculando...').prop('disabled', true);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'calculate_emissions',
                domain: domain,
                server: server,
                trees: trees,
                co2: co2,
                nonce: dominios_reseller_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('✅ Emisiones calculadas correctamente', 'success');
                    // Aquí podrías actualizar la interfaz con los nuevos valores
                } else {
                    showNotice('❌ Error al calcular emisiones: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotice('❌ Error de conexión al calcular emisiones', 'error');
            },
            complete: function() {
                button.text('Calcular').prop('disabled', false);
            }
        });
    });

    // Guardar todos los cambios
    $(document).on('click', '.save-all-changes', function() {
        const button = $(this);
        const server = button.data('server');
        const table = $(`#domains-table-${server}`);
        const originalText = button.text();

        button.text('💾 Guardando...').prop('disabled', true);

        const data = [];
        table.find('tbody tr').each(function() {
            const row = $(this);
            const domain = row.data('domain');
            const trees = row.find('.trees-input').val();
            const co2 = row.find('.co2-input').val();

            if (domain) {
                data.push({
                    domain: domain,
                    trees: parseInt(trees) || 0,
                    co2: parseFloat(co2) || 0
                });
            }
        });

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_domain_data',
                server: server,
                data: JSON.stringify(data),
                nonce: dominios_reseller_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('✅ Datos guardados correctamente', 'success');
                } else {
                    showNotice('❌ Error al guardar: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotice('❌ Error de conexión al guardar datos', 'error');
            },
            complete: function() {
                button.text(originalText).prop('disabled', false);
            }
        });
    });

    // Actualizar datos
    $(document).on('click', '.refresh-data', function() {
        const button = $(this);
        const server = button.data('server');
        const originalText = button.text();

        button.text('🔄 Actualizando...').prop('disabled', true);

        // Recargar la página para actualizar los datos
        setTimeout(function() {
            location.reload();
        }, 500);
    });

    // Función para mostrar notificaciones
    function showNotice(message, type = 'info') {
        // Remover notificaciones existentes
        $('.dominios-reseller-notice').remove();

        const noticeClass = type === 'success' ? 'notice-success' :
                           type === 'error' ? 'notice-error' :
                           type === 'warning' ? 'notice-warning' : 'notice-info';

        const notice = $(`
            <div class="notice ${noticeClass} dominios-reseller-notice">
                <p>${message}</p>
            </div>
        `);

        $('.dominios-reseller-admin').prepend(notice);

        // Auto-remover después de 5 segundos
        setTimeout(function() {
            notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Validación de inputs numéricos
    $(document).on('input', '.trees-input, .co2-input', function() {
        const value = $(this).val();
        const numValue = parseFloat(value);

        if (isNaN(numValue) || numValue < 0) {
            $(this).addClass('input-error');
        } else {
            $(this).removeClass('input-error');
        }
    });

    // Mejorar UX con tooltips
    $(document).on('mouseenter', '.status-badge', function() {
        const status = $(this).text().toLowerCase();
        let tooltip = '';

        switch(status) {
            case 'activo':
                tooltip = 'Cuenta activa en WHM';
                break;
            case 'suspendido':
                tooltip = 'Cuenta suspendida en WHM';
                break;
            case 'addon':
                tooltip = 'Dominio adicional (addon domain)';
                break;
        }

        if (tooltip) {
            $(this).attr('title', tooltip);
        }
    });

    // Confirmación antes de guardar cambios masivos
    $(document).on('click', '.save-all-changes', function(e) {
        const dataCount = $(`#domains-table-${$(this).data('server')} tbody tr`).length;

        if (dataCount > 10) {
            if (!confirm(`¿Guardar cambios para ${dataCount} dominios?`)) {
                e.preventDefault();
                return false;
            }
        }
    });

    // Auto-guardado de inputs (opcional)
    let autoSaveTimer;
    $(document).on('input', '.trees-input, .co2-input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            // Aquí podrías implementar auto-guardado silencioso
            console.log('Auto-save triggered');
        }, 2000);
    });

    // Filtros para tabla unificada
    $('#server-filter, #status-filter').on('change', function() {
        const serverFilter = $('#server-filter').val();
        const statusFilter = $('#status-filter').val();

        $('#unified-domains-table tbody tr').each(function() {
            const row = $(this);
            const rowServer = row.data('server');
            const rowStatus = row.data('status');

            let showRow = true;

            if (serverFilter && rowServer !== serverFilter.toLowerCase()) {
                showRow = false;
            }

            if (statusFilter && rowStatus !== statusFilter) {
                showRow = false;
            }

            if (showRow) {
                row.show();
            } else {
                row.hide();
            }
        });
    });

    // Guardar cambios tabla unificada
    $(document).on('click', '.save-all-unified', function() {
        const button = $(this);
        const originalText = button.text();

        button.text('💾 Guardando...').prop('disabled', true);

        const data = [];
        $('#unified-domains-table tbody tr:visible').each(function() {
            const row = $(this);
            const domain = row.data('domain');
            const server = row.data('server');
            const trees = row.find('.trees-input').val();
            const co2 = row.find('.co2-input').val();

            if (domain) {
                data.push({
                    domain: domain,
                    server: server,
                    trees: parseInt(trees) || 0,
                    co2: parseFloat(co2) || 0
                });
            }
        });

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_unified_domain_data',
                data: JSON.stringify(data),
                nonce: dominios_reseller_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('✅ Datos guardados correctamente', 'success');
                } else {
                    showNotice('❌ Error al guardar: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotice('❌ Error de conexión al guardar datos', 'error');
            },
            complete: function() {
                button.text(originalText).prop('disabled', false);
            }
        });
    });

    // Actualizar datos tabla unificada
    $(document).on('click', '.refresh-unified', function() {
        const button = $(this);
        const originalText = button.text();

        button.text('🔄 Actualizando...').prop('disabled', true);

        // Recargar la página para actualizar los datos
        setTimeout(function() {
            location.reload();
        }, 500);
    });

});