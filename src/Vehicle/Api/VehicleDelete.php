<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Vehicle\Api;

class VehicleDelete extends VehicleRestBase
{
    public function registerRoutes(): void
    {
        register_rest_route($this->api_namespace . '/' . $this->api_version, '/' . $this->api_post_type . '/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'deleteVehicle'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);
    }

    public function deleteVehicle(\WP_REST_Request $request): \WP_REST_Response
    {
        $vehicle_id = (int)$request->get_param('id');
        $deleted = wp_delete_post($vehicle_id, true);

        if (!$deleted) {
            return new \WP_REST_Response(['message' => 'Failed to delete vehicle.'], 400);
        }

        return new \WP_REST_Response(['message' => 'Vehicle deleted successfully.'], 200);
    }
}
