<?php

namespace Tok\MPSubscriptions\Core\Services\Payloads;

class MercadoPagoPayloadBuilder implements PayloadBuilderInterface {

    public function build(array $fields): array {
        $post_id = $fields['post_id']['value'] ?? null;
        $post_title = $post_id ? get_the_title($post_id) : '';

        return [
            'reason'                    => 'Assinatura do plano ' . $post_title,
            'description'               =>  $post_title,
            'payer_email'               =>  $fields['email']['value'] ?? '',
            'auto_recurring'            =>  [
                'frequency'             =>  1,
                'frequency_type'        =>  'months',
                'transaction_amount'    =>  number_format(floatval($fields['average_price']['value'] ?? 0), 2, '.', ''),
                'currency_id'           =>  'BRL',
                'start_date'            =>  gmdate('c'),
                'end_date'              =>  null,
                'payment_methods'       =>  ['credit_card'],
            ],
            'back_url'                  =>  'https://cervejaimbe.com.br/plan/obrigado',
        ];
    }
}
