<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\ImportProfile;

class PostType
{
    public function __construct()
    {
        add_action('init', [$this, 'register']);

        // Register meta box
        new Meta();
    }

    public function register(): void
    {
        $labels = [
            'name' => __('Import Profiles', 'wp-autos'),
            'singular_name' => __('Import Profile', 'wp-autos'),
            'add_new' => __('Add New'),
            'add_new_item' => __('Add New Import Profile'),
            'edit_item' => __('Edit Import Profile'),
            'new_item' => __('New Import Profile'),
            'view_item' => __('View Import Profile'),
            'view_items' => __('View Import Profiles'),
            'search_items' => __('Search Import Profiles'),
            'not_found' => __('No Import Profiles found'),
            'not_found_in_trash' => __('No Import Profiles found in Trash'),
            'all_items' => __('All Import Profiles'),
            'archives' => __('Import Profile Archives'),
            'attributes' => __('Import Profile Attributes'),
            'insert_into_item' => __('Insert into Import Profile'),
            'uploaded_to_this_item' => __('Uploaded to this Import Profile'),
            'featured_image' => __('Featured Image'),
            'set_featured_image' => __('Set featured image'),
            'remove_featured_image' => __('Remove featured image'),
            'use_featured_image' => __('Use as featured image'),
            'filter_items_list' => __('Filter Import Profiles list'),
            'items_list_navigation' => __('Import Profiles list navigation'),
            'items_list' => __('Import Profiles list'),
            'item_published' => __('Import Profile published.'),
            'item_published_privately' => __('Import Profile published privately.'),
            'item_reverted_to_draft' => __('Import Profile reverted to draft.'),
            'item_scheduled' => __('Import Profile scheduled.'),
            'item_updated' => __('Import Profile updated.'),
        ];

        $storage = [
            'label' => __('Import Profile', 'wp-autos'),
            'public' => false,  // Make it non-public so it won't appear in the admin menu
            'show_ui' => false,  // No UI for managing posts directly
            'supports' => ['title'],
            'capability_type' => 'post',
            'capabilities' => [
                'create_posts' => false,  // Users cannot create import profiles manually
            ],
            'map_meta_cap' => true,
            'rewrite' => false,
            'query_var' => false,
        ];

        $args = [
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'import-profiles'],
            'show_in_rest' => true,
            'supports' => ['title'],
            'menu_icon' => 'dashicons-upload',
            'show_in_menu' => ASDK,
        ];

        register_post_type('import-profile', $args);
    }
}
