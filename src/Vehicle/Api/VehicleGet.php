<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Vehicle\Api;

class VehicleGet extends VehicleRestBase
{
    public function registerRoutes(): void
    {
        register_rest_route($this->api_namespace . '/' . $this->api_version, '/vehicles', [
            'methods' => 'GET',
            'callback' => [$this, 'getVehicles'],
            'permission_callback' => '__return_true', // Public access
        ]);

        register_rest_route($this->api_namespace . '/' . $this->api_version, '/vehicles/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getVehicle'],
            'permission_callback' => '__return_true', // Public access
        ]);
    }

    public function getVehicles(\WP_REST_Request $request): \WP_REST_Response
    {
        $args = (new VehicleSearch())->buildQueryArgs($request);
        $vehicles_query = new \WP_Query($args);
        $vehicles = [];

        if ($vehicles_query->have_posts()) {
            while ($vehicles_query->have_posts()) {
                $vehicles_query->the_post();
                $vehicle = $this->prepareJsonData(get_post());
                $vehicles[] = $vehicle;
            }
            wp_reset_postdata();
        }

        return new \WP_REST_Response($vehicles, 200);
    }

    public function getVehicle(\WP_REST_Request $request): \WP_REST_Response
    {
        $vehicle_id = (int)$request->get_param('id');
        $vehicle_post = get_post($vehicle_id);

        if (!$vehicle_post or $vehicle_post->post_type !== 'vehicle') {
            return new \WP_REST_Response(['message' => 'Vehicle not found.'], 404);
        }

        $vehicle = $this->prepareJsonData($vehicle_post);

        return new \WP_REST_Response($vehicle, 200);
    }
}
