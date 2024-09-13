<?php

declare(strict_types=1);

namespace WpAutos\Vehicles\Api;

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
        $args = [
            'post_type' => 'vehicle',
            'posts_per_page' => -1,
            'meta_query' => [],
            'tax_query' => ['relation' => 'AND'],
        ];

        // Add search by VIN (meta query)
        if ($vin = $request->get_param('vin')) {
            $args['meta_query'][] = [
                'key' => 'vin',
                'value' => $vin,
                'compare' => 'LIKE',
            ];
        }

        // Add search by make, model, and year (taxonomies)
        if ($make = $request->get_param('make')) {
            $args['tax_query'][] = [
                'taxonomy' => 'make',
                'field' => 'name',
                'terms' => $make,
                'operator' => 'LIKE',
            ];
        }

        if ($model = $request->get_param('model')) {
            $args['tax_query'][] = [
                'taxonomy' => 'model',
                'field' => 'name',
                'terms' => $model,
                'operator' => 'LIKE',
            ];
        }

        if ($year = $request->get_param('year')) {
            $args['tax_query'][] = [
                'taxonomy' => 'year',
                'field' => 'name',
                'terms' => $year,
                'operator' => 'LIKE',
            ];
        }

        if ($options = $request->get_param('options')) {
            $args['meta_query'][] = [
                'key' => 'options',
                'value' => $options,
                'compare' => 'LIKE',
            ];
        }

        return $args;
    }
}
