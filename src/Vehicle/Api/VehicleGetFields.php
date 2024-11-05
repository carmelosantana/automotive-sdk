<?php

declare(strict_types=1);

namespace WipyAutos\AutomotiveSdk\Vehicle\Api;

use WipyAutos\AutomotiveSdk\Vehicle\Fields as VehicleMetaFields;

class VehicleGetFields extends VehicleRestBase
{
    public function registerRoutes(): void
    {
        register_rest_route($this->api_namespace . '/' . $this->api_version, '/' . $this->api_post_type . '/fields', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$this, 'getFields'],
            'permission_callback' => '__return_true', // Allow public access
        ]);
    }

    /**
     * Get all field keys and their unique values (meta and taxonomies).
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getFields(\WP_REST_Request $request = null, $response = 'api'): \WP_REST_Response|array
    {
        // Try to get the cached result first
        $fields = get_transient('vehicle_fields_data');
        if ($fields !== false) {
            if ($response === 'api') {
                return new \WP_REST_Response($fields, 200);
            }
            return $fields;
        }

        // Retrieve meta fields from Vehicle Meta and their unique values
        $meta_fields = $this->getMetaFields();

        // Retrieve all unique taxonomy values
        $taxonomy_values = $this->getTaxonomyValues();

        // Combine both results
        $fields = array_merge($meta_fields, $taxonomy_values);

        // Cache the result for 5 minutes
        set_transient('vehicle_fields_data', $fields, 60 * MINUTE_IN_SECONDS);

        if ($response === 'api') {
            return new \WP_REST_Response($fields, 200);
        }
        return $fields;
    }

    /**
     * Get all unique meta fields and their values based on Vehicle Meta, including min and max for number fields.
     *
     * @return array
     */
    private function getMetaFields(): array
    {
        global $wpdb;
        $fields = [];

        // Fetch all fields from Vehicle/Meta
        $meta_definitions = VehicleMetaFields::getMetasFlat();
        $meta_keys = [];

        // Collect meta keys from Vehicle Meta definitions
        foreach ($meta_definitions as $field) {
            $meta_keys[$field['name']] = $field['type'];
        }

        // Fetch unique values for each meta key
        foreach ($meta_keys as $key => $type) {
            // Handle number fields to provide min and max values
            if ($type === 'number') {
                $min_value = $wpdb->get_var($wpdb->prepare("
                SELECT MIN(CAST(meta_value AS UNSIGNED)) 
                FROM {$wpdb->postmeta}
                WHERE meta_key = %s AND meta_value REGEXP '^[0-9]+$'
            ", $key));

                $max_value = $wpdb->get_var($wpdb->prepare("
                SELECT MAX(CAST(meta_value AS UNSIGNED)) 
                FROM {$wpdb->postmeta}
                WHERE meta_key = %s AND meta_value REGEXP '^[0-9]+$'
            ", $key));

                $fields[$key] = [
                    'type' => $type,
                    'min' => $min_value ?: 0,  // Default to 0 if no value is found
                    'max' => $max_value ?: 0,  // Default to 0 if no value is found
                ];

                // Handle text fields, limiting string length to 64 characters or less
            } elseif ($type === 'text') {
                $values = $wpdb->get_col($wpdb->prepare("
                SELECT DISTINCT meta_value
                FROM {$wpdb->postmeta}
                WHERE meta_key = %s AND CHAR_LENGTH(meta_value) <= 64
            ", $key));

                $fields[$key] = [
                    'type' => $type,
                    'values' => $values,
                ];

                // Handle other types
            } else {
                $fields[$key] = [
                    'type' => $type,
                ];
            }
        }

        return $fields;
    }

    /**
     * Get all unique values for common taxonomies.
     *
     * @return array
     */
    private function getTaxonomyValues(): array
    {
        $taxonomies = VehicleMetaFields::getTaxonomies();
        $fields = [];

        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms([
                'taxonomy' => $taxonomy['name'],
                'hide_empty' => false,
            ]);

            $fields[$taxonomy['name']] = [
                'type' => 'string',
                'values' => !is_wp_error($terms) ? wp_list_pluck($terms, 'name') : [],
            ];
        }

        return $fields;
    }
}
