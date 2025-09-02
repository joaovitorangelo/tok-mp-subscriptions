<?php

namespace Tok\MPSubscriptions\PostTypes;

defined('ABSPATH') || exit;

class Plan extends CustomPostType
{
    public function __construct()
    {
        parent::__construct('plans', [
            'name'          => 'Planos',
            'singular_name' => 'Plano',
        ], [
            'menu_icon' => 'dashicons-tag',
            'supports'  => ['title'],
        ]);

        // Metabox
        $this->add_metabox('fields_plan', 'Informações do Plano', [$this, 'render_metabox']);
    }

    public function render_metabox($post)
    {
        ?>
        <p><label for="plan_title">Título do Plano:</label></p>
        <input type="text" name="plan_title" id="plan_title"
            value="<?php echo esc_attr(get_post_meta($post->ID,'_plan_code',true)); ?>"
            style="width:100%;">
        
        <p><label for="plan_value">Valor:</label></p>
        <input type="text" name="plan_value" id="plan_value"
            value="<?php echo esc_attr(get_post_meta($post->ID,'_plan_value',true)); ?>"
            style="width:100%;">

        <p><label for="plan_width">Largura:</label></p>
        <input type="text" name="plan_width" id="plan_width"
            value="<?php echo esc_attr(get_post_meta($post->ID,'_plan_width',true)); ?>"
            style="width:100%;">

        <p><label for="plan_height">Altura:</label></p>
        <input type="text" name="plan_height" id="plan_height"
            value="<?php echo esc_attr(get_post_meta($post->ID,'_plan_height',true)); ?>"
            style="width:100%;">

        <p><label for="plan_length">Comprimento:</label></p>
        <input type="text" name="plan_length" id="plan_length"
            value="<?php echo esc_attr(get_post_meta($post->ID,'_plan_length',true)); ?>"
            style="width:100%;">

        <p><label for="plan_weight">Peso:</label></p>
        <input type="text" name="plan_weight" id="plan_weight"
            value="<?php echo esc_attr(get_post_meta($post->ID,'_plan_weight',true)); ?>"
            style="width:100%;">
        <?php
    }
}
