<?php 

namespace Tok\MPSubscriptions\Frontend\Handlers;

use Tok\MPSubscriptions\Core\PostTypes\Subscription;

use Tok\MPSubscriptions\Core\Services\MercadoPago;

defined('ABSPATH') || exit;

class WebhookHandler {

    public static function init() {
        add_action('rest_api_init', function () {
            register_rest_route('tok-mp/v1', '/webhook', [
                'methods'  => 'POST',
                'callback' => [self::class, 'handle_webhook'],
                'permission_callback' => '__return_true'
            ]);
        });
    }

    public static function handle_webhook(\WP_REST_Request $request) {
        $data = $request->get_json_params();

        if (isset($data['type']) && $data['type'] === 'preapproval') {
            $id = $data['data']['id'] ?? null;

            if ($id) {
                // Consulta os dados reais da assinatura
                $mp = new MercadoPago();
                $mp->init();
                $subscription = $mp->get_subscription($id);

                if ($subscription && $subscription['status'] === 'authorized') {
                    // Assinatura confirmada → salva no CPT
                    self::store_subscription($subscription);
                }
            }
        }

        return ['status' => 'ok'];
    }

    private static function store_subscription($subscription) {
        // Cria o post "subscription"
        $post_id = wp_insert_post([
            'post_type'   => 'subscriptions',
            'post_status' => 'publish',
            'post_title'  => 'Assinatura - ' . $subscription['payer_email'],
        ]);

        if (!$post_id) return;

        // Salva metadados correspondentes ao seu CPT
        update_post_meta($post_id, '_subscription_code', $subscription['id']);
        update_post_meta($post_id, '_subscription_value', $subscription['transaction_amount'] ?? 0);
        update_post_meta($post_id, '_mp_status', $subscription['status']);
        update_post_meta($post_id, '_mp_payer_email', $subscription['payer_email']);
        update_post_meta($post_id, '_mp_plan_id', $subscription['preapproval_plan_id']);
    }
}
