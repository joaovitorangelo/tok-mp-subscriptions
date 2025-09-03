<?php 

namespace Tok\MPSubscriptions\Core\Services;

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
        $this->access_token = Plugin::get_option('ME_ACCESS_TOKEN');
        $this->base_url = 'https://sandbox.melhorenvio.com.br/api/v2'; // altere para produção se precisar
        $this->client = new HttpClient($this->access_token);

        // Exemplo de hook futuro
        add_action('init', [$this, 'maybe_create_label']);
    }

    /**
     * maybe_create_label
     * 
     * Exemplo: cria etiqueta se for disparado via POST
     */
    public function maybe_create_label() {
        if (isset($_POST['tok_create_label'])) {
            $shipment = $this->create_shipment([
                // corpo mínimo para teste
                "service" => "1", // ID do serviço de entrega
                "from" => [
                    "postal_code" => "01001000"
                ],
                "to" => [
                    "postal_code" => "20040030"
                ],
                "products" => [
                    [
                        "name" => "Produto Teste",
                        "quantity" => 1,
                        "unitary_value" => 100.00
                    ]
                ]
            ]);

            // pode logar ou salvar no banco
            // error_log(print_r($shipment, true));
        }
    }

    /**
     * calculate_shipping
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
    public function calculate_shipping($data) {
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
}
