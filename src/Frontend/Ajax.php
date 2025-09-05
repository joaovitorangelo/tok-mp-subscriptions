<?php

namespace Tok\MPSubscriptions\Frontend;

use Tok\MPSubscriptions\Core\Services\MelhorEnvio;

use Tok\MPSubscriptions\Core\Services\ViaCep;

use Tok\MPSubscriptions\Core\Services\Payloads\PayloadBuilderFactory;

class Ajax {

    public static function init() {
        // Para usuários logados
        add_action('wp_ajax_handle_calculate_shipping', [self::class, 'handle_calculate_shipping']);
        // Para usuários não logados
        add_action('wp_ajax_nopriv_handle_calculate_shipping', [self::class, 'handle_calculate_shipping']);
    }

    public static function handle_calculate_shipping() {

        // Recebe dados via POST
        $fields = $_POST['fields'] ?? [];

        if (empty($fields)) {
            wp_send_json([
                'success' => false,
                'message' => 'Nenhum dado recebido.'
            ]);
        }

        // Monta o payload com o builder
        try {
            $builder = PayloadBuilderFactory::make('melhor_envio');
            $payload = $builder->build($fields);
        } catch (\Throwable $e) { // Captura qualquer tipo de erro ou exceção
            wp_send_json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        $viaCep = new ViaCep();
        $dadosCep = $viaCep->consult($fields['cep']['value']);

        // Verifica se o CEP é de Imbituba
        if ($dadosCep && $dadosCep['localidade'] !== 'Imbituba') {
            // Cria o serviço de frete
            $shippingService = new MelhorEnvio();
            $shippingService->init();

            // Calcula o frete
            $shipping = $shippingService->request_shipping_quote($payload);

            // Calcula a média
            $averagePrice = $shippingService->calculate_average_price($shipping ?? []);
        } else {
            // Define valor de frete como 0 ou algo específico
            $averagePrice = 0;
        }

        wp_send_json([
            'success' => true,
            'message' => 'Cotação de frete feita com sucesso!',
            'data'    => $dadosCep['localidade'],
            'average' => $averagePrice
        ]);

    }
}
