<?php

namespace Tok\MPSubscriptions\PostTypes;

defined('ABSPATH') || exit;

/**
 * Classe genérica para criar Custom Post Types
 */
class CustomPostType {
    
    protected string $post_type;
    protected array $labels;
    protected array $args;
    protected array $taxonomies = [];
    protected array $metaboxes = [];

    public function __construct(string $post_type, array $labels = [], array $args = []) {
        $this->post_type = $post_type;

        // Labels padrão
        $default_labels = [
            'name' => ucfirst($post_type),
            'singular_name' => ucfirst($post_type),
            'menu_name' => ucfirst($post_type),
            'add_new_item' => 'Add New ' . ucfirst($post_type),
            'edit_item' => 'Edit ' . ucfirst($post_type),
            'new_item' => 'New ' . ucfirst($post_type),
            'view_item' => 'View ' . ucfirst($post_type),
            'search_items' => 'Search ' . ucfirst($post_type),
            'not_found' => 'No ' . strtolower($post_type) . ' found',
            'not_found_in_trash' => 'No ' . strtolower($post_type) . ' found in Trash',
        ];

        $this->labels = array_merge($default_labels, $labels);

        // Args padrão
        $default_args = [
            'labels' => $this->labels,
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor', 'custom-fields'],
            'show_in_rest' => true,
        ];

        $this->args = array_merge($default_args, $args);

        add_action('init', [$this, 'register_post_type']);
        add_action('add_meta_boxes', [$this, 'register_metaboxes']);
    }

    /**
     * Registrar CPT
     */
    public function register_post_type() {
        register_post_type($this->post_type, $this->args);

        // Registrar taxonomias associadas
        foreach ($this->taxonomies as $taxonomy) {
            $taxonomy->register($this->post_type);
        }
    }

    /**
     * Registrar metaboxes
     */
    public function register_metaboxes() {
        foreach ($this->metaboxes as $metabox) {
            add_meta_box(
                $metabox['id'],
                $metabox['title'],
                $metabox['callback'],
                $this->post_type,
                $metabox['context'] ?? 'normal',
                $metabox['priority'] ?? 'default'
            );
        }
    }

    /**
     * Adicionar taxonomia
     */
    public function add_taxonomy(Taxonomy $taxonomy) {
        $this->taxonomies[] = $taxonomy;
    }

    /**
     * Adicionar metabox
     * 
     * @param string $id ID do metabox
     * @param string $title Título do metabox
     * @param callable $callback Função de callback que renderiza o conteúdo
     * @param string $context normal, side, advanced
     * @param string $priority default, high, low
     */
    public function add_metabox(string $id, string $title, callable $callback, string $context = 'normal', string $priority = 'high') {
        $this->metaboxes[] = compact('id', 'title', 'callback', 'context', 'priority');

        // Hook para adicionar o metabox de forma “tradicional”
        add_action('add_meta_boxes', function() use ($id, $title, $callback, $context, $priority) {
            add_meta_box($id, $title, $callback, $this->post_type, $context, $priority);
        });
    }

}
