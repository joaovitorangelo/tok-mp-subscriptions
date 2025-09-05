<?php 

namespace Tok\MPSubscriptions\Frontend\Handlers;

use Tok\MPSubscriptions\Core\Services\MercadoPago;

use Tok\MPSubscriptions\Core\PostTypes\Subscription;

use Tok\MPSubscriptions\Core\Services\SubscriptionManager;

use Tok\MPSubscriptions\Infrastructure\ErrorHandler;

defined('ABSPATH') || exit;

class WebhookHandler {

    /**
     * Inicializa os endpoints REST
     */
    public static function init() {
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
                'methods'  => 'GET',
                'callback' => [self::class, 'handle_cron_process_sqs'],
                'permission_callback' => function($request) {
                    return $request->get_param('token') === 'T0kNov@Er4';
                }
            ]);
        });
    }

    /**
     * Recebe webhook do Mercado Pago
     */
    public static function handle_cron_mp_webhook(\WP_REST_Request $request) {
        $data = $request->get_json_params();

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
     */
    public static function handle_cron_check_subscriptions(\WP_REST_Request $request): array {
        $mp = new MercadoPago();
        $mp->init();

        $manager = new SubscriptionManager($mp);

        $total_updated = $manager->update_all_statuses();

        return ['status' => 'ok', 'updated' => $total_updated];
    }

    public function handle_cron_process_sqs()
    {
        if (!$this->sqsClient || !$this->queueUrl) {
            return;
        }

        try {
            $result = $this->sqsClient->receiveMessage([
                'QueueUrl' => $this->queueUrl,
                'MaxNumberOfMessages' => 10,
                'WaitTimeSeconds' => 5,
            ]);

            if (empty($result->get('Messages'))) {
                return;
            }

            foreach ($result->get('Messages') as $message) {
                $job = json_decode($message['Body'], true);

                // Reexecuta a requisição HTTP
                if ($job) {
                    $method = $job['method'];
                    $url    = $job['url'];
                    $body   = $job['payload'] ?? [];

                    if ($method === 'POST') {
                        $this->post($url, $body);
                    } else {
                        $this->get($url, $body);
                    }
                }

                // Remove da fila depois de processar
                $this->sqsClient->deleteMessage([
                    'QueueUrl' => $this->queueUrl,
                    'ReceiptHandle' => $message['ReceiptHandle'],
                ]);
            }

        } catch (\Exception $e) {
            ErrorHandler::reportMessage("Erro ao processar jobs SQS: " . $e->getMessage());
        }
    }

}
