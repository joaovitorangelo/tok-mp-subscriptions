<?php

namespace Tok\MPSubscriptions\Infrastructure;

use Aws\Sqs\SqsClient;

use Tok\MPSubscriptions\Plugin;

defined('ABSPATH') || exit;

/**
 * HttpClient
 * 
 * Centraliza chamadas HTTP para APIs (Mercado Pago / MelhorEnvio)
 * Envia falhas para SQS + Sentry
 */
class HttpClient
{
    private $access_token;

    private $last_method;
    private $last_url;
    private $last_body;

    private $sqsClient;
    private $queueUrl;

    public function __construct($access_token)
    {
        $this->access_token = $access_token;

        // Inicializa AWS SQS
        $this->initSqs();
    }

    private function initSqs()
    {
        try {
            // Inicializa SQS
            $this->sqsClient    =   new SqsClient([
                'region'        =>  Plugin::get_option('AWS_REGION'),
                'version'       =>  'latest',
                'credentials'   =>  [
                    'key'       =>  Plugin::get_option('AWS_KEY'),
                    'secret'    =>  Plugin::get_option('AWS_SECRET')
                ]
            ]);

            // URL da fila SQS
            $this->queueUrl = 'https://sqs.' . Plugin::get_option('AWS_REGION') . '.amazonaws.com/' . Plugin::get_option('AWS_ACCOUNT_ID') . '/tok-mp-subscriptions-jobs'; // Identificador da conta que criou a fila
        } catch (\Exception $e) {
            ErrorHandler::reportMessage('Erro ao inicializar SQS: ' . $e->getMessage());
            // Evita inicializar SQS se houver falha
            $this->sqsClient = null;
            $this->queueUrl  = null;
        }
    }

    public function get($url, $args = [])
    {
        $this->last_method = 'GET';
        $this->last_url = $url;
        $this->last_body = $args;

        $response = wp_remote_get($url, [
            'headers' => $this->get_headers(),
        ] + $args);

        return $this->handle_response($response);
    }

    public function post($url, $body = [], $args = [])
    {
        $this->last_method = 'POST';
        $this->last_url = $url;
        $this->last_body = $body;

        $response = wp_remote_post($url, [
            'body'    => json_encode($body),
            'headers' => $this->get_headers(),
        ] + $args);

        return $this->handle_response($response);
    }

    private function get_headers()
    {
        return [
            'Authorization' => 'Bearer ' . $this->access_token,
            'Content-Type'  => 'application/json',
        ];
    }

    private function handle_response($response)
    {
        if (is_wp_error($response)) {
            $errorMessage = $response->get_error_message();

            ErrorHandler::reportMessage("HTTP Request Failed: " . $errorMessage);
            // $this->sendToSqs($this->last_method, $this->last_url, $this->last_body);

            return [
                'error'     =>  true,
                'message'   =>  $errorMessage,
            ];
        }

        $decoded = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($decoded['error'])) {
            ErrorHandler::reportMessage("API Error: " . json_encode($decoded));
            // $this->sendToSqs($this->last_method, $this->last_url, $this->last_body);
        }

        return $decoded;
    }

    /**
     * sendToSqs
     * 
     * Envia o erro para fila SQS
     */
    public function sendToSqs($method, $url, $body) // Tornar private
    {
        $payload = [
            'method'    =>  $method,
            'url'       =>  $url,
            'payload'   =>  $body,
            'headers'   =>  $this->get_headers(),
        ];

        try {
            $this->sqsClient->sendMessage([
                'QueueUrl'                  =>  $this->queueUrl,
                'MessageBody'               =>  json_encode($payload),
                // Para .fifo
                // 'MessageGroupId'            =>  'default',
                // 'MessageDeduplicationId'    =>  uniqid(),
            ]);
        } catch (\Exception $e) {
            ErrorHandler::reportMessage("Erro ao enviar job para SQS: " . $e->getMessage());
            return [
                'success'   =>  false,
                'message'   =>  'Falha ao enviar job para SQS: ' . $e->getMessage(),
            ];
        }

        return [
            'success'   =>  true,
            'message'   =>  'Job enviado para SQS com sucesso.',
        ];
    }

    // public static function handle_cron_process_sqs(\WP_REST_Request $request) // Tornar private
    // {

    //     // Debug: veja se o request chegou
    //     update_option('sqslog_raw', $request); // salva o objeto inteiro
    //     update_option('sqslog_body', $request->get_body()); // corpo cru
    //     update_option('sqslog_json', $request->get_json_params()); // json parseado
    //     return ['status' => 'ok'];

    //     // $client = new self(''); // Token vazio, pois não usaremos para chamadas diretas

    //     // $log = [];

    //     // $data = $request->get_json_params();

    //     // $log['data'] = $data;

    //     // if (empty($data)) {
    //     //     return ['status' => 'error', 'message' => 'Dados não enviados'];
    //     // }

    //     // $mp = new MercadoPago();
    //     // $mp->init();

    //     // try {
    //     //     $response = $mp->create_plan($data); // Aqui você usa sua função PHP segura
    //     //     $log['response'] = $response; 
    //     //     // return ['status' => 'ok', 'response' => $response];
    //     // } catch (\Exception $e) {
    //     //     // Log para monitorar falhas
    //     //     // error_log('Erro ao tentar criar plano via webhook retry: ' . $e->getMessage());
    //     //     $log['response'] = $e->getMessage(); 
    //     //     // return ['status' => 'error', 'message' => $e->getMessage()];
    //     // }

    //     // update_option( 'sqslog', $log );

    //     // try {
    //     //     $result = $client->sqsClient->receiveMessage([
    //     //         'QueueUrl'              =>  $client->queueUrl,
    //     //         'MaxNumberOfMessages'   =>  10,
    //     //         'WaitTimeSeconds'       =>  5,
    //     //         'VisibilityTimeout'     =>  30,
    //     //     ]);

    //     //     if (empty($result->get('Messages'))) {
    //     //         throw new \Exception('Falha ao receber mensagens do SQS ou nenhuma mensagem disponível.');
    //     //     }

    //     //     $processed = 0;

    //     //     foreach ($result->get('Messages') as $message) {
    //     //         $job = json_decode($message['Body'], true);
    //     //         if ($job) {
    //     //             $method = $job['method'];
    //     //             $body   = $job['payload'] ?? [];

    //     //             if ($method === 'POST') {
    //     //                 $client->post($url, $body);
    //     //             } else {
    //     //                 $client->get($url, $body);
    //     //             }
    //     //         }

    //     //         // $client->sqsClient->deleteMessage([
    //     //         //     'QueueUrl'      => $client->queueUrl,
    //     //         //     'ReceiptHandle' => $message['ReceiptHandle'],
    //     //         // ]);

    //     //         $processed++;
    //     //     }

    //     //     return [
    //     //         'success'   =>  true, 
    //     //         'processed' =>  $processed
    //     //     ];
    //     // } catch (\Exception $e) {
    //     //     ErrorHandler::reportMessage($e->getMessage());
    //     //     return [
    //     //         'success'   =>  false,
    //     //         'message'   =>  $e->getMessage()
    //     //     ];
    //     // }
    // }

}
