<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Api\Vehicles;

use WpAutos\AutomotiveSdk\Vehicle\Fields as VehicleMetaFields;

class VehicleFields
{
    /**
     * Get all field keys and their unique values.
     *
     * @return array
     */
    public function getFields(): array
    {
        // Try to get the cached result first
        $fields = get_transient('vehicle_fields_data');
        if ($fields !== false) {
            return $fields;
        }

        // Retrieve meta fields from Vehicle Meta and their unique values
        $meta_fields = $this->getMetaFields();

        // Retrieve all unique taxonomy values
        $taxonomy_values = $this->getTaxonomyValues();

        // Combine both results
        $fields = array_merge($meta_fields, $taxonomy_values);

        // Cache the result for 5 minutes
        set_transient('vehicle_fields_data', $fields, 5 * MINUTE_IN_SECONDS);

        return $fields;
    }

    /**
     * Get all unique meta fields and their values based on Vehicle Meta.
     *
     * @return array
     */
    private function getMetaFields(): array
    {
        global $wpdb;
        $fields = [];

        // Fetch all fields from Vehicle/Meta
        $meta_definitions = VehicleMetaFields::get();
        $meta_keys = [];

        // Collect meta keys from Vehicle Meta definitions
        foreach ($meta_definitions as $section) {
            foreach ($section['fields'] as $field) {
                $meta_keys[$field['name']] = $field['type'];
            }
        }

        // Fetch unique values for each meta key
        foreach ($meta_keys as $key => $type) {
            // Filter string values to 64 characters or less
            if ($type === 'text') {
                $values = $wpdb->get_col($wpdb->prepare("
                    SELECT DISTINCT meta_value
                    FROM {$wpdb->postmeta}
                    WHERE meta_key = %s AND CHAR_LENGTH(meta_value) <= 64
                ", $key));

                $fields[$key] = [
                    'type' => $type,
                    'values' => $values,
                ];
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
        $taxonomies = ['make', 'model', 'trim', 'year'];
        $fields = [];

        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            ]);

            $fields[$taxonomy] = [
                'type' => 'string',
                'values' => !is_wp_error($terms) ? wp_list_pluck($terms, 'name') : [],
            ];
        }

        return $fields;
    }
}
