<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Import;

use WpAutos\AutomotiveSdk\Admin\File;
use WpAutos\AutomotiveSdk\Vehicle\Fields as VehicleFields;

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
    public function fileImport(int $offset = 0, int $limit = 0): array
    {
        $file_data = $this->file->getData();

        if (!$file_data) {
            return ['added' => 0, 'updated' => 0];
        }

        // Remove the header row if it's the first batch
        if ($offset === 0) {
            unset($file_data[0]);
        }

        $file_data = $limit > 0 ? array_slice($file_data, $offset, $limit) : array_slice($file_data, $offset);

        $vehicles_added = [];
        $vehicles_updated = [];

        foreach ($file_data as $data) {
            $vehicle = $this->mapDataToVehicle($data); // Map CSV data to vehicle fields
            $vehicle = $this->parseData($vehicle); // Parse and filter the data

            // Apply any filters to vehicle data before importing
            $vehicle = apply_filters('wpautos_import_vehicle_data', $vehicle);

            // Check if the vehicle exists by VIN
            $vin_exists = get_posts(['post_type' => 'vehicle', 'meta_key' => 'vin', 'meta_value' => $vehicle['vin']]);
            if (count($vin_exists) > 0) {
                $vehicle_id = $vin_exists[0]->ID;
                wp_update_post([
                    'ID' => $vehicle_id,
                    'post_title' => $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'],
                ]);
                $vehicles_updated[] = $vehicle_id;

                // clear taxonomies
                $taxonomies = VehicleFields::getTaxonomies();
                foreach ($taxonomies as $taxonomy) {
                    wp_set_post_terms($vehicle_id, [], $taxonomy['name']);
                }

                // clear meta
                $metas = VehicleFields::getMetas();
                foreach ($metas as $meta) {
                    delete_post_meta($vehicle_id, $meta['name']);
                }
            } else {
                // Insert new vehicle post
                $vehicle_id = wp_insert_post([
                    'post_type' => 'vehicle',
                    'post_title' => $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'],
                    'post_status' => 'publish',
                ]);
                $vehicles_added[] = $vehicle_id;
            }

            // Add meta and taxonomy to the post
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
            if (empty($value)) {
                continue;
            }
            update_post_meta($vehicle_id, $key, $value);
        }

        // Add taxonomy
        $taxonomies = VehicleFields::getTaxonomies();
        foreach ($taxonomies as $taxonomy) {
            if (!isset($vehicle[$taxonomy['name']]) or empty($vehicle[$taxonomy['name']])) {
                continue;
            }

            wp_set_post_terms($vehicle_id, $vehicle[$taxonomy['name']], $taxonomy['name']);
        }
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
                $csv_column = $value['csv'] ?? null;
                $vehicle[$key] = $csv_column ? $data[$csv_column] : null;
            }
        }

        return $vehicle;
    }

    /**
     * Parse and sanitize the vehicle data fields.
     *
     * @param array $data The vehicle data to be parsed.
     * @return array Parsed vehicle data.
     */
    public function parseData(array $data): array
    {
        $parsed_data = [];
        foreach ($data as $key => $value) {
            $parsed_data[$key] = $this->parseField($key, $value);
        }
        return $parsed_data;
    }

    /**
     * Parse individual field based on field type.
     *
     * @param string $key The field key.
     * @param mixed $value The field value.
     * @return mixed Parsed field value.
     */
    public function parseField(string $key, mixed $value): mixed
    {
        $fields = VehicleFields::getMetas();
        $field = null;

        // Find the matching field configuration
        foreach ($fields as $section) {
            foreach ($section['fields'] as $f) {
                if ($f['name'] === $key) {
                    $field = $f;
                    break;
                }
            }
        }

        if ($field) {
            $value = sanitize_text_field($value);

            // Process based on field type
            switch ($field['data_type'] ?? $field['type'] ?? '') {
                case 'number':
                    $value = preg_replace('/[^0-9]/', '', $value); // Remove non-numeric characters
                    break;
                case 'array':
                    $delimiter = (isset($this->mapping[$key]['delimiter']) and !empty($this->mapping[$key]['delimiter'])) ? sanitize_text_field($this->mapping[$key]['delimiter']) : ',';
                    $value = explode($delimiter, $value); // Convert to array using delimiter
                    break;
            }
        }

        return $value;
    }
}
