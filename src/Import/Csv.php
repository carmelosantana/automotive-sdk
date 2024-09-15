<?php

declare(strict_types=1);

namespace WpAutos\VehiclesSdk\Import;

use WpAutos\VehiclesSdk\Admin\File;

class Csv
{
    protected ?File $file = null;
    protected array $file_header = [];
    protected array $template = [];

    /**
     * Imports data from the file.
     *
     * @return array Contains the number of vehicles added and updated.
     */
    public function fileImport(): array|bool
    {
        $file_data = $this->file->getData();

        if (!$file_data) {
            return false;
        }

        // disable post meta cache during import
        wp_suspend_cache_addition(true);

        // Remove the header row
        unset($file_data[0]);

        $vehicles_added = [];
        $vehicles_updated = [];
        foreach ($file_data as $data) {
            $vehicle = $this->mapDataToVehicle($data);

            // Check if the vehicle exists
            $vin_exists = get_posts(['post_type' => 'vehicle', 'meta_key' => 'vin', 'meta_value' => $vehicle['vin']]);

            if (count($vin_exists) > 0) {
                $vehicle_id = $vin_exists[0]->ID;
                wp_update_post([
                    'ID' => $vehicle_id,
                    'post_title' => $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'],
                ]);
                $vehicles_updated[] = $vehicle_id;
            } else {
                // Insert new vehicle
                $vehicle_id = wp_insert_post([
                    'post_type' => 'vehicle',
                    'post_title' => $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'],
                    'post_status' => 'publish',
                ]);
                $vehicles_added[] = $vehicle_id;
            }

            // Add meta and taxonomy
            $this->addMetaAndTaxonomy($vehicle_id, $vehicle);
        }

        return [
            'added' => count($vehicles_added),
            'updated' => count($vehicles_updated),
        ];
    }

    public function setFile($file)
    {
        $this->file = new File();
        $this->file->load($file);

        return $this;
    }    

    /**
     * Adds meta and taxonomy to a vehicle post.
     *
     * @param int $vehicle_id The ID of the vehicle post.
     * @param array $vehicle The vehicle data.
     * @return void
     */
    private function addMetaAndTaxonomy(int $vehicle_id, array $vehicle): void
    {
        // Add meta
        foreach ($vehicle as $key => $value) {
            update_post_meta($vehicle_id, $key, $value);
        }

        // Add taxonomy
        wp_set_object_terms($vehicle_id, $vehicle['make'], 'make');
        wp_set_object_terms($vehicle_id, $vehicle['model'], 'model');
        wp_set_object_terms($vehicle_id, $vehicle['trim'], 'trim');
        wp_set_object_terms($vehicle_id, $vehicle['year'], 'year');
    }

    /**
     * Maps the CSV data to the vehicle array.
     *
     * @param array $data The row of data from the CSV file.
     * @return array Mapped vehicle data.
     */
    private function mapDataToVehicle(array $data): array
    {
        $vehicle = [];
        foreach ($this->file->getTemplate()['template'] as $key => $value) {
            if (is_string($value)) {
                $vehicle[$value] = $data[array_search($key, $this->file->getHeader())];
            } elseif (is_array($value)) {
                $match = array_intersect($value, $this->file->getHeader());
                $vehicle[$key] = $data[array_search($match[0], $this->file->getHeader())];
            }
        }
        return $vehicle;
    }
}
