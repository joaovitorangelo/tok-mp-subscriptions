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
    
    private $client;

    public function init() {
        $this->access_token = Plugin::get_option('MP_ACCESS_TOKEN');
        $this->client = new HttpClient($this->access_token);
        // add_action('init', [$this, 'process_subscription']);
    }

    // public function process_subscription() {
    //     if( isset( $_POST['tok_create_subscription'] ) ) {
    //         $plan_id = sanitize_text_field( $_POST['plan_id'] );
    //         $user_id = get_current_user_id();

    //         $response = $this->create_subscription($plan_id, $user_id);
    //     }
    // }

    /**
     * get_subscription_by_email
     * 
     * Busca Assinatura pelo E-mail
     */
    private function get_subscription_by_email($email) {
        $url = 'https://api.mercadopago.com/preapproval/search?payer_email=' . urlencode($email);
        return $this->client->get($url);
    }

    /**
     * create_plan
     * 
     * Cria um plano de assinatura.
     * 
     * Observação: o método deve ser definido como `private` para garantir a segurança do sistema, 
     * evitando acessos externos à classe. Atualmente está `public` apenas para fins de teste.
     */
    public function create_plan($data) {
        $url = 'https://api.mercadopago.com/preapproval_plan';
        return $this->client->post($url, $data);
    }

    /**
     * create_subscription
     * 
     * Vincula o cliente em um Plano de Assinatura
     */
    private function create_subscription($plan_id, $user_id) {
        $url = 'https://api.mercadopago.com/preapproval';
        $body = [
            'plan_id' => $plan_id,
            'payer_email' => wp_get_current_user()->user_email
        ];

        return $this->client->post($url, $body);
    }
}