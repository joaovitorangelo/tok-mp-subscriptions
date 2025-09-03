<?php

namespace Tok\MPSubscriptions\Frontend;

use Tok\MPSubscriptions\Frontend\Handlers\SubscriptionsPlanForm;

class Forms {
    
    public static function init() {
        add_action('elementor_pro/forms/new_record', [self::class, 'handle_form'], 10, 2);
    }

    public static function handle_form($record, $handler) {
        $form_name = $record->get_form_settings('form_name');
        $raw_fields = $record->get('fields');
        $fields = [];
        foreach ($raw_fields as $id => $field) {
            $fields[$id] = [
                'id'    => $id,
                'title' => $field['title'],
                'value' => $field['value'],
            ];
        }

        switch ($form_name) {

            case 'tok_mp_subscriptions_plan_form':
                $return = SubscriptionsPlanForm::process($fields);
                break;

            default:
                $return = null;
                break;
        }

        $handler->data['customer_mail'] = $return;
    }
}
