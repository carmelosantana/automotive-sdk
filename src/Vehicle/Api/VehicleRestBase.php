<?php

declare(strict_types=1);

namespace WipyAutos\AutomotiveSdk\Vehicle\Api;

use WipyAutos\AutomotiveSdk\Vehicle\Data as VehicleData;
use WipyAutos\AutomotiveSdk\Vehicle\Fields;

class VehicleRestBase
{
    protected string $api_namespace = 'automotive-sdk';
    protected string $api_post_type = 'vehicles';
    protected string $api_version = 'v1';

    public function register(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    // Register routes in child classes
    public function registerRoutes(): void
    {
        // Child classes will implement this
    }

    public function checkPermissions(): \WP_Error|bool
    {
        if (!current_user_can('edit_posts')) {
            return new \WP_Error('rest_forbidden', esc_html__('You cannot access this resource.', 'automotive-sdk'), ['status' => 401]);
        }

        return true;
    }

    // Prepare vehicle data for output request
    protected function prepareJsonData(\WP_Post $post): array
    {
        $metas = [];
        $fields = Fields::getMetasFlat();

        foreach ($fields as $field) {
            $value = get_post_meta($post->ID, $field['name'], true);
            if ($value !== '') {
                $metas[$field['name']] = $value;

                switch ($field['data_type']) {
                    case 'array':
                        $metas[$field['name']] = maybe_unserialize($value);
                        break;
                }
            }
        }

        // taxonomies need key value pair
        $taxonomies = [];
        $fields = Fields::getTaxonomies();
        foreach ($fields as $field) {
            $terms = wp_get_post_terms($post->ID, $field['name'], ['fields' => 'names']);
            if (!empty($terms)) {
                $taxonomies[$field['name']] = $terms[0];
            }
        }

        return [
            'id' => $post->ID,
            'date' => $post->post_date,
            'slug' => $post->post_name,
            'status' => $post->post_status,
            'title' => ['rendered' => $post->post_title],
            'content' => ['rendered' => $post->post_content],
            'meta' => $metas,
            'taxonomies' => $taxonomies,
            '_links' => [
                'alternate' => [
                    [
                        'href' => get_permalink($post->ID)
                    ]
                ],
                'collection' => [
                    [
                        'href' => get_rest_url(null, "/{$this->api_namespace}/{$this->api_version}/{$this->api_post_type}")
                    ]
                ],
                'self' => [
                    [
                        'href' => get_rest_url(null, "/{$this->api_namespace}/{$this->api_version}/{$this->api_post_type}/{$post->ID}")
                    ]
                ]
            ]
        ];
    }

    // Prepare vehicle data for io request
    protected function prepareVehicleData(\WP_REST_Request $request): array
    {
        // prepare metas
        $metas = [];
        $fields = Fields::getMetas();
        foreach ($fields as $field) {
            $value = $request->get_param($field['name']);
            if ($value !== null) {
                $metas[$field['name']] = sanitize_text_field($value);
            }
        }

        // prepare taxonomies
        $taxonomies = [];
        $fields = Fields::getTaxonomies();
        foreach ($fields as $field) {
            $value = $request->get_param($field['name']);
            if ($value !== null) {
                $taxonomies[$field['name']] = sanitize_text_field($value);
            }
        }

        if (empty($request->get_param('title'))) {
            $request->set_param('title', (new VehicleData())->generateTitle($request->get_params()));
        }

        if (empty($request->get_param('content'))) {
            $request->set_param('content', '');
        }

        if (empty($request->get_param('post_status'))) {
            $request->set_param('post_status', 'publish');
        }

        return [
            'title' => $request->get_param('title'),
            'content' => $request->get_param('content'),
            'post_status' => $request->get_param('post_status'),
            'metas' => $metas,
            'taxonomies' => $taxonomies,
        ];
    }

    // Update vehicle data = meta, taxonomies
    protected function updateVehicleData(int $vehicle_id, array $vehicle_data): void
    {
        $this->updateVehicleMeta($vehicle_id, $vehicle_data['metas']);
        $this->updateVehicleTaxonomies($vehicle_id, $vehicle_data['taxonomies']);
    }

    // Update vehicle meta data
    protected function updateVehicleMeta(int $vehicle_id, array $vehicle_data): void
    {
        foreach ($vehicle_data as $key => $value) {
            if (!empty($value)) {
                update_post_meta($vehicle_id, $key, $value);
            } else {
                delete_post_meta($vehicle_id, $key);
            }
        }
    }

    // Update Taxonomies
    protected function updateVehicleTaxonomies(int $vehicle_id, array $vehicle_data): void
    {
        foreach ($vehicle_data as $taxonomy => $terms) {
            if (!empty($terms)) {
                wp_set_object_terms($vehicle_id, $terms, $taxonomy);
            } else {
                wp_set_object_terms($vehicle_id, [], $taxonomy);
            }
        }
    }
}
