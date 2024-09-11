<?php

declare(strict_types=1);

namespace CarmeloSantana\VinImporter;

use Brick\Money\Money;
use Mustache_Engine;

class Vehicle
{
    // This is the vehicle post type.
    public function __construct()
    {
        add_action('init', [$this, 'registerPostType']);
        add_action('init', [$this, 'registerTaxonomies']);
        add_action('add_meta_boxes', [$this, 'metaboxRegister']);
        add_action('save_post', [$this, 'metaboxSave']);

        // add custom sorted columns
        add_filter('manage_vehicle_posts_columns', [$this, 'addColumns']);
        add_action('manage_vehicle_posts_custom_column', [$this, 'addColumnsContent'], 10, 2);
        add_filter('manage_edit-vehicle_sortable_columns', [$this, 'addSortableColumns']);
        add_action('pre_get_posts', [$this, 'sortColumns']);

        // add meta search
        add_filter('posts_join', [$this, 'vinSearchJoin']);
        add_filter('posts_where', [$this, 'vinSearchWhere']);

        // render mustache template for the_content and any other area on screen
        add_filter('the_content', [$this, 'renderContent']);
    }

    // Render content
    public function renderContent($content)
    {
        global $post;

        if ($post->post_type !== 'vehicle') {
            return $content;
        }

        $fields = self::fields();
        $meta = get_post_meta($post->ID);

        $data = [];
        foreach ($fields as $section => $field) {
            foreach ($field['fields'] as $field) {
                $data[$field['name']] = $meta[$field['name']][0] ?? '';
            }
        }

        // get taxonomy terms
        $data['make'] = get_the_term_list($post->ID, 'make', '', ', ', '');
        $data['model'] = get_the_term_list($post->ID, 'model', '', ', ', '');
        $data['year'] = get_the_term_list($post->ID, 'year', '', ', ', '');

        $m = new Mustache_Engine(['entity_flags' => ENT_QUOTES]);
        $content = $m->render($content, $data);

        return $content;
    }

    // Add custom columns
    public function addColumns($columns)
    {
        $columns['make'] = 'Make';
        $columns['model'] = 'Model';
        $columns['year'] = 'Year';
        $columns['price'] = 'Price';
        $columns['sale_price'] = 'Sale Price';
        $columns['vin'] = 'VIN';

        // remove title
        unset($columns['title']);
        unset($columns['date']);
        return $columns;
    }

    // Can take a key and format the value to an acceptable output
    // Example, vauto pricing comes in as USD23450 but we need to display $23,450
    public function formatValue($key, $value)
    {
        switch ($key) {
            case 'price':
            case 'sale_price':
                if (!is_numeric($value) and !empty($value)) {
                    // remove all numeric characters and add to $currency
                    $currency = preg_replace('/[0-9]/', '', $value);

                    switch ($currency) {
                        default:
                            $currency = 'USD';
                            break;
                    }
                    $value = preg_replace('/[^0-9]/', '', $value);

                    // $money = Money::of($value, $currency);
                    // $money = $money->formatTo('en_US');
                    return $money;
                }
            default:
                return $value;
        }
    }

    // Add content to custom columns
    public function addColumnsContent($column, $post_id)
    {
        switch ($column) {
                // taxonomies, not meta
            case 'make':
                echo get_the_term_list($post_id, 'make', '', ', ', '');
                break;
            case 'model':
                echo get_the_term_list($post_id, 'model', '', ', ', '');
                break;
            case 'year':
                echo get_the_term_list($post_id, 'year', '', ', ', '');
                break;
                # meta
            case 'price':
                echo $this->formatValue('price', get_post_meta($post_id, 'price', true));
                break;
            case 'sale_price':
                echo $this->formatValue('sale_price', get_post_meta($post_id, 'sale_price', true));
                break;
            case 'vin':
                echo get_post_meta($post_id, 'vin', true);
                break;
        }
    }

    // Add vehicle meta search to edit.php search
    public function addMetaSearch($query)
    {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        $meta_query = $query->get('meta_query');

        if (isset($_GET['s'])) {
            $search = sanitize_text_field($_GET['s']);
            $meta_query[] = [
                'relation' => 'OR',
                [
                    'key' => 'vin',
                    'value' => $search,
                    'compare' => 'LIKE',
                ],
            ];
        }

        $query->set('meta_query', $meta_query);
    }

    // Add sortable columns
    public function addSortableColumns($columns)
    {
        $columns['make'] = 'make';
        $columns['model'] = 'model';
        $columns['year'] = 'year';
        $columns['price'] = 'price';
        $columns['sale_price'] = 'sale_price';
        $columns['vin'] = 'vin';
        return $columns;
    }

