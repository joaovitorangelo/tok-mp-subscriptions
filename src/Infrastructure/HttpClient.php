<?php

namespace Tok\MPSubscriptions\Infrastructure;

defined('ABSPATH') || exit;

/**
 * HttpClient
 * 
 * Centraliza as chamadas HTTP para a API do Mercado Pago
 * Reutilizável para POST e GET
 */
class HttpClient
{
    private $access_token;

    public function __construct($access_token)
    {
        $this->access_token = $access_token;
    }

    public function get($url, $args = [])
    {
        $response = wp_remote_get($url, [
            'headers' => $this->get_headers(),
        ] + $args);

        return $this->handle_response($response);
    }

    public function post($url, $body = [], $args = [])
    {
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
            return [
                'error' => true,
                'message' => $response->get_error_message(),
            ];
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }
}
