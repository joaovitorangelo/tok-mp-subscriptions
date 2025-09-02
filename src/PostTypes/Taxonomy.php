<?php

namespace Tok\MPSubscriptions\PostTypes;

defined('ABSPATH') || exit;

/**
 * Classe genérica para criar taxonomias
 */
class Taxonomy {
    protected string $taxonomy;
    protected array $labels;
    protected array $args;

    public function __construct(string $taxonomy, array $labels = [], array $args = []) {
        $this->taxonomy = $taxonomy;

        $default_labels = [
            'name' => ucfirst($taxonomy),
            'singular_name' => ucfirst($taxonomy),
            'search_items' => 'Search ' . ucfirst($taxonomy),
            'all_items' => 'All ' . ucfirst($taxonomy),
            'edit_item' => 'Edit ' . ucfirst($taxonomy),
            'update_item' => 'Update ' . ucfirst($taxonomy),
            'add_new_item' => 'Add New ' . ucfirst($taxonomy),
            'new_item_name' => 'New ' . ucfirst($taxonomy),
        ];

        $this->labels = array_merge($default_labels, $labels);

        $default_args = [
            'labels' => $this->labels,
            'public' => true,
            'hierarchical' => true,
            'show_in_rest' => true,
        ];

        $this->args = array_merge($default_args, $args);
    }

    public function register(string $post_type) {
        register_taxonomy($this->taxonomy, [$post_type], $this->args);
    }
}
