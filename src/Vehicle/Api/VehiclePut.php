<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Vehicle\Api;

class VehiclePut extends VehicleRestBase
{
    public function registerRoutes(): void
    {
        register_rest_route($this->api_namespace . '/' . $this->api_version, '/' . $this->api_post_type . '/(?P<id>\d+)', [
            'methods' => \WP_REST_Server::EDITABLE,
            'callback' => [$this, 'updateVehicle'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);
    }

    public function updateVehicle(\WP_REST_Request $request): \WP_REST_Response
    {
        $vehicle_id = (int)$request->get_param('id');
        $vehicle_data = $this->prepareVehicleData($request);

        $updated = wp_update_post([
            'ID' => $vehicle_id,
            'post_title' => $vehicle_data['title'],
        ]);

        if (is_wp_error($updated)) {
            return new \WP_REST_Response(['message' => 'Failed to update vehicle.'], 400);
        }

        $this->updateVehicleData($vehicle_id, $vehicle_data);

        return new \WP_REST_Response($this->prepareJsonData(get_post($vehicle_id)), 200);
    }
}
