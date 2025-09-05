<?php

namespace Tok\MPSubscriptions\Infrastructure;

use Aws\Sqs\SqsClient;

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
        // $this->initSqs();
    }

    private function initSqs()
    {
        try {
            
            // Inicializa SQS
            $this->sqsClient = new SqsClient([
                'region'        =>  'us-east-2',
                'version'       =>  'latest',
                'credentials'   =>  [ // Armazenar credenciais em um local seguro
                    'key'       =>  '',
                    'secret'    =>  ''
                ]
            ]);

            // URL da fila SQS
            $this->queueUrl = '';

            $send = $this->sqsClient->sendMessage([
                'QueueUrl'    => $this->queueUrl,
                'MessageBody' => json_encode(['test' => 'ok', 'time' => time()]),
            ]);

            $result['sqs'] = ['MessageId' => $send['MessageId'] ?? null];
            
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
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
            $this->sendToSqs($this->last_method, $this->last_url, $this->last_body);

            return [
                'error' => true,
                'message' => $errorMessage,
            ];
        }

        $decoded = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($decoded['error'])) {
            $message = "API Error: " . json_encode($decoded);
            ErrorHandler::reportMessage($message);
            $this->sendToSqs($this->last_method, $this->last_url, $this->last_body);
        }

        return $decoded;
    }

    private function sendToSqs($method, $url, $body)
    {
        $payload = [
            'method' => $method,
            'url' => $url,
            'payload' => $body,
            'headers' => $this->get_headers(),
        ];

        try {
            $this->sqsClient->sendMessage([
                'QueueUrl'    => $this->queueUrl,
                'MessageBody' => json_encode($payload),
            ]);

            ErrorHandler::reportMessage("Falha no $method $url. Job enviado para SQS.");
        } catch (\Exception $e) {
            ErrorHandler::reportMessage("Erro ao enviar job para SQS: " . $e->getMessage());
        }
    }

    public function processSqsJobs()
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
