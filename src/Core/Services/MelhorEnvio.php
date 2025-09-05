<?php 

namespace Tok\MPSubscriptions\Core\Services;

use Tok\MPSubscriptions\Infrastructure\HttpClient;

use Tok\MPSubscriptions\Infrastructure\ErrorHandler;

use Tok\MPSubscriptions\Plugin;

defined('ABSPATH') || exit;

/**
 * Tok_MelhorEnvio
 * 
 * Gerencia a integração com a API do Melhor Envio.
 * 
 * Responsável por:
 *  Calcular frete, criar etiquetas e gerenciar pedidos
 *  utilizando a API oficial do Melhor Envio.
 */
class MelhorEnvio {

    private $access_token;
    private $client;
    private $base_url;

    public function init() {
        $this->base_url = 'https://sandbox.melhorenvio.com.br/api/v2'; // altere para produção se precisar
        
        $this->access_token = Plugin::get_option('ME_ACCESS_TOKEN', '', true);
        if (!$this->access_token) {
            ErrorHandler::reportMessage("MelhorEnvio: token não definido");
            return;
        }
        
        $this->client = new HttpClient($this->access_token);
    }

    /**
     * request_shipping_quote
     * 
     * Faz uma cotação de frete
     * 
     * @example
     * $me = new \Tok\MPSubscriptions\MelhorEnvio();
     * $me->init();
     * $data = [
     *     "from" => [
     *         "postal_code" => "09530210",
     *     ],
     *     "to" => [
     *         "postal_code" => "88782000",
     *     ],
     *     "products" => [
     *         [
     *             "id" => "1",
     *             "name" => "Produto Teste",
     *             "quantity" => 1,
     *             "unitary_value" => 100.00,
     *             "weight" => 0.5,
     *             "length" => 20,
     *             "height" => 10,
     *             "width"  => 15
     *         ]
     *     ]
     * ];
     */
    public function request_shipping_quote($data) {
        $url = $this->base_url . '/me/shipment/calculate';
        return $this->client->post($url, $data);
    }

    /**
     * create_shipment
     * 
     * Cria um envio (etiqueta) no Melhor Envio
     */
    public function create_shipment($data) {
        $url = $this->base_url . '/me/shipment';
        return $this->client->post($url, $data);
    }

    /**
     * get_services
     * 
     * Lista os serviços disponíveis (Correios, Jadlog, etc)
     */
    public function get_services() {
        $url = $this->base_url . '/me/shipment/services';
        return $this->client->get($url);
    }

    /**
     * Calcula a média de preços das cotações válidas
     *
     * @param array $shippingData Array retornado do request_shipping_quote()['data']
     * @return float Média dos preços
     */
    public function calculate_average_price(array $shippingData): float {
        $prices = array_map(function($service) {
            return !empty($service['price']) ? (float) str_replace(',', '.', $service['price']) : 0;
        }, $shippingData);

        $prices = array_filter($prices); // Remove zeros ou valores nulos

        // Proteção contra erros ou divisões por zero quando o array tiver apenas um preço ou nenhum
        if (count($prices) <= 1) {
            return !empty($prices) ? round(array_sum($prices) / count($prices), 2) : 0;
        }

        // Remove o valor mais alto
        $max = max($prices);
        $key = array_search($max, $prices);
        unset($prices[$key]);

        return !empty($prices) ? round(array_sum($prices) / count($prices), 2) : 0;
    }

}
