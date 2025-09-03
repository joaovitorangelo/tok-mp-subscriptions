<?php 

namespace Tok\MPSubscriptions\Core\Services;

use Tok\MPSubscriptions\Infrastructure\HttpClient;

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
        
        $this->access_token = Plugin::get_option('ME_ACCESS_TOKEN');
        if (!$this->access_token) {
            error_log('MercadoEnvio: token não definido');
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
        $total = 0;
        $count = 0;

        foreach ($shippingData as $service) {
            if (!empty($service['price'])) {
                $price = (float) str_replace(',', '.', $service['price']);
                $total += $price;
                $count++;
            }
        }

        return $count > 0 ? round($total / $count, 2) : 0;
    }

}
