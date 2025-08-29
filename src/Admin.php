<?php

namespace Tok\MPSubscriptions;

defined('ABSPATH') || exit;

/**
 * Tok_Admin
 * 
 * Gerencia toda a parte de administração do plugin:
 *  Criação da página de configurações (add_options_page).
 *  Registro das opções no banco (register_setting) usando a Settings API.
 *  Renderização dos campos (add_settings_field) e do formulário de configuração.
 * 
 * Aqui você define todos os campos do Firebase, Mercado Pago ou qualquer configuração que o usuário precise ajustar.
 * 
 * É separada para organizar o código e seguir OOP, deixando o admin isolado do resto do plugin
 */
class Admin {

    public function init() {
        add_action('admin_menu', [$this, 'register_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings_page() {
        add_options_page(
            'Mercado Pago Subscriptions',
            'Mercado Pago Subscriptions',
            'manage_options',
            'mp-subscriptions-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting('mp_subscriptions_group', 'mp_settings');

        add_settings_section(
            'mp_main_section',
            'Configurações do Mercado Pago / Melhor Envio',
            '__return_false',
            'mp-subscriptions-settings'
        );

        $this->add_field('MP_PUBLIC_KEY', 'MP_PUBLIC_KEY');
        $this->add_field('MP_ACCESS_TOKEN', 'MP_ACCESS_TOKEN');
        $this->add_field('ME_ACCESS_TOKEN', 'ME_ACCESS_TOKEN');
        // Adicione outros campos conforme necessário
    }

    private function add_field($id, $title) {
        add_settings_field(
            $id,
            $title,
            [$this, 'render_field'],
            'mp-subscriptions-settings',
            'mp_main_section',
            ['id' => $id]
        );
    }

    public function render_field($args) {
        $value = Plugin::get_option($args['id']);
        echo '<input type="text" name="mp_settings['.esc_attr($args['id']).']" value="'.esc_attr($value).'" class="regular-text" />';
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Mercado Pago Subscriptions</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('mp_subscriptions_group');
                do_settings_sections('mp-subscriptions-settings');
                submit_button('Salvar Configurações');
                ?>
            </form>
        </div>
        <?php
    }
}