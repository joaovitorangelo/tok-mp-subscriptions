<?php

namespace Tok\MPSubscriptions\Core\Services\Payloads;

class MercadoPagoPayloadBuilder implements PayloadBuilderInterface {

    public function build(array $fields): array {
        $post_id = $fields['post_id']['value'];
        $post_title = get_the_title($post_id);

        return [
            'transaction_amount' => (float) ($fields['price']['value'] ?? 0),
            'description'        => $post_title,
            'payment_method_id'  => $fields['payment_method']['value'] ?? 'pix',
            'payer' => [
                'email' => $fields['email']['value'] ?? '',
            ],
        ];
    }
}
