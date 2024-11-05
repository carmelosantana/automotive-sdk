<?php

declare(strict_types=1);

namespace WipyAutos\AutomotiveSdk\Vehicle;

class Data
{
    public $vehicle;

    public function generateSlug(array $vehicle): string
    {
        return sanitize_title(strtolower($vehicle['vin'] . '-' . $vehicle['year'] . '-' . $vehicle['make'] . '-' . $vehicle['model'] . '-' . $vehicle['trim']));

    }

    public function generateTitle(array $vehicle): string
    {
        return trim(preg_replace('/\s+/', ' ', $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'] . ' ' . $vehicle['trim']));
    }

    public function get(string $key, $default = null)
    {
        return $this->vehicle[$key] ?? $default;
    }

    public function getVehicle()
    {
        return $this->vehicle;
    }

    /**
     * Retrieve a vehicle by its ID.
     *
     * @param int $id The vehicle ID.
     * @return array|null The vehicle data or null if not found.
     */
    public function getVehicleById(int $id): ?array
    {
        $vehicle_post = get_post($id);

        if (!$vehicle_post or $vehicle_post->post_type !== 'vehicle') {
            return null;
        }

        return $this->buildVehicleData($vehicle_post);
    }

    /**
     * Retrieve a vehicle by VIN.
     *
     * @param string $vin The VIN of the vehicle.
     * @return array|null The vehicle data or null if not found.
     */
    public function getVehicleByVin(string $vin): ?array
    {
        $args = [
            'post_type' => 'vehicle',
            'meta_query' => [
                [
                    'key' => 'vin',
                    'value' => $vin,
                    'compare' => '='
                ]
            ],
            'posts_per_page' => 1,
        ];

        $query = new \WP_Query($args);
        if ($query->have_posts()) {
            $query->the_post();
            $vehicle_post = get_post();
            wp_reset_postdata();
            return $this->buildVehicleData($vehicle_post);
        }

        return null;
    }

    /**
     * Build a vehicle data array from a post object.
     *
     * @param \WP_Post $vehicle_post The vehicle post object.
     * @return array The vehicle data including taxonomies and meta values.
     */
    private function buildVehicleData(\WP_Post $vehicle_post): array
    {
        $vehicle_data = [
            'id' => $vehicle_post->ID,
            'title' => $vehicle_post->post_title,
            'make' => $this->getTaxonomyTerms($vehicle_post->ID, 'make'),
            'model' => $this->getTaxonomyTerms($vehicle_post->ID, 'model'),
            'trim' => $this->getTaxonomyTerms($vehicle_post->ID, 'trim'),
            'year' => $this->getTaxonomyTerms($vehicle_post->ID, 'year'),
        ];

        return $vehicle_data;
    }

    public function setVehicle($vehicle)
    {
        $this->vehicle = $vehicle;
    }

    /**
     * Query vehicles based on various parameters.
     *
     * @param array $meta_query The meta query array to filter vehicles.
     * @param int $posts_per_page The number of posts per page (default: -1 for all).
     * @return array The list of vehicles.
     */
    public function queryVehicles(array $meta_query = [], int $posts_per_page = -1): array
    {
        $args = [
            'post_type' => 'vehicle',
            'meta_query' => $meta_query,
            'posts_per_page' => $posts_per_page,
        ];

        $query = new \WP_Query($args);
        $vehicles = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $vehicle_post = get_post();
                $vehicles[] = $this->buildVehicleData($vehicle_post);
            }
            wp_reset_postdata();
        }

        return $vehicles;
    }

    /**
     * Retrieve terms for a given taxonomy and post ID.
     *
     * @param int $post_id The post ID.
     * @param string $taxonomy The taxonomy slug.
     * @return array The terms for the taxonomy.
     */
    private function getTaxonomyTerms(int $post_id, string $taxonomy): array
    {
        return wp_get_post_terms($post_id, $taxonomy, ['fields' => 'names']);
    }
}
