<?php
/*
Plugin Name: Tok Mercado Pago Subscriptions
Description: Integração com Mercado Pago e Melhor Envio para a criação de planos de assinatura personalizados.
Version: 1.0.3
Author: Tok Digital
Text Domain: tok-mp-subscriptions
*/

defined( 'ABSPATH' ) || exit;

// --- Constantes ---
define( 'TOK_PLUGIN_VERSION', '1.0.3' );

define( 'TOK_PLUGIN_FILE', __FILE__ );

// define( 'TOK_PLUGIN_DIR', __DIR__ . '/' ); ← causando fatal error

define( 'TOK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// --- Carrega o autoload do Composer ---
require_once __DIR__ . '/' . 'vendor/autoload.php';

// --- Inicializa o plugin ---
add_action('plugins_loaded', function(){
    $plugin = new \Tok\MPSubscriptions\Plugin();
    $plugin->run();
});