<?php

namespace Tok\MPSubscriptions\Core\Security;

defined('ABSPATH') || exit;

/**
 * Crypto
 * 
 * Criptografia e Descriptografia de credenciais
 */
class Crypto {
    private static $cipher = 'AES-256-CBC';

    public static function encrypt($data, $secret_key) {
        if (empty($data)) return '';
        $ivlen = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($data, self::$cipher, $secret_key, 0, $iv);
        return base64_encode($iv . $ciphertext);
    }

    public static function decrypt($data, $secret_key) {
        if (empty($data)) return '';
        $raw = base64_decode($data);
        $ivlen = openssl_cipher_iv_length(self::$cipher);
        $iv = substr($raw, 0, $ivlen);
        $ciphertext = substr($raw, $ivlen);
        return openssl_decrypt($ciphertext, self::$cipher, $secret_key, 0, $iv);
    }
}
