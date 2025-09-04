<?php

namespace Tok\MPSubscriptions\Core\PostTypes;

defined('ABSPATH') || exit;

/**
 * Plan
 * 
 * CPT Planos de Assinatura.
 */
class Plan extends CustomPostType
{
    public function __construct()
    {
        parent::__construct('plan', [
            'name'                  =>  'Planos',
            'singular_name'         =>  'Plano',
        ], [
            'public'                =>  true,
            'show_ui'               =>  true,
            'show_in_menu'          =>  true,
            'show_in_rest'          =>  false,
            'rewrite'               =>  ['slug' => 'plan'],
            'capability_type'       =>  'post',
            'has_archive'           =>  true,
            'hierarchical'          =>  false,
            'menu_position'         =>  null,
            'menu_icon'             =>  'dashicons-tag',
            'supports'              =>  ['title', 'editor', 'excerpt', 'thumbnail'],
            'publicly_queryable'    =>  true,
            'query_var'             =>  true,
        ]);

        // Metabox
        $this->add_metabox('fields_plan', 'Informações do Plano', [$this, 'render_metabox']);

        // Salvamento dos metadados
        add_action('save_post', [$this, 'save_metabox']);
    }

    public function render_metabox($post)
    {
        wp_nonce_field('save_plan_meta', 'plan_meta_nonce');
        ?>
        <p><label for="plan_price">Preço:</label></p>
        <input type="text" name="plan_price" id="plan_price"
            value="<?php echo esc_attr(get_post_meta($post->ID,'_plan_price',true)); ?>"
            style="width:100%;">

        <p><label for="plan_unitary_value">Preço Unitário:</label></p>
        <input type="text" name="plan_unitary_value" id="plan_unitary_value"
            value="<?php echo esc_attr(get_post_meta($post->ID,'_plan_unitary_value',true)); ?>"
            style="width:100%;">

        <p><label for="plan_quantity">Quantidade:</label></p>
        <input type="text" name="plan_quantity" id="plan_quantity"
            value="<?php echo esc_attr(get_post_meta($post->ID,'_plan_quantity',true)); ?>"
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

    public function save_metabox($post_id)
    {
        // Verificação do nonce
        if (!isset($_POST['plan_meta_nonce']) || !wp_verify_nonce($_POST['plan_meta_nonce'], 'save_plan_meta')) {
            return;
        }

        // Evitar autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Verificar permissão
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = [
            'plan_title', 
            'plan_price', 
            'plan_unitary_value', 
            'plan_quantity', 
            'plan_width', 
            'plan_height', 
            'plan_length', 
            'plan_weight'
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
}
