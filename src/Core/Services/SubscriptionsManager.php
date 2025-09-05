<?php

namespace Tok\MPSubscriptions\Core\Services;

use Tok\MPSubscriptions\Core\Services\MercadoPago;

defined('ABSPATH') || exit;

class SubscriptionManager {

    private MercadoPago $mp;

    public function __construct(MercadoPago $mp) {
        $this->mp = $mp;
    }

    /**
     * Atualiza o status de todas as assinaturas do Mercado Pago no WordPress
     *
     * @return int Total de assinaturas atualizadas
     */
    public function update_all_statuses(): int {
        $limit = 50;
        $offset = 0;
        $total_updated = 0;

        do {
            $subscriptions = $this->mp->list_subscriptions($limit, $offset);

            foreach ($subscriptions as $sub) {
                $this->update_subscription_status($sub['id']);
                $total_updated++;
            }

            $offset += $limit;
        } while (!empty($subscriptions));

        return $total_updated;
    }

    /**
     * Atualiza o status de uma assinatura específica no WordPress
     *
     * @param string $subscription_id
     * @return array|null Dados da assinatura atualizada ou null se não encontrado
     */
    public function update_subscription_status(string $subscription_id): ?array {
        $subscription = $this->mp->get_subscription($subscription_id);
        if (!$subscription) return null;

        $posts = get_posts([
            'post_type'  => 'subscriptions',
            'meta_key'   => '_subscription_code',
            'meta_value' => $subscription_id,
            'numberposts'=> 1
        ]);

        if (!empty($posts)) {
            update_post_meta($posts[0]->ID, '_mp_status', $subscription['status'] ?? '');
        }

        return $subscription;
    }

    /**
     * Salva uma nova assinatura no WordPress
     *
     * @param array $subscription
     * @return int|null Post ID criado ou null se falhar
     */
    public function store_subscription(array $subscription): ?int {
        $post_id = wp_insert_post([
            'post_type'   => 'subscriptions',
            'post_status' => 'publish',
            'post_title'  => 'Assinatura - ' . ($subscription['payer_email'] ?? 'Sem e-mail'),
        ]);

        if (!$post_id) return null;

        update_post_meta($post_id, '_subscription_code', $subscription['id'] ?? '');
        update_post_meta($post_id, '_subscription_value', $subscription['transaction_amount'] ?? 0);
        update_post_meta($post_id, '_mp_status', $subscription['status'] ?? '');
        update_post_meta($post_id, '_mp_payer_email', $subscription['payer_email'] ?? '');
        update_post_meta($post_id, '_mp_plan_id', $subscription['preapproval_plan_id'] ?? '');

        return $post_id;
    }
}
