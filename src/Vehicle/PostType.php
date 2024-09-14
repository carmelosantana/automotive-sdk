<?php

declare(strict_types=1);

namespace WpAutos\Vehicles\Vehicle;

class PostType
{
    // This is the vehicle post type.
    public function __construct()
    {
        add_action('init', [$this, 'registerPostType']);
        add_action('init', [$this, 'registerTaxonomies']);
    }

    // Register post type
    public function registerPostType()
    {
        // labels to change all "Post(s)" to "Vehicle(s)"
        $labels = [
            'name' => __('Vehicles'),
            'singular_name' => __('Vehicle'),
            'add_new' => __('Add New'),
            'add_new_item' => __('Add New Vehicle'),
            'edit_item' => __('Edit Vehicle'),
            'new_item' => __('New Vehicle'),
            'view_item' => __('View Vehicle'),
            'view_items' => __('View Vehicles'),
            'search_items' => __('Search Vehicles'),
            'not_found' => __('No Vehicles found'),
            'not_found_in_trash' => __('No Vehicles found in Trash'),
            'all_items' => __('All Vehicles'),
            'archives' => __('Vehicle Archives'),
            'attributes' => __('Vehicle Attributes'),
            'insert_into_item' => __('Insert into Vehicle'),
            'uploaded_to_this_item' => __('Uploaded to this Vehicle'),
            'featured_image' => __('Featured Image'),
            'set_featured_image' => __('Set featured image'),
            'remove_featured_image' => __('Remove featured image'),
            'use_featured_image' => __('Use as featured image'),
            'filter_items_list' => __('Filter Vehicles list'),
            'items_list_navigation' => __('Vehicles list navigation'),
            'items_list' => __('Vehicles list'),
            'item_published' => __('Vehicle published.'),
            'item_published_privately' => __('Vehicle published privately.'),
            'item_reverted_to_draft' => __('Vehicle reverted to draft.'),
            'item_scheduled' => __('Vehicle scheduled.'),
            'item_updated' => __('Vehicle updated.'),
        ];

        register_post_type('vehicle', [
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'vehicles'],
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
            'menu_icon' => 'dashicons-car',
            'taxonomies' => ['make', 'model', 'trim', 'year', 'special'],
        ]);
    }

    // Register taxonomies
    public function registerTaxonomies()
    {
        // Add custom taxonomy
        register_taxonomy('make', 'vehicle', [
            'label' => __('Make'),
            'rewrite' => ['slug' => 'makes'],
            'hierarchical' => true,
        ]);

        // Add custom taxonomy
        register_taxonomy('model', 'vehicle', [
            'label' => __('Model'),
            'rewrite' => ['slug' => 'models'],
            'hierarchical' => true,
        ]);

        // Trim
        register_taxonomy('trim', 'vehicle', [
            'label' => __('Trim'),
            'rewrite' => ['slug' => 'trims'],
            'hierarchical' => true,
        ]);

        // Add custom taxonomy
        register_taxonomy('year', 'vehicle', [
            'label' => __('Year'),
            'rewrite' => ['slug' => 'years'],
            'hierarchical' => true,
        ]);
    }
}
