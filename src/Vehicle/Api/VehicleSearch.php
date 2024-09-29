<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Vehicle\Api;

use WpAutos\AutomotiveSdk\Vehicle\Fields as VehicleFields;

class VehicleSearch
{
    /**
     * Build the WP_Query arguments for the vehicle search.
     *
     * @param \WP_REST_Request $request
     * @return array
     */
    public function buildQueryArgs(\WP_REST_Request $request): array
    {
        $meta_search_keys = ['vin', 'options', 'description'];
        $args = [
            'post_type' => 'vehicle',
            'posts_per_page' => -1,
            'meta_query' => [],
            'tax_query' => ['relation' => 'AND'],
        ];

        // Search by meta fields
        foreach ($meta_search_keys as $key) {
            if ($value = $request->get_param($key)) {
                $args['meta_query'][] = [
                    'key' => $key,
                    'value' => $value,
                    'compare' => 'LIKE',
                ];
            }
        }

        // Search taxonomy terms
        $taxonomy = VehicleFields::getTaxonomies();

        foreach ($taxonomy as $tax) {
            if ($value = $request->get_param($tax['name'])) {
                $args['tax_query'][] = [
                    'taxonomy' => $tax['name'],
                    'field' => 'slug',
                    'terms' => $value,
                ];
            }
        }

        return $args;
    }
}
