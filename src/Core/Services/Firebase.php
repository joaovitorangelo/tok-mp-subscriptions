<?php

namespace Tok\MPSubscriptions\Core\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;

use Google\Auth\HttpHandler\HttpHandlerFactory;

use Tok\MPSubscriptions\Plugin;

use Tok\MPSubscriptions\Infrastructure\HttpClient;

defined( 'ABSPATH' ) || exit;

/**
 * Firebase
 * 
 * Responsável por autenticação Firebase, ServiceAccountCredentials, fetchAuthToken, etc.
 */
class Firebase {

    private $credential;

    public function __construct() {
        $this->credential = new ServiceAccountCredentials(
            [
                "https://www.googleapis.com/auth/firebase.messaging",
                "https://www.googleapis.com/auth/datastore"
            ],
            json_decode( Plugin::get_option('FIREBASE_SERVICE_ACCOUNT'), true )
        );
    }

    /**
     * authenticate
     * 
     * Obtém o token de autenticação para utilizar os serviços do Google Cloud/Firebase
     */
    public function authenticate() {
        $access_token = $this->credential->fetchAuthToken( HttpHandlerFactory::build() );
        return $access_token['access_token'] ?? null;
    }

    /**
     * get_fcm_tokens_on_firestore
     * 
     * Obtém todos os tokens FCM armazenados no Firestore (coleção 'fcm_tokens')
     */
    public function fetch_and_store_fcmTokens(string $email, string $fcm_token): array|bool 
    {

        $accessToken = $this->authenticate();
        $httpClient = new HttpClient($accessToken);

        $query = [
            'structuredQuery' => [
                'from' => [['collectionId' => 'App']],
                'where' => [
                    'fieldFilter' => [
                        'field' => ['fieldPath' => 'email'],
                        'op'    => 'EQUAL',
                        'value' => ['stringValue' => $email]
                    ]
                ],
                'limit' => 1
            ]
        ];

        $response = $httpClient->post(
            'https://firestore.googleapis.com/v1/projects/' . Plugin::get_option('FIREBASE_PROJECT_ID') . '/databases/(default)/documents:runQuery',
            $query
        );

        if (empty($response[0]['document']['name'])) {
            return false;
        }

        $doc_name = $response[0]['document']['name'];

        $token_response = $httpClient->get('https://firestore.googleapis.com/v1/' . $doc_name . '/fcm_tokens');

        $user = get_user_by('email', $email);

        if (!$user) {
            return false;
        }

        $fcm_tokens = get_user_meta($user->data->ID, 'fcm_tokens', true) ?? [];

        if (!is_array($fcm_tokens)) {
            $fcm_tokens = [];
        }

        if (!isset($fcm_tokens['device_type'])) {
            $fcm_tokens['device_type'] = [];
        }

        if (!empty($token_response['documents'])) {
            foreach ($token_response['documents'] as $document) {
                if (!isset($document['fields']['device_type']['stringValue'], $document['fields']['fcm_token']['stringValue'])) {
                    continue; // pula documento inválido
                }

                $deviceType = $document['fields']['device_type']['stringValue'];
                $token = $document['fields']['fcm_token']['stringValue'];

                $fcm_tokens['device_type'][$deviceType][] = $token;

                // remover duplicados
                $fcm_tokens['device_type'][$deviceType] = array_unique($fcm_tokens['device_type'][$deviceType]);
            }
        }

        // Garante que sempre existe o índice "Web"
        if (!isset($fcm_tokens['device_type']['Web'])) {
            $fcm_tokens['device_type']['Web'] = [];
        }

        if (!empty($fcm_token)) {
            $fcm_tokens['device_type']['Web'][] = $fcm_token;
            $fcm_tokens['device_type']['Web'] = array_unique($fcm_tokens['device_type']['Web']);
        }

        update_user_meta( $user->data->ID, 'fcm_tokens', $fcm_tokens );

        return $fcm_tokens ?: [];

    }

}

