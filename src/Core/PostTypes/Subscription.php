<?php

namespace Tok\MPSubscriptions\Core\PostTypes;

defined('ABSPATH') || exit;

/**
 * Subscription
 * 
 * CPT Assinatura.
 */
class Subscription extends CustomPostType
{
    public function __construct()
    {
        parent::__construct('subscriptions', [
            'name'          => 'Subscriptions',
            'singular_name' => 'Subscription',
        ], [
            'menu_icon' => 'dashicons-awards',
            'supports'  => ['title'],
        ]);

        // Taxonomia
        $this->add_taxonomy(new Taxonomy('subscriptions_status', [
            'name'          => 'Status',
            'singular_name' => 'Status'
        ]));

        // Metabox
        $this->add_metabox('fields_subscription', 'Informações da Assinatura', [$this, 'render_metabox']);
    }

    public function render_metabox($post)
    {
        ?>
        <p><label for="subscription_code">Código da Assinatura:</label></p>
        <input type="text" name="subscription_code" id="subscription_code"
            value="<?php echo esc_attr(get_post_meta($post->ID,'_subscription_code',true)); ?>"
            style="width:100%;">
        
        <p><label for="subscription_value">Valor:</label></p>
        <input type="number" name="subscription_value" id="subscription_value"
            value="<?php echo esc_attr(get_post_meta($post->ID,'_subscription_value',true)); ?>"
            style="width:100%;">
        <?php
    }
}
