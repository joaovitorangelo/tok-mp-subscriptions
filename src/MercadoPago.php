<?php 

namespace Tok\MPSubscriptions;

defined('ABSPATH') || exit;

/**
 * Tok_MercadoPago
 * 
 * Gerencia a integração com a API do Mercado Pago.
 * 
 * Responsável por:
 *  Inicializar webhooks ou hooks de assinatura (init()).
 *  Criar planos e assinaturas via API do Mercado Pago.
 *  Processar pagamentos, notificações ou outras ações relacionadas a assinaturas.
 * 
 * Mantém a lógica de negócio separada do admin, seguindo o princípio de separação de responsabilidades
 */
class MercadoPago {

    private $access_token;

    public function init() {
        $this->access_token = Plugin::get_option('MP_ACCESS_TOKEN');
        add_action('init', [$this, 'process_subscription']);
    }

    public function process_subscription() {
        if(isset($_POST['tok_create_subscription'])) {
            $plan_id = sanitize_text_field($_POST['plan_id']);
            $user_id = get_current_user_id();

            $response = $this->create_subscription($plan_id, $user_id);
        }
    }

    private function create_subscription($plan_id, $user_id) {
        $url = 'https://api.mercadopago.com/preapproval';
        $body = [
            'plan_id' => $plan_id,
            'payer_email' => wp_get_current_user()->user_email
        ];

        $response = wp_remote_post($url, [
            'body'    => json_encode($body),
            'headers' => [
                'Authorization' => 'Bearer '.$this->access_token,
                'Content-Type'  => 'application/json'
            ]
        ]);

        return json_decode(wp_remote_retrieve_body($response), true);
    }
}