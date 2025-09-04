<?php

namespace Tok\MPSubscriptions\Frontend\Handlers;

use Tok\MPSubscriptions\Core\Services\MelhorEnvio;

use Tok\MPSubscriptions\Core\Services\MercadoPago;

use Tok\MPSubscriptions\Core\Services\Payloads\PayloadBuilderFactory;

use Tok\MPSubscriptions\Infrastructure\ErrorHandler;

class SubscriptionsPlanForm {

    public static function process(array $fields) {

        // Monta o payload com o builder
        try {
            $builder = PayloadBuilderFactory::make('melhor_envio');
            $payload = $builder->build($fields);
        } catch (\Throwable $e) { // Captura qualquer tipo de erro ou exceção
            ErrorHandler::report($e);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        // Cria o serviço de frete
        $shippingService = new MelhorEnvio();
        $shippingService->init();

        // Calcula o frete
        $shipping = $shippingService->request_shipping_quote($payload);

        // Calcula a média
        $averagePrice = $shippingService->calculate_average_price($shipping ?? []);

        $fields['average_price']['value'] = $averagePrice; 

        // Monta o payload com o builder
        try {
            $builder = PayloadBuilderFactory::make('mercado_pago');
            $payload = $builder->build($fields);
        } catch (\Throwable $e) { // Captura qualquer tipo de erro ou exceção
            ErrorHandler::report($e);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        // Cria o serviço de plano
        $planService = new MercadoPago();
        $planService->init();

        $plan = $planService->create_plan($payload);
        
        return [
            'success'   =>  true,
            'message'   =>  'Plano de Assinatura feito com sucesso!',
            'data'      =>  $plan,
        ];

    }

}