    // Sort columns
    public function sortColumns($query)
    {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        $orderby = $query->get('orderby');

        if ('make' == $orderby) {
            $query->set('orderby', 'make');
        }

        if ('model' == $orderby) {
            $query->set('orderby', 'model');
        }

        if ('year' == $orderby) {
            $query->set('orderby', 'year');
        }

        if ('price' == $orderby) {
            $query->set('meta_key', 'price');
            $query->set('orderby', 'meta_value_num');
        }

        if ('sale_price' == $orderby) {
            $query->set('meta_key', 'sale_price');
            $query->set('orderby', 'meta_value_num');
        }

        if ('vin' == $orderby) {
            $query->set('meta_key', 'vin');
            $query->set('orderby', 'meta_value');
        }

        if ('state_of_vehicle' == $orderby) {
            $query->set('meta_key', 'state_of_vehicle');
            $query->set('orderby', 'meta_value');
        }
    }

    // Register post type
    public function registerPostType()
    {
        // labels to change all "Post(s)" to "Vehicle(s)"
        $labels = [
            'name' => __('Vehicles'),
            'singular_name' => __('Vehicle'),
            'add_new' => __('Add Vehicle'),
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

    // display custom meta boxes for custom post type item
    public function metaboxRegister()
    {
        // add_meta_box(
        //     'vehicle_meta_box',
        //     'Vehicle Details',
        //     [$this, 'metaboxDisplay'],
        //     $this->slug,
        //     'advanced',
        //     'high'
        // );

        // add meta box per section
        $fields = self::fields();
        foreach ($fields as $section => $field) {
            add_meta_box(
                'vehicle_meta_box_' . $section,
                $field['description'],
                // use a display function that takes the section as an argument
                function ($post) use ($section) {
                    $fields = self::fields()[$section]['fields'];
                    $meta = get_post_meta($post->ID);

                    foreach ($fields as $field) {
                        $value = $meta[$field['name']][0] ?? '';
                        $label = $field['label'];
                        $type = $field['type'];
                        $name = $field['name'];

                        // label on the left, input on the right
                        echo '<div style="display: flex; margin-bottom: 1rem;">';
                        echo '<label style="width: 200px;">' . $label . '</label>';

                        // use switch for different input types
                        switch ($type) {
                            case 'text':
                                echo '<input type="text" name="' . $name . '" value="' . $value . '" style="width: 100%;">';
                                break;
                            case 'number':
                                echo '<input type="number" name="' . $name . '" value="' . $value . '" style="width: 100%;">';
                                break;
                            case 'textarea':
                                echo '<textarea name="' . $name . '" style="width: 100%;">' . $value . '</textarea>';
                                break;
                        }

                        echo '</div>';
                    }
                },
                'vehicle',
                'advanced',
                'high',
                $field
            );
        }
    }

    // save custom meta box
    public function metaboxSave($post_id)
    {
        $fields = self::fields();

        foreach ($fields as $section => $field) {
            foreach ($field['fields'] as $field) {
                $name = $field['name'];
                if (isset($_POST[$name])) {
                    update_post_meta($post_id, $name, sanitize_text_field($_POST[$name]));
                }
            }
        }
    }

    // add support to search for vin meta in the search box of edit.php
    public function vinSearchJoin($join)
    {
        global $wpdb;

        if (is_search()) {
            $join .= " INNER JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id ";
        }

        return $join;
    }

    // add support to search for vin meta in the search box of edit.php
    public function vinSearchWhere($where)
    {
        global $wpdb;

        if (is_search()) {
            $search = sanitize_text_field($_GET['s']);
            $where .= " OR ( $wpdb->postmeta.meta_key = 'vin' AND $wpdb->postmeta.meta_value LIKE '%$search%' ) ";
        }

        return $where;
    }

    public static function fields()
    {
        return [
            'specifications' => [
                'description' => __('Details for this particular vehicle.', 'alpaca-bot'),
                'fields' => [
                    [
                        'name' => 'vin',
                        'type' => 'text',
                        'label' => 'VIN'
                    ],
                    [
                        'name' => 'stock_number',
                        'type' => 'text',
                        'label' => 'Stock Number'
                    ],
                    [
                        'name' => 'year',
                        'type' => 'text',
                        'label' => 'Year'
                    ],
                    [
                        'name' => 'make',
                        'type' => 'text',
                        'label' => 'Make'
                    ],
                    [
                        'name' => 'model',
                        'type' => 'text',
                        'label' => 'Model'
                    ],
                    [
                        'name' => 'trim',
                        'type' => 'text',
                        'label' => 'Trim'
                    ],
                    [
                        'name' => 'body',
                        'type' => 'text',
                        'label' => 'Body'
                    ],
                    [
                        'name' => 'mileage',
                        'type' => 'number',
                        'label' => 'Mileage'
                    ],
                    [
                        'name' => 'transmission',
                        'type' => 'text',
                        'label' => 'Transmission'
                    ],
                    [
                        'name' => 'engine',
                        'type' => 'text',
                        'label' => 'Engine'
                    ],
                    [
                        'name' => 'engine_cylinders',
                        'type' => 'text',
                        'label' => 'Engine Cylinders'
                    ],
                    [
                        'name' => 'engine_displacement',
                        'type' => 'text',
                        'label' => 'Engine Displacement'
                    ],
                    [
                        'name' => 'fuel_type',
                        'type' => 'text',
                        'label' => 'Fuel Type'
                    ],
                    [
                        'name' => 'drive_train',
                        'type' => 'text',
                        'label' => 'Drive Train'
                    ],
                    [
                        'name' => 'doors',
                        'type' => 'number',
                        'label' => 'Doors'
                    ],
                    [
                        'name' => 'exterior_color',
                        'type' => 'text',
                        'label' => 'Exterior Color'
                    ],
                    [
                        'name' => 'interior_color',
                        'type' => 'text',
                        'label' => 'Interior Color'
                    ],
                ],
            ],
            'price' => [
                'description' => __('Pricing, MSRP, financing.', 'alpaca-bot'),
                'fields' => [
                    [
                        'name' => 'internet_price',
                        'type' => 'number',
                        'label' => 'Internet Price'
                    ],
                    [
                        'name' => 'invoice',
                        'type' => 'number',
                        'label' => 'Invoice'
                    ],
                    [
                        'name' => 'price',
                        'type' => 'number',
                        'label' => 'Price'
                    ],
                    [
                        'name' => 'sale_price',
                        'type' => 'number',
                        'label' => 'Sale Price'
                    ],
                    [
                        'name' => 'msrp',
                        'type' => 'number',
                        'label' => 'MSRP'
                    ],
                    [
                        'name' => 'lease_payment',
                        'type' => 'number',
                        'label' => 'Lease Payment'
                    ],
                ],
            ],
            'media' => [
                'description' => __('Images, videos, and other media.', 'alpaca-bot'),
                'fields' => [
                    [
                        'name' => 'photo_urls',
                        'type' => 'textarea',
                        'label' => 'Photo URLs'
                    ],
                    [
                        'name' => 'video_url',
                        'type' => 'text',
                        'label' => 'Video URL'
                    ],
                ],
            ],
            'rooftop' => [
                'description' => __('Dealership location details, URL, and contact information.', 'alpaca-bot'),
                'fields' => [
                    [
                        'name' => 'dealer_name',
                        'type' => 'text',
                        'label' => 'Dealer Name'
                    ],
                    [
                        'name' => 'dealer_address',
                        'type' => 'text',
                        'label' => 'Dealer Address'
                    ],
                    [
                        'name' => 'dealer_city',
                        'type' => 'text',
                        'label' => 'Dealer City'
                    ],
                    [
                        'name' => 'dealer_state',
                        'type' => 'text',
                        'label' => 'Dealer State'
                    ],
                    [
                        'name' => 'dealer_zip',
                        'type' => 'text',
                        'label' => 'Dealer Zip'
                    ],
                    [
                        'name' => 'listing_url',
                        'type' => 'text',
                        'label' => 'Listing URL'
                    ],
                ],
            ],
            'warranty' => [
                'description' => __('Warranty information for the vehicle.', 'alpaca-bot'),
                'fields' => [
                    [
                        'name' => 'certified',
                        'type' => 'text',
                        'label' => 'Certified'
                    ],
                    [
                        'name' => 'certification_warranty',
                        'type' => 'text',
                        'label' => 'Certification Warranty'
                    ],
                    [
                        'name' => 'warranty_month',
                        'type' => 'number',
                        'label' => 'Warranty Month'
                    ],
                    [
                        'name' => 'warranty_miles',
                        'type' => 'number',
                        'label' => 'Warranty Miles'
                    ],
                ],
            ],
            'additional_info' => [
                'description' => __('Additional information about the vehicle.', 'alpaca-bot'),
                'fields' => [
                    [
                        'name' => 'internet_special',
                        'type' => 'text',
                        'label' => 'Internet Special'
                    ],
                    [
                        'name' => 'book_value',
                        'type' => 'number',
                        'label' => 'Book Value'
                    ],
                    [
                        'name' => 'description',
                        'type' => 'textarea',
                        'label' => 'Description'
                    ],
                    [
                        'name' => 'options',
                        'type' => 'textarea',
                        'label' => 'Options'
                    ],
                    [
                        'name' => 'fuel_economy_city',
                        'type' => 'number',
                        'label' => 'Fuel Economy City'
                    ],
                    [
                        'name' => 'fuel_economy_highway',
                        'type' => 'number',
                        'label' => 'Fuel Economy Highway'
                    ],
                    [
                        'name' => 'vehicle_status',
                        'type' => 'text',
                        'label' => 'Vehicle Status'
                    ],
                    [
                        'name' => 'stock_type',
                        'type' => 'text',
                        'label' => 'Stock Type'
                    ],
                    [
                        'name' => 'vehicle_condition',
                        'type' => 'text',
                        'label' => 'Vehicle Condition'
                    ],
                    [
                        'name' => 'carfax_one_owner',
                        'type' => 'text',
                        'label' => 'Carfax One Owner'
                    ],
                    [
                        'name' => 'carfax_available',
                        'type' => 'text',
                        'label' => 'Carfax Available'
                    ],
                ],
            ],
        ];
    }
}
