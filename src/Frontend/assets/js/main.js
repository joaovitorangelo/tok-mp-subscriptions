document.addEventListener('DOMContentLoaded', function() {
    jQuery('#form-field-cep').on('blur', function() {
        const cep = jQuery(this).val();
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
            },
            error: function(err) {
                console.error('Erro AJAX:', err);
            }
        });
    });

    jQuery(document).on('submit_success', function(event, response) {
        const customerMail = response.data.customer_mail;

        let result;
        try {
            result = (typeof customerMail === 'string') ? JSON.parse(customerMail.replace(/\*/g, '"')) : customerMail;
        } catch(e) {
            result = customerMail;
        }

        if(result.success) {
            console.log(result);
        } else {
            console.log('Ocorreu um erro!');
        }
    });
});
