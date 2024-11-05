<?php

declare(strict_types=1);

namespace WipyAutos\AutomotiveSdk\Post;

use WipyAutos\AutomotiveSdk\Post\MetaFields;

class PostType
{
    protected bool $has_archive = true;
    protected string $show_in_menu = ASDK;
    protected bool $show_rest = true;
    protected bool $show_in_graphql = true;
    protected bool $post_public = true;
    protected array $post_supports = ['title', 'editor'];
    protected string $post_type = '';
    protected array $post_labels = [];

    protected string $label_singular_name = '';
    protected string $label_plural_name = '';

    protected array $post_meta_fields = [];
    protected array $post_taxonomies = [];

    public function __construct(string $post_type = '', array $post_meta_fields = [], array $post_taxonomies = [])
    {
        if (!empty($post_type)) {
            $this->post_type = $post_type;
        }

        if (empty($this->post_type)) {
            throw new \Exception('Post type is required.');
        }

        if (!empty($post_meta_fields)) {
            $this->post_meta_fields = $post_meta_fields;
        } elseif (method_exists($this, 'buildMetas')) {
            $this->post_meta_fields = $this->buildMetas();
        }

        if (!empty($post_taxonomies)) {
            $this->post_taxonomies = $post_taxonomies;
        }
    }

    public function register(): void
    {
        $this->registerHooks();
    }

    /**
     * Register hooks
     */
    public function registerHooks(): void
    {
        add_action('init', [$this, 'registerPostType']);

        // Initialize MetaFields for meta handling
        if (!empty($this->post_meta_fields)) {
            $metaFields = new MetaFields($this->post_type, $this->post_meta_fields);
        }

        if (!empty($this->post_taxonomies)) {
            add_action('init', [$this, 'registerTaxonomies']);
        }

        add_filter('manage_' . $this->post_type . '_posts_columns', [$this, 'addCustomColumns']);
        add_action('manage_' . $this->post_type . '_posts_custom_column', [$this, 'renderCustomColumns'], 10, 2);
    }

    /**
     * Get labels for post type
     */
    public function getLabels(): array
    {
        $name = $this->post_name ?? ucfirst($this->post_type);

        if (empty($this->label_singular_name)) {
            $this->label_singular_name = ucfirst($this->post_type);
        }

        if (empty($this->label_plural_name)) {
            $this->label_plural_name = ucfirst($this->post_type) . 's';
        }

        if (empty($this->post_labels)) {
            $this->post_labels = [
                'name' => __($name),
                'singular_name' => __($this->label_singular_name),
                'add_new' => __('Add New'),
                'add_new_item' => __('Add New ' . $this->label_singular_name),
                'edit_item' => __('Edit ' . $this->label_singular_name),
                'new_item' => __('New ' . $this->label_singular_name),
                'view_item' => __('View ' . $this->label_singular_name),
                'view_items' => __('View ' . $this->label_plural_name),
                'search_items' => __('Search ' . $this->label_plural_name),
                'not_found' => __('No ' . $this->label_plural_name . ' found'),
                'not_found_in_trash' => __('No ' . $this->label_plural_name . ' found in Trash'),
                'all_items' => __($this->label_plural_name),
                'archives' => __($this->label_singular_name . ' Archives'),
                'attributes' => __($this->label_singular_name . ' Attributes'),
                'insert_into_item' => __('Insert into ' . $this->label_singular_name),
                'uploaded_to_this_item' => __('Uploaded to this ' . $this->label_singular_name),
                'featured_image' => __('Featured Image'),
                'set_featured_image' => __('Set featured image'),
                'remove_featured_image' => __('Remove featured image'),
                'use_featured_image' => __('Use as featured image'),
                'filter_items_list' => __('Filter ' . $this->label_plural_name . ' list'),
                'items_list_navigation' => __($this->label_plural_name . ' list navigation'),
                'items_list' => __($this->label_plural_name . ' list'),
                'item_published' => __($this->label_singular_name . ' published.'),
                'item_published_privately' => __($this->label_singular_name . ' published privately.'),
                'item_reverted_to_draft' => __($this->label_singular_name . ' reverted to draft.'),
                'item_scheduled' => __($this->label_singular_name . ' scheduled.'),
                'item_updated' => __($this->label_singular_name . ' updated.'),
            ];
        }

        return $this->post_labels;
    }

    /**
     * Register custom post type
     */
    public function registerPostType(): void
    {
        register_post_type($this->post_type, [
            'labels' => $this->getLabels(),
            'public' => $this->post_public,
            'has_archive' => $this->has_archive,
            'supports' => $this->post_supports,
            'show_in_menu' => $this->show_in_menu,
            'show_in_graphql' => $this->show_in_graphql,
        ]);
    }

    /**
     * Register taxonomies if any
     */
    public function registerTaxonomies(): void
    {
        foreach ($this->post_taxonomies as $taxonomy) {
            register_taxonomy($taxonomy['name'], $this->post_type, [
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
        // We need to loop through fields inside sections.
        foreach ($this->post_meta_fields as $section) {
            foreach ($section['fields'] as $field) {
                $columns[$field['name']] = $field['label'];
            }
        }
        return $columns;
    }

    /**
     * Render custom columns in admin list view
     */
    public function renderCustomColumns(string $column, int $post_id): void
    {
        foreach ($this->post_meta_fields as $section) {
            foreach ($section['fields'] as $field) {
                if ($column === $field['name']) {
                    $value = get_post_meta($post_id, $field['name'], true);
                    if (is_array($value)) {
                        echo esc_html(implode(', ', $value));
                    } else {
                        echo esc_html($value);
                    }
                }
            }
        }
    }

    /**
     * Custom method to fetch posts for multi-select dropdowns.
     */
    protected function getPosts(string $post_type): array
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

    public function setMetaFields(array $post_meta_fields): self
    {
        $this->post_meta_fields = $post_meta_fields;

        return $this;
    }

    public function getMetaFields(): array
    {
        return $this->post_meta_fields;
    }

    public function setTaxonomies(array $post_taxonomies): self
    {
        $this->post_taxonomies = $post_taxonomies;

        return $this;
    }

    public function setPostType(string $post_type): self
    {
        $this->post_type = $post_type;

        return $this;
    }
}
