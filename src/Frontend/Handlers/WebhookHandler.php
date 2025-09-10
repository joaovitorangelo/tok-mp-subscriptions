<?php 

namespace Tok\MPSubscriptions\Frontend\Handlers;

use Tok\MPSubscriptions\Core\Services\MercadoPago;

use Tok\MPSubscriptions\Core\PostTypes\Subscription;

use Tok\MPSubscriptions\Core\Services\SubscriptionManager;

use Tok\MPSubscriptions\Infrastructure\HttpClient;

use Tok\MPSubscriptions\Infrastructure\ErrorHandler;

defined('ABSPATH') || exit;

class WebhookHandler {

    /**
     * Inicializa os endpoints REST
     */
    public static function init() 
    {
        add_action('rest_api_init', function () {
            register_rest_route('tok-mp-subs/v1', '/cron-mp-webhook', [
                'methods'  => 'POST',
                'callback' => [self::class, 'handle_cron_mp_webhook'],
                'permission_callback' => function($request) {
                    return $request->get_param('token') === 'T0kNov@Er4';
                }
            ]);

            // Endpoint para cron
            register_rest_route('tok-mp-subs/v1', '/cron-check-subscriptions', [
                'methods'  => 'GET',
                'callback' => [self::class, 'handle_cron_check_subscriptions'],
                'permission_callback' => function($request) {
                    return $request->get_param('token') === 'T0kNov@Er4';
                }
            ]);

            // AWS Sqs Process Webhook
            register_rest_route('tok-mp-subs/v1', '/cron-process-sqs', [
                'methods'  => 'POST',
                'callback' => [self::class, 'handle_cron_process_sqs'],
                'permission_callback' => function($request) {
                    return $request->get_param('token') === 'T0kNov@Er4';
                }
            ]);
        });
    }

    /**
     * Recebe webhook do Mercado Pago após pagamento do plano de assinatura
     * 
     * funcionando...
     */
    public static function handle_cron_mp_webhook(\WP_REST_Request $request) 
    {
        $data = $request->get_json_params();

        file_put_contents(WP_CONTENT_DIR . '/uploads/mp-webhook.log', json_encode($data) . PHP_EOL, FILE_APPEND);

        if (isset($data['type']) && $data['type'] === 'preapproval') {
            $id = $data['data']['id'] ?? null;

            if ($id) {
                // Consulta os dados reais da assinatura
                $mp = new MercadoPago();
                $mp->init();
                $subscription = $mp->get_subscription($id);

                if ($subscription && $subscription['status'] === 'authorized') {
                    $manager = new SubscriptionManager($mp);
                    $manager->store_subscription($subscription);
                }
            }
        }

        return ['status' => 'ok'];
    }

    /**
     * Cron para atualizar status de assinaturas
     * 
     * ainda nao foi testado...
     */
    public static function handle_cron_check_subscriptions(\WP_REST_Request $request): array {
        $mp = new MercadoPago();
        $mp->init();

        $manager = new SubscriptionManager($mp);

        $total_updated = $manager->update_all_statuses();

        return ['status' => 'ok', 'updated' => $total_updated];
    }

    /**
     * handle_cron_process_sqs
     * 
     * Processa a fila do SQS
     */
    public static function handle_cron_process_sqs(\WP_REST_Request $request) // Tornar private
    {

        // $client = new self(''); // Token vazio, pois não usaremos para chamadas diretas
        
        // $mp = new MercadoPago();
        // $mp->init();
        
        update_option( 'sqslog', $request->get_body() );
        
        $body = json_decode( $request->get_body(), true );

        $log = [];

        $log['body'] = $body; 

        try {
            $response = $mp->create_plan( $body['url'], $body['payload'] );
            $log['response'] = $response; 
        } catch (\Exception $e) {
            $log['response'] = $e->getMessage(); 
        }

        // update_option( 'sqslog', $log );

        return;

        // update_option( 'sqslog', $log );

        // try {
        //     $result = $client->sqsClient->receiveMessage([
        //         'QueueUrl'              =>  $client->queueUrl,
        //         'MaxNumberOfMessages'   =>  10,
        //         'WaitTimeSeconds'       =>  5,
        //         'VisibilityTimeout'     =>  30,
        //     ]);

        //     if (empty($result->get('Messages'))) {
        //         throw new \Exception('Falha ao receber mensagens do SQS ou nenhuma mensagem disponível.');
        //     }

        //     $processed = 0;

        //     foreach ($result->get('Messages') as $message) {
        //         $job = json_decode($message['Body'], true);
        //         if ($job) {
        //             $method = $job['method'];
        //             $body   = $job['payload'] ?? [];

        //             if ($method === 'POST') {
        //                 $client->post($url, $body);
        //             } else {
        //                 $client->get($url, $body);
        //             }
        //         }

        //         // $client->sqsClient->deleteMessage([
        //         //     'QueueUrl'      => $client->queueUrl,
        //         //     'ReceiptHandle' => $message['ReceiptHandle'],
        //         // ]);

        //         $processed++;
        //     }

        //     return [
        //         'success'   =>  true, 
        //         'processed' =>  $processed
        //     ];
        // } catch (\Exception $e) {
        //     ErrorHandler::reportMessage($e->getMessage());
        //     return [
        //         'success'   =>  false,
        //         'message'   =>  $e->getMessage()
        //     ];
        // }
    }

}
