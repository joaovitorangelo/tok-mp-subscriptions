<?php

namespace Tok\MPSubscriptions\Core\Services;

use Tok\MPSubscriptions\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * PushNotifications
 * 
 * Responsável por construir e enviar notificações push.
 * 
 * Envia notificação para um ou mais usuarios (IOS, Android e Web)
 */
class PushNotifications {

    private $access_token;

    public function __construct() {
        $firebase = new Firebase();
        $this->access_token = $firebase->authenticate();
    }

    /**
     * send_to_user_by_email
     * 
     * Monta os dados da notificação padrão e envia para todos os tokens associados ao email informado.
     */
    public function send_to_user_by_email( array $config ): array {
        $user = get_user_by('email', $config['email']);

        if (!$user) {
            return false;
        }

        $fcm_tokens = get_user_meta($user->ID, 'fcm_tokens', true) ?? [];

        $data = [
            'title'         =>  $config['title'],
            'body'          =>  $config['body'],
            'link'          =>  $config['link'],
            'fcm_tokens'    =>  $fcm_tokens ?? []
        ];

        return $this->dispatch( $data );
        
    }

    /**
     * dispatch
     * 
     * Dispara notificações para vários tokens simultaneamente com múltiplas requisições HTTP em paralelo.
     * 
     * Para cada token:
     *  Monta a notificação
     *  Prepara a requisição cURL para enviar ao Firebase
     *  Adiciona a requisição para ser executada em paralelo
     *  Guarda o handle para depois coletar a resposta
     * 
     * @param array $data Array associativo com as chaves:
     *                    - 'title' (string)
     *                    - 'body' (string)
     *                    - 'image' (string)
     *                    - 'link' (string)
     *                    - 'fcm_tokens' (array) Lista de tokens para envio
     * 
     * @return bool Retorna um true se pelo menos uma notificação foi enviada com sucesso, ou false caso contrário.
     */
    public function dispatch( array $data ): array 
    {

        // Cria um gerenciador para múltiplas requisições HTTP paralelas usando cURL
        $multiHandle = curl_multi_init();
        // Guarda os handles individuais de cada requisição
        $curlHandles = [];
        // Guarda as respostas das requisições
        $responses = [];

        // Loop para preparar cada requisição
        foreach ( $data['fcm_tokens'] as $token ) {
            // Monta o corpo JSON da notificação para o token atual
            $body = json_encode([
                "message"                   =>  [
                    "token"                 =>  $token,
                    // Dados da notificação (título, corpo, imagem)
                    "notification"          =>  [
                        "title"             =>  $data['title'],
                        "body"              =>  $data['body'],
                        "image"             =>  wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') ?: ''
                    ],
                    // Configurações específicas para web
                    "webpush"               =>  [
                        "fcm_options"       =>  [
                            "link"          =>  $data['link']
                        ],
                    ],
                    // Configurações específicas para Android
                    "android"               =>  [
                        "priority"          =>  "high",
                        "notification"      =>  [
                            "sound"         =>  "default",
                            "click_action"  =>  ""
                        ],
                    ],
                    // Configurações específicas para iOS (APNs)
                    "apns"                  =>  [
                        "payload"           =>  [
                            "aps"           =>  [
                                "sound"     =>  "default",
                                "badge"     =>  1,
                                "alert"     =>  [
                                    "title" =>  $data['title'],
                                    "body"  =>  $data['body']
                                ],
                                "category"  =>  "GENERAL"
                            ],
                        ],
                    ],
                ]
            ]);

            // Inicializa uma requisição curl para a URL do FCM
            $ch = curl_init( 'https://fcm.googleapis.com/v1/projects/' . Plugin::get_option('FIREBASE_PROJECT_ID') . '/messages:send' );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->access_token
            ]);

            // Adiciona essa requisição ao multi-handle para executar em paralelo
            curl_multi_add_handle( $multiHandle, $ch );
            // Guarda esse handle para coletar depois a resposta
            $curlHandles[$token] = $ch;

        }

        // Executa todas as requisições em paralelo
        $running = null;
        do {
            // Executa todas as requisições adicionadas ao multi-handle, processando-as paralelamente.
            curl_multi_exec( $multiHandle, $running );
            // Bloqueia o script aguardando por atividade nas conexões cURL para otimizar uso da CPU
            curl_multi_select( $multiHandle );
        } while ( $running > 0 );

        // Coleta as respostas
        foreach ( $curlHandles as $token => $ch ) {
            // Obtém o conteúdo (resposta da API)
            $response = curl_multi_getcontent( $ch );
            // Decodifica o JSON da resposta
            $response = json_decode( $response, true );

            // Remove registro de tokens inválidos
            if (
                isset( $response['error'] ) 
                && $response['error']['message'] === 'Requested entity was not found.'
            ) {
                $user = get_user_by('email', $data['user_email'] ?? null);

                if ($user) {
                    $tokens = get_user_meta($user->ID, 'fcm_tokens', true) ?? [];
                    $tokens = array_diff($tokens, [$token]); // remove apenas o inválido
                    update_user_meta($user->ID, 'fcm_tokens', $tokens);
                }

                continue;
            }

            // Só retorna os envios bem sucedidos
            $responses[$token] = $response;
            // Remove e fecha o handle cURL para liberar recursos
            curl_multi_remove_handle( $multiHandle, $ch ); 
            curl_close( $ch );
        }

        // Libera a memória do multi-handle
        curl_multi_close($multiHandle);

        return $responses;

    }

}