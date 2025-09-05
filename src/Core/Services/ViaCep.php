<?php

namespace Tok\MPSubscriptions\Core\Services;

use Tok\MPSubscriptions\Infrastructure\HttpClient;

class ViaCep
{
    private $httpClient;

    public function __construct()
    {
        // ViaCEP não exige token → passamos string vazia
        $this->httpClient = new HttpClient('');
    }

    /**
     * Consulta CEP completo no ViaCEP
     *
     * @param string $cep
     * @return array|null
     */
    public function consult(string $cep): ?array
    {
        $cep = preg_replace('/\D/', '', $cep);

        if (strlen($cep) !== 8) {
            return null;
        }

        $url = sprintf("https://viacep.com.br/ws/%s/json/", $cep);

        $response = $this->httpClient->get($url, [
            'headers' => ['Content-Type' => 'application/json']
        ]);

        if (!$response || isset($response['erro'])) {
            return null;
        }

        return $response;
    }
}
