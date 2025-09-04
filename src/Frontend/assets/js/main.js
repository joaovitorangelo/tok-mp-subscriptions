document.addEventListener('DOMContentLoaded', function() {
    const priceDefault = jQuery('#plan_price').html();
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
            jQuery('#plan_price').html(priceDefault);
            requestAnimationFrame(checkCep);
            return;
        }

        // Validação final do CEP
        if (!/^\d{8}$/.test(cepNumbersOnly)) {
            alert('CEP inválido! Por favor, insira um CEP no formato 00000-000.');
            jQuery('#plan_price').html(priceDefault);
            requestAnimationFrame(checkCep);
            return;
        }

        // Se passou na validação, faz a requisição AJAX
        const postId = jQuery('#form-field-post_id').val();
        const fields = {
            post_id: { value: postId },
            cep: { value: cep }
        };

        jQuery.ajax({
            url: tok_mp_subscriptions_ajax_obj.ajax_url,
            method: 'POST',
            data: {
                action: 'handle_calculate_shipping',
                fields: fields
            },
            success: function(response) {
                console.log(response);
                if (response.average !== 0) {
                    const formattedAverage = response.average.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                    jQuery('#plan_price').html('<strong>' + formattedAverage + '</strong>');
                } else {
                    jQuery('#plan_price').html(priceDefault);
                }
            },
            error: function(err) {
                console.error('Erro AJAX:', err);
            }
        });

        requestAnimationFrame(checkCep); // continua monitorando
    };

    requestAnimationFrame(checkCep); // inicia o loop

    jQuery(document).on('submit_success', function(event, response) {
        const customerMail = response.data.customer_mail;

        let result;
        try {
            result = (typeof customerMail === 'string') ? JSON.parse(customerMail.replace(/\*/g, '"')) : customerMail;
        } catch(e) {
            result = customerMail;
        }

        console.log(result);
    });
});
