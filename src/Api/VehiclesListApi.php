<?php

declare(strict_types=1);

namespace WpAutos\Vehicles\Api;

class VehiclesListApi
{
    protected VehicleSearch $vehicleSearch;

    public function __construct()
    {
        $this->vehicleSearch = new VehicleSearch();
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    /**
     * Register the /api/v0/vehicles/list route
     */
    public function registerRoutes(): void
    {
        register_rest_route('api/v0', '/vehicles/list', [
            'methods' => 'GET',
            'callback' => [$this, 'getVehiclesList'],
            'permission_callback' => '__return_true', // public access
        ]);
    }

    /**
     * Fetch all vehicles and return them in a simplified list format.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getVehiclesList(\WP_REST_Request $request): \WP_REST_Response
    {
        // Build the query args using the reusable search class
        $args = $this->vehicleSearch->buildQueryArgs($request);

        // Execute the query
        $vehicles_query = new \WP_Query($args);
        $vehicles = [];

        // Loop through each vehicle post
        if ($vehicles_query->have_posts()) {
            while ($vehicles_query->have_posts()) {
                $vehicles_query->the_post();

                // Get meta data
                $vin = get_post_meta(get_the_ID(), 'vin', true);
                $price = get_post_meta(get_the_ID(), 'price', true);

                // Get taxonomies
                $year = wp_get_post_terms(get_the_ID(), 'year', ['fields' => 'names'])[0] ?? '';
                $make = wp_get_post_terms(get_the_ID(), 'make', ['fields' => 'names'])[0] ?? '';
                $model = wp_get_post_terms(get_the_ID(), 'model', ['fields' => 'names'])[0] ?? '';
                $trim = wp_get_post_terms(get_the_ID(), 'trim', ['fields' => 'names'])[0] ?? '';
                $body = get_post_meta(get_the_ID(), 'body', true);  // Body is meta data

                // Create the vehicle key as "Year Make Model Trim Body"
                $vehicle_key = trim("{$year} {$make} {$model} {$trim} {$body}");

                // Remove extra whitespace
                $vehicle_key = preg_replace('/\s+/', ' ', $vehicle_key);

                // Compile the response
                $vehicle = [
                    'vin' => $vin,
                    'vehicle' => $vehicle_key,
                    'price' => $price,
                ];

                $vehicles[] = $vehicle;
            }
            wp_reset_postdata();
        }

        // Return the vehicles as a JSON response
        return new \WP_REST_Response($vehicles, 200);
    }
}
