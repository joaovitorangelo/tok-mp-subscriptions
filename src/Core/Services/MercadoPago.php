<?php 

namespace Tok\MPSubscriptions\Core\Services;

use Tok\MPSubscriptions\Infrastructure\HttpClient;

use Tok\MPSubscriptions\Infrastructure\ErrorHandler;

use Tok\MPSubscriptions\Plugin;

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
        // $this->access_token = Plugin::get_option('MP_ACCESS_TOKEN', '', true);
        $this->access_token = 'TEST-4483457805093712-082916-ff67e799d0071665b5862f7c82397e33-1923849358';
        if (!$this->access_token) {
            ErrorHandler::reportMessage("MercadoPago: token não definido");
            return;
        }

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

    public function list_subscriptions(int $limit = 50, int $offset = 0): array {
        $url = "https://api.mercadopago.com/preapproval/search?limit={$limit}&offset={$offset}";
        $response = $this->client->get($url);

        return $response['results'] ?? [];
    }

    public function search_plan_by_name($name) {
        // Busca planos existentes (limitando a 50 por exemplo)
        $url = 'https://api.mercadopago.com/preapproval_plan/search?limit=50';
        $response = $this->client->get($url);

        if (!isset($response['results'])) {
            return null; // Nenhum plano encontrado
        }

        // Percorre os resultados e retorna o primeiro plano ativo com o mesmo 'reason'
        foreach ($response['results'] as $plan) {
            if (isset($plan['reason'], $plan['status']) 
                && $plan['reason'] === $name 
                && $plan['status'] === 'active') {
                return $plan; // Retorna o primeiro encontrado
            }
        }

        return null; // Nenhum plano ativo encontrado
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

        try {
            $response = $this->post($url, $data);

            // Aqui você pode verificar se a criação deu certo
            if (isset($response['status']) && $response['status'] >= 400) {
                throw new \Exception('Erro ao criar plano: ' . json_encode($response));
            }

            return $response;

        } catch (\Exception $e) {
            // Envia para a fila SQS para tentar novamente depois
            $this->sendToSqs('POST', $url, $data);

            // Log do erro para debug
            error_log("Falha ao criar plano. Enviado para SQS: " . $e->getMessage());

            // Retorna false ou null para indicar falha
            return null;
        }
    }

    /**
     * create_subscription
     * 
     * Vincula o cliente em um Plano de Assinatura
     */
    public function create_subscription($plan_id, $payer_email) {
        $url = 'https://api.mercadopago.com/preapproval';
        $body = [
            'preapproval_plan_id'   =>  $plan_id,
            'payer_email'           =>  $payer_email,
        ];
        return $this->client->post($url, $body);
    }

    public function configure_webhook($url) {
        $endpoint = 'https://api.mercadopago.com/webhooks';

        $body = [
            'url' => $url,
            'event_types' => ['preapproval', 'payment']
        ];

        return $this->client->post($url, $body);
    }

}