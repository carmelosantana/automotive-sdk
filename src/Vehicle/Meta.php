<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Vehicle;

class Meta
{
    // This is the vehicle post type.
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'metaboxRegister']);
        add_action('save_post', [$this, 'metaboxSave']);

        // add custom sorted columns
        add_filter('manage_vehicle_posts_columns', [$this, 'addColumns']);
        add_action('manage_vehicle_posts_custom_column', [$this, 'addColumnsContent'], 10, 2);
        add_filter('manage_edit-vehicle_sortable_columns', [$this, 'addSortableColumns']);
        add_action('pre_get_posts', [$this, 'sortColumns']);
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
        unset($columns['date']);
        return $columns;
    }

    // Add content to custom columns
    public function addColumnsContent($column, $post_id)
    {
        switch ($column) {
                // tax
            case 'make':
                echo get_the_term_list($post_id, 'make', '', ', ', '');
                break;
            case 'model':
                echo get_the_term_list($post_id, 'model', '', ', ', '');
                break;
            case 'year':
                echo get_the_term_list($post_id, 'year', '', ', ', '');
                break;
                // meta
            case 'price':
                echo get_post_meta($post_id, 'price', true);
                break;
            case 'sale_price':
                echo get_post_meta($post_id, 'sale_price', true);
                break;
            case 'vin':
                echo get_post_meta($post_id, 'vin', true);
                break;
        }
    }

    // Add vehicle meta search to edit.php search
    public function addMetaSearch($query)
    {
        if (!is_admin() or !$query->is_main_query()) {
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
        if (!is_admin() or !$query->is_main_query()) {
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

    // display custom meta boxes for custom post type item
    public function metaboxRegister()
    {
        // add meta box per section
        $fields = Fields::get();
        foreach ($fields as $section => $field) {
            add_meta_box(
                'vehicle_meta_box_' . $section,
                $field['description'],
                // use a display function that takes the section as an argument
                function ($post) use ($section) {
                    $fields = Fields::get()[$section]['fields'];
                    $meta = get_post_meta($post->ID);

                    foreach ($fields as $field) {
                        $value = $meta[$field['name']][0] ?? '';
                        $label = $field['label'];
                        $type = $field['type'];
                        $name = $field['name'];
                        $data_type = $field['data_type'] ?? '';

                        // label on the left, input on the right
                        echo '<div style="display: flex; margin-bottom: 1rem;">';
                        echo '<label style="width: 200px;">' . $label . '</label>';

                        // prepare the data type
                        switch ($data_type) {
                            case 'array':
                                $value = maybe_unserialize($value);
                                $value = implode("\n", $value);
                                break;
                        }

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
        $fields = Fields::get();

        foreach ($fields as $section => $field) {
            foreach ($field['fields'] as $field) {
                $name = $field['name'];
                if (isset($_POST[$name])) {
                    switch ($field['data_type'] ?? $field['type']) {
                        case 'array':
                            $value = explode("\n", sanitize_textarea_field($_POST[$name]));
                            $value = array_filter($value);
                            $value = array_map('trim', $value);
                            update_post_meta($post_id, $name, $value);
                            break;

                        default:
                            update_post_meta($post_id, $name, sanitize_text_field($_POST[$name]));
                            break;
                    }
                }
            }
        }
    }
}
