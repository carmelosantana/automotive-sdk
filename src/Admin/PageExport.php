<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Admin;

use WpAutos\AutomotiveSdk\Vehicle\Data;

class PageExport extends Page
{
    protected $page_slug = 'export';
    protected $page_title = 'Export';
    protected $menu_title = 'Export';

    public function __construct()
    {
        parent::__construct();
        add_action('admin_enqueue_scripts', [$this, 'adminAdditionalScripts']);
        add_action('wp_ajax_export_json', [$this, 'exportJsonAjax']);
        add_action('wp_ajax_export_csv', [$this, 'exportCsvAjax']);
    }

    public function adminContent(): void
    {
        echo '<h3>All Vehicles</h3>';
        $this->adminExportList();
    }

    public function adminExportList(): void
    {
        echo '<table class="wp-list-table widefat fixed striped" style="width: 100%;">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Export Type</th>';
        echo '<th>Description</th>';
        echo '<th>Action</th>';
        echo '</tr>';
        echo '</thead><tbody>';

        $export_types = [
            'json' => 'Export data as JSON format',
            'csv' => 'Export data as CSV format',
        ];

        foreach ($export_types as $type => $description) {
            echo '<tr>';
            echo '<td>' . strtoupper($type) . '</td>';
            echo '<td>' . esc_html($description) . '</td>';
            echo '<td><button class="button export-' . esc_attr($type) . '-btn" data-type="' . esc_attr($type) . '">Export</button></td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    public function adminAdditionalScripts(): void
    {
        wp_enqueue_script('vehicles-export-js', ASDK_ASSETS_URL . '/js/vehicles-export.js', ['jquery'], null, true);
        wp_localize_script('vehicles-export-js', 'vehiclesExport', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('vehicles_export_nonce'),
        ]);
    }

    public function exportJsonAjax(): void
    {
        check_ajax_referer('vehicles_export_nonce', 'nonce');
        $data = new Data();
        $vehicles = $data->queryVehicles();
        $json = json_encode($vehicles, JSON_PRETTY_PRINT);

        // Create a temporary file for the JSON data
        $upload_dir = wp_upload_dir();
        $file_name = 'vehicles-export-' . time() . '.json';
        $file_path = $upload_dir['path'] . '/' . $file_name;
        file_put_contents($file_path, $json);

        // Return the URL of the file to download
        wp_send_json_success(['url' => $upload_dir['url'] . '/' . $file_name]);
    }

    public function exportCsvAjax(): void
    {
        check_ajax_referer('vehicles_export_nonce', 'nonce');
        $data = new Data();
        $vehicles = $data->queryVehicles();

        // Convert data to CSV format
        $csv = $this->arrayToCsv($vehicles);

        // Create a temporary file for the CSV data
        $upload_dir = wp_upload_dir();
        $file_name = 'vehicles-export-' . time() . '.csv';
        $file_path = $upload_dir['path'] . '/' . $file_name;
        file_put_contents($file_path, $csv);

        // Return the URL of the file to download
        wp_send_json_success(['url' => $upload_dir['url'] . '/' . $file_name]);
    }

    private function arrayToCsv(array $data): string
    {
        $csv = '';
        if (empty($data)) {
            return $csv;
        }

        // Add header row
        $headers = array_keys($data[0]);
        $csv .= implode(',', $headers) . "\n";

        // Add data rows
        foreach ($data as $row) {
            $csv .= implode(',', array_map([$this, 'escapeCsvValue'], $row)) . "\n";
        }

        return $csv;
    }

    private function escapeCsvValue($value): string
    {
        // Convert the value to a string if it's not
        $value = (string) $value;

        // Escape double quotes
        $escaped = str_replace('"', '""', $value);

        // If the value contains a comma, newline or double quote, enclose it in double quotes
        if (strpos($escaped, ',') !== false || strpos($escaped, "\n") !== false || strpos($escaped, '"') !== false) {
            $escaped = '"' . $escaped . '"';
        }

        return $escaped;
    }
}
