<?php 

namespace Tok\MPSubscriptions;

use Tok\MPSubscriptions\Admin;

use Tok\MPSubscriptions\MercadoPago;

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

    public function __construct() {
        $this->admin = new Admin();
        $this->mp    = new MercadoPago();
    }

    public function run() {
        if(is_admin()){
            $this->admin->init();
        }

        $this->mp->init();
    }

    public static function get_option($key, $default = '') {
        $options = get_option('mp_settings', []);
        return $options[$key] ?? $default;
    }
}