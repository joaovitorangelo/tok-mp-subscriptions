document.addEventListener('DOMContentLoaded', function() {
    jQuery('#plan_price').after('<div id="calculate_shipping"></div>');
    const cepField = document.querySelector('#form-field-cep');
    let lastCep = '';

    const checkCep = () => {
        const cep = cepField.value.trim();
        if (cep === lastCep) {
            requestAnimationFrame(checkCep);
            return; // não mudou, não faz nada
        }
        lastCep = cep;

        const cepNumbersOnly = cep.replace(/\D/g, '');

        // Se estiver vazio ou não tiver exatamente 8 números, não faz nada
        if (cepNumbersOnly.length !== 8) {
            jQuery('#calculate_shipping').html('');
            requestAnimationFrame(checkCep);
            return;
        }

        // Validação final do CEP
        if (!/^\d{8}$/.test(cepNumbersOnly)) {
            alert('CEP inválido! Por favor, insira um CEP no formato 00000-000.');
            jQuery('#calculate_shipping').html('');
            requestAnimationFrame(checkCep);
            return;
        }

        // Se passou na validação, faz a requisição AJAX
        const postId = jQuery('#form-field-post_id').val();
        const fields = {
            post_id: { value: postId },
            cep:     { value: cep }
        };

        jQuery.ajax({
            url: tok_mp_subscriptions_ajax_obj.ajax_url,
            method: 'POST',
            data: {
                action: 'handle_calculate_shipping',
                fields: fields
            },
            success: function(response) {
                if (response.data === null) {
                    jQuery('#calculate_shipping').html('');
                } else if (response.data === 'Imbituba') {
                    jQuery('#calculate_shipping').html('Frete grátis');
                } else {
                    const formattedAverage = response.average.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                    jQuery('#calculate_shipping').html('Frete <strong>' + formattedAverage + '</strong>');
                }
            },
            error: function(err) {
                console.error('Erro AJAX:', err);
            }
        });

        requestAnimationFrame(checkCep); // continua monitorando
    };

    requestAnimationFrame(checkCep); // inicia o loop

    jQuery('#tok_mp_subscriptions_plan_form').on('submit_success', function(event, response) {
        const customerMail = response.data.customer_mail;

        jQuery('#tok_mp_subscriptions_plan_form .elementor-button-text').text(customerMail.message);

        console.log(customerMail);

        if ( customerMail.success ) {
            window.open(customerMail.data.init_point, '_blank');
        } else {
            return;
        }

    });
});
