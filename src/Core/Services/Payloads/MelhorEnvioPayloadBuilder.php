<?php

namespace Tok\MPSubscriptions\Core\Services\Payloads;

class MelhorEnvioPayloadBuilder implements PayloadBuilderInterface {

    public function build(array $fields): array {

        $post_id = (int) $fields['post_id']['value'];
        $post_title = get_the_title($post_id);

        $weight = (float) get_post_meta($post_id, '_plan_weight', true);
        $length = (float) get_post_meta($post_id, '_plan_length', true);
        $height = (float) get_post_meta($post_id, '_plan_height', true);
        $width  = (float) get_post_meta($post_id, '_plan_width', true);

        $cep = preg_replace('/\D/', '', $fields['cep']['value']);

        $quantity = (int) get_post_meta( $post_id, '_plan_quantity', true );

        $unitary_value = (float) get_post_meta( $post_id, '_plan_unitary_value', true );

        return [
            'from' => [
                'postal_code' => '88780000' // Imbituba SC
            ],
            'to' => [
                'postal_code' => $cep
            ],
            'products' => [
                [
                    'id'            =>  $post_id,
                    'name'          =>  $post_title,
                    'quantity'      =>  $quantity,
                    'unitary_value' =>  $unitary_value,
                    'weight'        =>  $weight,
                    'length'        =>  $length,
                    'height'        =>  $height,
                    'width'         =>  $width,
                ]
            ]
        ];
        
    }
}
