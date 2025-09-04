<?php
// Carrega o autoload do Composer
require __DIR__ . '/../vendor/autoload.php';

// Se precisar inicializar funções do WordPress
if ( file_exists(ABSPATH . 'wp-load.php') ) {
    require_once ABSPATH . 'wp-load.php';
}
