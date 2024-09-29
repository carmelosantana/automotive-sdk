<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Vehicle\Api;

class VehiclePost extends VehicleRestBase
{
    public function registerRoutes(): void
    {
        register_rest_route($this->api_namespace . '/' . $this->api_version, '/' . $this->api_post_type, [
            'methods' => 'POST',
            'callback' => [$this, 'createVehicle'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);
    }

    public function createVehicle(\WP_REST_Request $request): \WP_REST_Response
    {
        $vehicle_data = $this->prepareVehicleData($request);
        $vehicle_id = wp_insert_post([
            'post_type' => 'vehicle',
            'post_title' => $vehicle_data['title'],
            'post_status' => 'publish',
        ]);

        if (is_wp_error($vehicle_id)) {
            return new \WP_REST_Response(['message' => 'Failed to create vehicle.'], 400);
        }

        return new \WP_REST_Response($this->prepareJsonData(get_post($vehicle_id)), 200);
    }
}
