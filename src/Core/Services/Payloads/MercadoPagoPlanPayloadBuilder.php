<?php

namespace Tok\MPSubscriptions\Core\Services\Payloads;

class MercadoPagoPlanPayloadBuilder implements PayloadBuilderInterface {

    public function build(array $fields): array {
        $post_id = $fields['post_id']['value'] ?? null;
        $post_title = $post_id ? get_the_title($post_id) : '';

        $plan_price = (float) get_post_meta( $post_id, '_plan_price', true );

        $average_price = number_format( floatval( $fields['average_price']['value'] ?? 0 ), 2, '.', '' );
        
        $transaction_amount = $plan_price + $average_price;

        return [
            'reason'                    =>  $post_title . ' - ' . $fields['cep']['value'],
            'description'               =>  $post_title . ' - ' . $fields['cep']['value'],
            'payer_email'               =>  $fields['email']['value'] ?? '',
            'auto_recurring'            =>  [
                'frequency'             =>  1,
                'frequency_type'        =>  'months',
                'transaction_amount'    =>  round( $transaction_amount ),
                // 'transaction_amount'    =>  1.00, // Teste
                'currency_id'           =>  'BRL',
                'start_date'            =>  date('c'),
                'end_date'              =>  null,
            ],
            'payment_methods_allowed'   =>  [
                'payment_types'         =>  [['id' => 'credit_card']],
            ],
            'back_url'                  =>  'https://cervejaimbe.com.br/seja-um-imbetubense/obrigado-pelo-apoio/',
        ];
    }
}
