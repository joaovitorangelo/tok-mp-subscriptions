<?php

namespace Tok\MPSubscriptions\Infrastructure;

use Sentry;

defined('ABSPATH') || exit;

class ErrorHandler
{
    public static function report(\Throwable $e, array $context = [])
    {
        if (class_exists('\Sentry\State\Hub')) {
            Sentry\captureException($e);
        }

        // Log local como fallback
        error_log("[Tok MPSubscriptions] " . $e->getMessage());

        // Opcional: email para admin
        // if (!empty($context['email_admin'])) {
        //     wp_mail(
        //         get_option('admin_email'),
        //         'Erro no Tok MP Subscriptions',
        //         $e->getMessage()
        //     );
        // }
    }

    public static function reportMessage(string $message, array $context = [])
    {
        if (class_exists('\Sentry\State\Hub')) {
            Sentry\captureMessage($message);
        }
        error_log("[Tok MPSubscriptions] " . $message);
    }
}
