jQuery(document).ready(function($) {
    function saveData(element) {
        const domain = element.data('domain');
        const row = element.closest('tr');
        const treesPlanted = row.find('.trees-input').val();
        const co2Evaded = row.find('.co2-input').val();

        $.post(dominiosReseller.ajax_url, {
            action: 'actualizar_dominio',
            nonce: dominiosReseller.nonce,
            domain: domain,
            trees_planted: treesPlanted,
            co2_evaded: co2Evaded
        }, function(response) {
            if (response.success) {
                console.log(dominiosReseller.mensaje_guardado);
            } else {
                console.warn(dominiosReseller.mensaje_error + ': ' + (response.data?.message || ''));
            }
        });
    }

    function calcularEmisiones(domain) {
        $.post(dominiosReseller.ajax_url, {
            action: 'recalcular_co2',
            nonce: dominiosReseller.nonce,
            domain: domain
        }, function(response) {
            const row = $('#dominios-table').find('[data-domain="' + domain + '"]').closest('tr');

            if (response.success) {
                const co2 = parseFloat(response.data.co2_evaded);
                row.find('.co2-input').val(co2.toFixed(3));
                saveData(row.find('.co2-input'));
            } else {
                alert('Error al calcular emisiones: ' + (response.data?.message || ''));
            }
        });
    }

    $('#dominios-table').on('change blur', '.trees-input, .co2-input', function() {
        saveData($(this));
    });

    $('#guardar-cambios').on('click', function() {
        $('#dominios-table .trees-input, #dominios-table .co2-input').each(function() {
            saveData($(this));
        });
    });

    $('#dominios-table').on('click', '.calcular-emisiones', function() {
        const domain = $(this).data('domain');
        calcularEmisiones(domain);
    });
});
