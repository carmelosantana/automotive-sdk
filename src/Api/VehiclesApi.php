<?php

declare(strict_types=1);

namespace WpAutos\Vehicles\Api;

class VehiclesApi
{
    protected VehicleFields $vehicleFields;

    protected VehicleSearch $vehicleSearch;

    public function __construct()
    {
        $this->vehicleFields = new VehicleFields();
        $this->vehicleSearch = new VehicleSearch();

        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    /**
     * Register the /api/v0/vehicles/all and CRUD routes
     */
    public function registerRoutes(): void
    {
        register_rest_route('api/v0', '/vehicles/all', [
            'methods' => 'GET',
            'callback' => [$this, 'getVehicles'],
            'permission_callback' => '__return_true', // public access
        ]);

        register_rest_route('api/v0', '/vehicles/fields', [
            'methods' => 'GET',
            'callback' => [$this, 'getVehicleFields'],
            'permission_callback' => '__return_true', // public access
        ]);

        register_rest_route('api/v0', '/vehicles/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getVehicle'],
            'permission_callback' => '__return_true', // public access
        ]);

        register_rest_route('api/v0', '/vehicles', [
            'methods' => 'POST',
            'callback' => [$this, 'createVehicle'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route('api/v0', '/vehicles/(?P<id>\d+)', [
            'methods' => 'POST',
            'callback' => [$this, 'updateVehicle'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        register_rest_route('api/v0', '/vehicles/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'deleteVehicle'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);
    }

    /**
     * Check if the current user has permission to modify vehicles.
     *
     * @return bool
     */
    public function checkPermissions(): bool
    {
        return current_user_can('edit_others_posts');
    }

    /**
     * Fetch a specific vehicle by ID.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getVehicle(\WP_REST_Request $request): \WP_REST_Response
    {
        $vehicle_id = (int) $request->get_param('id');

        // Fetch the vehicle post
        $vehicle_post = get_post($vehicle_id);

        if (!$vehicle_post || $vehicle_post->post_type !== 'vehicle') {
            return new \WP_REST_Response(['message' => 'Vehicle not found.'], 404);
        }

        // Collect post data
        $vehicle = [
            'id' => $vehicle_post->ID,
            'title' => $vehicle_post->post_title,
            'meta' => get_post_meta($vehicle_post->ID),
            'make' => wp_get_post_terms($vehicle_post->ID, 'make', ['fields' => 'names']),
            'model' => wp_get_post_terms($vehicle_post->ID, 'model', ['fields' => 'names']),
            'year' => wp_get_post_terms($vehicle_post->ID, 'year', ['fields' => 'names']),
            'trim' => wp_get_post_terms($vehicle_post->ID, 'trim', ['fields' => 'names']),
        ];

        return new \WP_REST_Response($vehicle, 200);
    }

    /**
     * Get a schema of acceptable key/values for searching.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getVehicleFields(\WP_REST_Request $request): \WP_REST_Response
    {
        $fields = $this->vehicleFields->getFields();
        return new \WP_REST_Response($fields, 200);
    }

    /**
     * Fetch all vehicles.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getVehicles(\WP_REST_Request $request): \WP_REST_Response
    {
        $args = $this->vehicleSearch->buildQueryArgs($request);
        $vehicles_query = new \WP_Query($args);
        $vehicles = [];

        if ($vehicles_query->have_posts()) {
            while ($vehicles_query->have_posts()) {
                $vehicles_query->the_post();
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

        return new \WP_REST_Response($vehicles, 200);
    }

    /**
     * Create a new vehicle.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
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

        $this->updateVehicleMeta($vehicle_id, $vehicle_data);

        return new \WP_REST_Response(['message' => 'Vehicle created successfully.', 'vehicle_id' => $vehicle_id], 201);
    }

    /**
     * Update an existing vehicle.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
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

        $this->updateVehicleMeta($vehicle_id, $vehicle_data);

        return new \WP_REST_Response(['message' => 'Vehicle updated successfully.'], 200);
    }

    /**
     * Delete an existing vehicle.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function deleteVehicle(\WP_REST_Request $request): \WP_REST_Response
    {
        $vehicle_id = (int)$request->get_param('id');

        $deleted = wp_delete_post($vehicle_id, true);

        if (!$deleted) {
            return new \WP_REST_Response(['message' => 'Failed to delete vehicle.'], 400);
        }

        return new \WP_REST_Response(['message' => 'Vehicle deleted successfully.'], 200);
    }

    /**
     * Prepare vehicle data from the request.
     *
     * @param \WP_REST_Request $request
     * @return array
     */
    private function prepareVehicleData(\WP_REST_Request $request): array
    {
        return [
            'title' => $request->get_param('year') . ' ' . $request->get_param('make') . ' ' . $request->get_param('model'),
            'vin' => $request->get_param('vin'),
            'price' => $request->get_param('price'),
            'year' => $request->get_param('year'),
            'make' => $request->get_param('make'),
            'model' => $request->get_param('model'),
            'trim' => $request->get_param('trim'),
            'body' => $request->get_param('body'),
        ];
    }

    /**
     * Update vehicle meta data.
     *
     * @param int $vehicle_id
     * @param array $vehicle_data
     * @return void
     */
    private function updateVehicleMeta(int $vehicle_id, array $vehicle_data): void
    {
        foreach ($vehicle_data as $key => $value) {
            if (!empty($value)) {
                update_post_meta($vehicle_id, $key, sanitize_text_field($value));
            }
        }

        wp_set_object_terms($vehicle_id, $vehicle_data['make'], 'make');
        wp_set_object_terms($vehicle_id, $vehicle_data['model'], 'model');
        wp_set_object_terms($vehicle_id, $vehicle_data['year'], 'year');
    }
}
