<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Post;

use WpAutos\AutomotiveSdk\Post\MetaFields;

class PostType
{
    protected bool $has_archive = true;
    protected string $show_in_menu = ASDK;
    protected bool $post_public = true;
    protected array $post_supports = ['title', 'editor'];
    protected string $post_type = '';

    protected array $post_meta_fields = [];
    protected array $post_taxonomies = [];

    public function __construct(string $post_type, array $post_meta_fields = [], array $post_taxonomies = [])
    {
        $this->post_type = $post_type;
        $this->post_meta_fields = $post_meta_fields;
        $this->post_taxonomies = $post_taxonomies;

        // Call MetaFields for meta handling
        $metaFields = new MetaFields($this->post_type, $this->post_meta_fields);

        $this->registerHooks();
    }
    
    /**
     * Register hooks
     */
    public function registerHooks(): void
    {
        add_action('init', [$this, 'registerPostType']);
        add_action('init', [$this, 'registerTaxonomies']);

        add_filter('manage_' . $this->post_type . '_posts_columns', [$this, 'addCustomColumns']);
        add_action('manage_' . $this->post_type . '_posts_custom_column', [$this, 'renderCustomColumns'], 10, 2);
    }

    /**
     * Register custom post type
     */
    public function registerPostType(): void
    {
        register_post_type($this->post_type, [
            'label' => ucfirst($this->post_type),
            'public' => $this->post_public,
            'has_archive' => $this->has_archive,
            'supports' => $this->post_supports,
            'show_in_menu' => $this->show_in_menu,
        ]);
    }

    /**
     * Register taxonomies if any
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

    /**
     * Add custom columns in admin list view
     */
    public function addCustomColumns(array $columns): array
    {
        foreach ($this->post_meta_fields as $field) {
            $columns[$field['id']] = $field['label'];
        }
        return $columns;
    }

    /**
     * Render custom columns in admin list view
     */
    public function renderCustomColumns(string $column, int $post_id): void
    {
        foreach ($this->post_meta_fields as $field) {
            if ($column === $field['id']) {
                $value = get_post_meta($post_id, $field['id'], true);
                if (is_array($value)) {
                    echo esc_html(implode(', ', $value));
                } else {
                    echo esc_html($value);
                }
            }
        }
    }

    public function getPostMetaFields(): array
    {
        return $this->post_meta_fields;
    }

    /**
     * Custom method to fetch Service posts for the Services dropdown.
     */
    protected function getPosts($post_type): array
    {
        $posts = get_posts([
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);

        $options = [];
        foreach ($posts as $post) {
            $options[$post->ID] = $post->post_title;
        }

        return $options;
    }
}
