<?php

declare(strict_types=1);

namespace WipyAutos\AutomotiveSdk\Vehicle;

class PostType
{
    // This is the vehicle post type.
    public function __construct()
    {
        add_action('init', [$this, 'registerPostType']);
        add_action('init', [$this, 'registerTaxonomies']);
    }

    /**
     * Register post type for vehicles
     */
    public function registerPostType(): void
    {
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
            'show_in_graphql' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
            'menu_icon' => 'dashicons-car',
            'taxonomies' => array_column(Fields::getTaxonomies(), 'name'),
            'menu_position' => 55,
        ]);
    }

    /**
     * Register taxonomies for vehicles
     */
    public function registerTaxonomies(): void
    {
        foreach (Fields::getTaxonomies() as $taxonomy) {
            register_taxonomy($taxonomy['name'], 'vehicle', [
                'label' => __($taxonomy['label']),
                'rewrite' => ['slug' => $taxonomy['slug']],
                'hierarchical' => $taxonomy['hierarchical'],
                'show_in_rest' => true,
            ]);
        }
    }
}
