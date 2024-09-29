<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Post;

class PostType
{
    protected string $post_type;
    protected array $post_taxonomies = [];

    public function __construct(string $post_type, array $post_taxonomies = [])
    {
        $this->post_type = $post_type;
        $this->post_taxonomies = $post_taxonomies;
        $this->registerHooks();
    }

    /**
     * Register hooks
     */
    public function registerHooks(): void
    {
        add_action('init', [$this, 'registerPostType']);
        add_action('init', [$this, 'registerTaxonomies']);
    }

    /**
     * Register custom post type
     */
    public function registerPostType(): void
    {
        register_post_type($this->post_type, [
            'label' => ucfirst($this->post_type),
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor'],
            'show_in_menu' => 'rooftop',
        ]);
    }

    /**
     * Register taxonomies
     */
    public function registerTaxonomies(): void
    {
        foreach ($this->post_taxonomies as $taxonomy) {
            register_taxonomy($taxonomy['id'], $this->post_type, [
                'label' => $taxonomy['label'],
                'hierarchical' => true,
                'show_ui' => true,
                'show_admin_column' => true,
            ]);
        }
    }
}
