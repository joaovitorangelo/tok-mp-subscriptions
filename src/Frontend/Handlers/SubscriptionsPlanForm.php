<?php

namespace Tok\MPSubscriptions\Frontend\Handlers;

use Tok\MPSubscriptions\Core\Services\MelhorEnvio;

use Tok\MPSubscriptions\Core\Services\MercadoPago;

use Tok\MPSubscriptions\Core\Services\ViaCep;

use Tok\MPSubscriptions\Core\Services\Payloads\PayloadBuilderFactory;

use Tok\MPSubscriptions\Infrastructure\ErrorHandler;

use FcmDispatcher\FcmDispatcher;

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

        $fields['average_price']['value'] = $averagePrice;

        // Monta o payload com o builder
        try {
            $builder = PayloadBuilderFactory::make('mercado_pago_plan');
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

        // Verifica se o plano já existe antes de criar
        $plan_name = get_the_title( $fields['post_id']['value'] ) . ' - ' . $fields['cep']['value'];
        $existingPlans = $planService->search_plan_by_name($plan_name);

        if (!empty($existingPlans)) {
            $plan = $existingPlans;
        } else {
            $plan = $planService->create_plan($payload);
        }

        // Dispara email com wp_mail ou sendpulse?
        $to = $fields['email']['value'];
        $subject = 'Plano de assinatura ' . get_the_title( $fields['post_id']['value'] );
        $message = 'Olá! Clique no link para continuar e concluir a compra do seu plano: ' . $plan['init_point'];
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($to, $subject, $message, $headers);
        
        // Dispara notificação push
        if (class_exists('FcmDispatcher\FcmDispatcher')) {
            $config = array(
                'notification_type'                 =>  'individual',
                'user_email'                        =>  $fields['email']['value'],
                'title'                             =>  'Plano de assinatura ' . get_the_title( $fields['post_id']['value'] ),
                'body'                              =>  'Clique aqui para continuar e concluir a compra do seu plano de assinatura.',
                'image'                             =>  'https://cervejaimbe.com.br/tok-2023/wp-content/uploads/2024/02/marca-cervejaria-imbe-300x300.png',
                'link'                              =>  $plan['init_point']
            );
            $response = (new FcmDispatcher())->sendToUserByEmail($config);
        }

        return [
            'success'       => true,
            'message'       => 'Dados recebidos com sucesso!',
            'data'          => $plan,
        ];

    }

}
