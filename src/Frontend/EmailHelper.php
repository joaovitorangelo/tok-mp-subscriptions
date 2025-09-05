<?php

namespace Tok\MPSubscriptions\Frontend;

class EmailHelper {

    public static function get_template(string $template_name, array $vars = []): string {
        $template_file = __DIR__ . "/Emails/{$template_name}.php";

        if (!file_exists($template_file)) {
            return '';
        }

        extract($vars);

        ob_start();
        include $template_file;
        return ob_get_clean();
    }
}
