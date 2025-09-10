<?php

namespace Tok\MPSubscriptions\Frontend\Handlers;

use Tok\MPSubscriptions\Core\Services\MelhorEnvio;

use Tok\MPSubscriptions\Core\Services\MercadoPago;

use Tok\MPSubscriptions\Core\Services\ViaCep;

use Tok\MPSubscriptions\Core\Services\Payloads\PayloadBuilderFactory;

use Tok\MPSubscriptions\Infrastructure\ErrorHandler;

use Tok\MPSubscriptions\Core\Services\PushNotifications;

use Tok\MPSubscriptions\Frontend\EmailHelper;

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

        // Monta os dados do template do email
        $email_html = EmailHelper::get_template('subscription_notification', [
            'user_name' => $fields['name']['value'] ?? 'Cliente',
            'plan_link' => $plan['init_point'],
            'subject'   => 'Plano de assinatura ' . get_the_title($fields['post_id']['value']),
        ]);

        // Envia o email
        wp_mail(
            $fields['email']['value'],
            'Plano de assinatura ' . get_the_title($fields['post_id']['value']),
            $email_html,
            ['Content-Type: text/html; charset=UTF-8']
        );
        
        // Dispara notificação push
        $pushService = new PushNotifications();
        $pushService->init();

        $push = $pushService->send_to_user_by_email([
            'email' => $fields['email']['value'],
            'title' => 'Plano de assinatura ' . get_the_title( $fields['post_id']['value'] ),
            'body'  => 'Clique aqui para continuar e concluir a compra do seu plano de assinatura.',
            'link'  => $plan['init_point']
        ]);

        return [
            'success'       => true,
            'message'       => 'Dados recebidos com sucesso!',
            'data'          => $plan,
        ];

    }
    
    public static function get_email_template(string $template_name, array $vars = []): string {
        $template_file = __DIR__ . "/Emails/{$template_name}.php";

        if (!file_exists($template_file)) {
            return '';
        }

        extract($vars);

        ob_start();
        include $template_file;
        return ob_get_clean();
    }

}
