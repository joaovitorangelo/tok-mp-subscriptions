<?php
/*
Plugin Name: Tok Mercado Pago Subscriptions
Description: Integração com Mercado Pago e Melhor Envio para a criação de planos de assinatura personalizados.
Version: 1.0.8
Author: Tok Digital
Text Domain: tok-mp-subscriptions
*/

defined( 'ABSPATH' ) || exit;

// --- Constantes ---
define( 'TOK_MPSUBS_PLUGIN_VERSION', '1.0.8' );

define( 'TOK_MPSUBS_PLUGIN_FILE', __FILE__ );

// define( 'TOK_PLUGIN_DIR', __DIR__ . '/' ); ← causando fatal error

define( 'TOK_MPSUBS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// --- Carrega o autoload do Composer ---
require_once __DIR__ . '/' . 'vendor/autoload.php';

// Inicializa o Sentry
\Sentry\init([
    'dsn' => 'https://44f3face5d262df00d0d1cf939d8d070@o4509958551830528.ingest.us.sentry.io/4509958553862144',
    'environment' => defined('WP_ENV') ? WP_ENV : 'production',
    'release' => 'tok-mp-subscriptions@1.0.0',
    'error_types' => E_ALL & ~E_NOTICE, // captura todos erros exceto notices
]);

// --- Teste de captura de erro no Sentry ---
// add_action('init', function () {
//     if (isset($_GET['test_sentry'])) {
//         \Sentry\captureException(new \Exception("Teste de erro enviado para Sentry!"));
//         error_log("Erro de teste enviado para Sentry");
//         wp_die("Erro de teste enviado para Sentry (verifique no dashboard do Sentry).");
//     }
// });

// --- Inicializa o plugin ---
add_action('plugins_loaded', function(){
    $plugin = new \Tok\MPSubscriptions\Plugin();
    $plugin->run();
});



