<?php

namespace Tok\MPSubscriptions\Frontend\Handlers;

use Tok\MPSubscriptions\Core\Services\MelhorEnvio;

class SubscriptionsPlanForm {

    public static function process(array $fields) {

        $post_id = $fields['post_id']['value'];

        if (!$post_id) {
            return [
                'success' => false,
                'message' => 'Post ID não informado.',
            ];
        }

        $post_title = get_the_title( $post_id );

        $weight = (float) get_post_meta($post_id, '_plan_weight', true);
        $length = (float) get_post_meta($post_id, '_plan_length', true);
        $height = (float) get_post_meta($post_id, '_plan_height', true);
        $width  = (float) get_post_meta($post_id, '_plan_width', true);

        $cep = isset($fields['cep']['value']) ? preg_replace('/\D/', '', $fields['cep']['value']) : null;
        
        if (!$cep) {
            return [
                'success' => false,
                'message' => 'CEP inválido.',
            ];
        }

        // Monta os dados do produto para o Melhor Envio
        $data = [
            'from'                      =>  [
                'postal_code'           =>  '88780000' // Imbituba SC
            ],
            'to'                        =>  [
                'postal_code'           =>  $cep
            ],
            'products'                  =>  [
                [
                    'id'                =>  $post_id,
                    'name'              =>  $post_title,
                    'quantity'          =>  12, // Trazer dinâmicamente
                    'unitary_value'     =>  17.49,
                    'weight'            =>  $weight,
                    'length'            =>  $length,
                    'height'            =>  $height,
                    'width'             =>  $width,
                ]
            ]
        ];

        // Chama o MelhorEnvio para calcular frete
        $me = new MelhorEnvio();
        $me->init();
        $shipping = $me->calculate_shipping($data);

        return [
            'success'   =>  true,
            'message'   =>  'Cotação de frete feita com sucesso!',
            'data'      =>  $shipping,
        ];

    }

}
