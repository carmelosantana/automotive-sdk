<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Import;

use WpAutos\AutomotiveSdk\Admin\File;

class Csv
{
    protected ?File $file = null;
    protected array $file_header = [];
    protected array $template = [];

    private array $mapping = [];

    /**
     * Set the mapping for the import.
     *
     * @param array $mapping The mapping array.
     */
    public function setMapping(array $mapping): void
    {
        $this->mapping = $mapping;
    }

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


    /**
     * Set the file for the CSV import.
     *
     * @param string $file
     * @return $this
     */
    public function setFile(string $file): self
    {
        $this->file = new File();
        $this->file->load($file);

        return $this;
    }

    /**
     * Process a batch of vehicle data.
     *
     * @param int $offset The starting row for the batch.
     * @param int $limit The number of rows to process.
     * @return array Contains the number of vehicles added and updated.
     */
    public function fileImportBatch(int $offset, int $limit): array
    {
        $file_data = $this->file->getData();

        if (!$file_data) {
            return ['added' => 0, 'updated' => 0];
        }

        // Remove the header row if it's the first batch
        if ($offset === 0) {
            unset($file_data[0]);
        }

        $file_data = array_slice($file_data, $offset, $limit);

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
        $data = array_combine($this->file->getHeader(), $data);

        $vehicle = [];

        foreach ($this->mapping as $key => $value) {
            if (is_string($value)) {
                $vehicle[$value] = $data[array_search($key, $this->file->getHeader())];
            } elseif (is_array($value)) {
                if (isset($value['csv'])) {
                    $vehicle[$key] = $data[$value['csv']];
                } else {
                    $match = array_intersect($value, $this->file->getHeader());
                    $vehicle[$key] = $data[array_shift($match)];
                }
            }
        }

        return $vehicle;
    }
}
