<?php 

namespace Tok\MPSubscriptions;

use Tok\MPSubscriptions\Admin;

use Tok\MPSubscriptions\Core\Services\MercadoPago;

use Tok\MPSubscriptions\Core\Services\MelhorEnvio;

use Tok\MPSubscriptions\Core\PostTypes\CustomPostType;

use Tok\MPSubscriptions\Core\PostTypes\Taxonomy;

use Tok\MPSubscriptions\Core\Security\Crypto;

use Tok\MPSubscriptions\Frontend\Forms;

use Tok\MPSubscriptions\Frontend\Ajax;

use Tok\MPSubscriptions\Frontend\Handlers\WebhookHandler;

defined('ABSPATH') || exit;

/**
 * Tok_Plugin
 * 
 * É a classe principal do plugin.
 * 
 * Responsável por:
 *  Inicializar todas as outras classes.
 *  Fornecer funções utilitárias, como get_option() para pegar configurações do banco.
 *  Decidir quais funcionalidades rodar no admin ou no frontend.
 * 
 * Basicamente, é o ponto de entrada do plugin.
 */
class Plugin {

    private $admin;
    private $mp;
    private $me;
    private $cpts = [];
    private static $secret_key; // <-- chave secreta

    public function __construct() {
        self::init_secret_key();

        $this->admin = new Admin();
        $this->mp    = new MercadoPago();
        $this->me    = new MelhorEnvio();

        // Registrar seus Custom Post Types
        $this->register_post_types();
    }

    public function run() {
        if(is_admin()){
            $this->admin->init();
        }

        $this->mp->init();
        $this->me->init();

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        if ( defined('ELEMENTOR_PRO_VERSION') ) {
            Forms::init();
        }

        Ajax::init();
        WebhookHandler::init(); 
    }

    /**
     * Importa JS e CSS do frontend
     */
    public function enqueue_scripts() {

        wp_enqueue_script(
            'tok-mp-subscriptions-main',
            TOK_MPSUBS_PLUGIN_URL . 'src/Frontend/assets/js/main.js',
            ['jquery'], // dependências
            '1.0.0',
            true // carregamento no footer
        );

        wp_localize_script('tok-mp-subscriptions-main', 'tok_mp_subscriptions_ajax_obj', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('tok_nonce'),
        ]);

        // wp_enqueue_style(
        //     'tok-mp-subscriptions-style',
        //     TOK_MPSUBS_PLUGIN_URL . 'src/Frontend/assets/css/main.css',
        //     [],
        //     '1.0.0'
        // );
    }

    /**
     * Registrar todos os Custom Post Types do plugin
     */
    private function register_post_types()
    {
        $this->cpts['plans'] = new \Tok\MPSubscriptions\Core\PostTypes\Plan();
        $this->cpts['subscriptions'] = new \Tok\MPSubscriptions\Core\PostTypes\Subscription();
    }

    /**
     * Função utilitária para pegar opções do plugin
     * Já descriptografa valores sensíveis
     */
    public static function get_option($key, $default = '') {
        // Apenas admins podem ler tokens
        if (!current_user_can('manage_options')) {
            return $default;
        }

        $options = get_option('mp_settings', []);
        if (!isset($options[$key]) || empty($options[$key])) {
            return $default;
        }

        return Crypto::decrypt($options[$key], self::$secret_key);
    }

    /**
     * Função utilitária para criptografar valores antes de salvar
     */
    public static function encrypt_value($value) {
        return Crypto::encrypt($value, self::$secret_key);
    }

    private static function init_secret_key() {
        // Tenta buscar a chave no banco
        $key = get_option('MP_PLUGIN_SECRET_KEY', '');

        if (empty($key)) {
            // Gera 32 bytes aleatórios e converte para hex (64 caracteres)
            $key = bin2hex(random_bytes(32));

            // Salva no banco de forma permanente
            update_option('MP_PLUGIN_SECRET_KEY', $key);
        }

        self::$secret_key = $key;
    }

    public function setup_webhook() {
        $this->mp->init();

        $webhook_url = home_url('/wp-json/tok-mp-subs/v1/webhook');

        try {
            $this->mp->configure_webhook($webhook_url);
            error_log("Webhook do Mercado Pago configurado com sucesso: " . $webhook_url);
        } catch (\Exception $e) {
            error_log("Erro ao configurar webhook do Mercado Pago: " . $e->getMessage());
        }
    }

}