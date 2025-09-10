<?php 

namespace Tok\MPSubscriptions;

use Tok\MPSubscriptions\Admin;

use Tok\MPSubscriptions\Core\PostTypes\CustomPostType;

use Tok\MPSubscriptions\Core\Security\Crypto;

use Tok\MPSubscriptions\Core\PostTypes\Taxonomy;

use Tok\MPSubscriptions\Core\Services\MercadoPago;

use Tok\MPSubscriptions\Core\Services\MelhorEnvio;

use Tok\MPSubscriptions\Frontend\Forms;

use Tok\MPSubscriptions\Frontend\Ajax;

use Tok\MPSubscriptions\Frontend\Handlers\WebhookHandler;

defined('ABSPATH') || exit;

/**
 * Plugin
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
    private static $secret_key;

    public function __construct() 
    {
        self::init_secret_key();

        $this->admin = new Admin();

        // Registrar Custom Post Types
        $this->register_post_types();
        
        $this->mp = new MercadoPago();
        $this->me = new MelhorEnvio();
    }

    /**
     * run
     * 
     * Roda o plugin.
     */
    public function run() 
    {
        if( is_admin() ) $this->admin->init();

        $this->mp->init();
        $this->me->init();

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        add_filter('script_loader_tag', [$this, 'add_type_module_attribute'], 10, 3);
        
        if ( defined('ELEMENTOR_PRO_VERSION') ) Forms::init();

        Ajax::init();

        WebhookHandler::init(); 
    }

    /**
     * Importa JS e CSS do frontend
     */
    public function enqueue_scripts() 
    {
        wp_enqueue_script(
            'tok-mp-subs-main', 
            TOK_MPSUBS_PLUGIN_URL . 'src/Frontend/assets/js/main.js', 
            ['jquery'], 
            '1.0.0', 
            true
        );

        wp_enqueue_script(
            'tok-mp-subs-push',
            TOK_MPSUBS_PLUGIN_URL . 'src/Frontend/assets/js/push.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('tok-mp-subs-main', 'tok_mp_subs', [
            'ajax_url'                      =>  admin_url('admin-ajax.php'),
            'nonce'                         =>  wp_create_nonce('tok_nonce'),
            'plugin_url'                    =>  TOK_MPSUBS_PLUGIN_URL,
            'firebase_service_account'      =>  json_decode(Plugin::get_option('FIREBASE_SERVICE_ACCOUNT'), true),
            'firebase_vapid_key'            =>  Plugin::get_option('FIREBASE_VAPIDKEY'),
            'firebase_api_key'              =>  Plugin::get_option('FIREBASE_API_KEY'),
            'firebase_auth_domain'          =>  Plugin::get_option('FIREBASE_AUTH_DOMAIN'),
            'firebase_project_id'           =>  Plugin::get_option('FIREBASE_PROJECT_ID'),
            'firebase_storage_bucket'       =>  Plugin::get_option('FIREBASE_STORAGE_BUCKET'),
            'firebase_messaging_sender_id'  =>  Plugin::get_option('FIREBASE_MESSAGING_SENDER_ID'),
            'firebase_app_id'               =>  Plugin::get_option('FIREBASE_APP_ID'),
            'firebase_measurement_id'       =>  Plugin::get_option('FIREBASE_MEASUREMENT_ID'),
        ]);
    }

    public function add_type_module_attribute($tag, $handle, $src) {
        $module_scripts = ['tok-mp-subs-main', 'tok-mp-subs-push'];
        if ( in_array( $handle, $module_scripts ) ) {
            $tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
        }
        return $tag;
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
    public static function get_option($key, $default = '', $allow_frontend = false) {
        // Apenas admins ou quando explicitamente permitido
        if (!current_user_can('manage_options') && !$allow_frontend) {
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
}