<?php

declare(strict_types=1);

namespace WpAutos\Vehicles\Api;

class VehiclesApi
{
    protected VehicleSearch $vehicleSearch;

    public function __construct()
    {
        $this->vehicleSearch = new VehicleSearch();
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    /**
     * Register the /api/v0/vehicles/all route
     */
    public function registerRoutes(): void
    {
        register_rest_route('api/v0', '/vehicles/all', [
            'methods' => 'GET',
            'callback' => [$this, 'getVehicles'],
            'permission_callback' => '__return_true', // public access
        ]);
    }

    /**
     * Fetch all vehicles and return them in JSON format, using the search class for filtering.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getVehicles(\WP_REST_Request $request): \WP_REST_Response
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

                // Collect post data
                $vehicle = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'meta' => get_post_meta(get_the_ID()),
                    'make' => wp_get_post_terms(get_the_ID(), 'make', ['fields' => 'names']),
                    'model' => wp_get_post_terms(get_the_ID(), 'model', ['fields' => 'names']),
                    'year' => wp_get_post_terms(get_the_ID(), 'year', ['fields' => 'names']),
                ];

                $vehicles[] = $vehicle;
            }
            wp_reset_postdata();
        }

        // Return the vehicles as a JSON response
        return new \WP_REST_Response($vehicles, 200);
    }
}
