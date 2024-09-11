<?php

declare(strict_types=1);

namespace CarmeloSantana\VinImporter\Import;

use CarmeloSantana\VinImporter\Vehicle;

class Admin
{
    public $actions_separator = ' • ';

    private $file_data;

    private $file_header;

    private $file_header_hash;

    private $library;

    private $template;

    private $Files;

    public function __construct()
    {
        add_action('admin_menu', [$this, 'adminMenu']);
        add_action('wp_ajax_get_vehicle_count', [$this, 'adminVehicleCountAjax']);
        add_action('wp_ajax_nopriv_get_vehicle_count', [$this, 'adminVehicleCountAjax']); // for non-logged-in users
        add_action('admin_enqueue_scripts', [$this, 'adminEnqueue']);
        add_action('admin_footer', [$this, 'adminInlineJs']);

        add_filter('upload_mimes', [$this, 'allowUploadMimes']);

        $this->Files = new Files();
    }

    // enque scripts and styles
    public function adminEnqueue()
    {
        wp_enqueue_script('vin-importer-admin', VIN_IMPORTER_DIR_URL . 'assets/vin-importer.js', ['jquery'], null, true);
    }

    // output inline js
    public function adminInlineJs()
    {
?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Perform the AJAX request on page load
                fetchVehicleCount();
            });

            function fetchVehicleCount() {
                var ajaxUrl = "<?php echo admin_url('admin-ajax.php'); ?>";

                // Send the AJAX request
                fetch(ajaxUrl + "?action=get_vehicle_count")
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Display the count in the placeholder
                            document.getElementById('vehicle-count').innerHTML = '🟢 Vehicle Count: ' + data.count;
                        } else {
                            document.getElementById('vehicle-count').innerHTML = '⚠️ Error fetching vehicle count';
                        }
                    });
            }
        </script>
<?php
    }

    public function adminMenu()
    {
        add_submenu_page(
            // add to custom vehicle post type
            'edit.php?post_type=vehicle',
            VIN_IMPORTER_TITLE,
            VIN_IMPORTER_TITLE,
            'manage_options',
            VIN_IMPORTER . '-tools',
            [$this, 'adminPage']
        );
    }

    public function adminPage()
    {
        set_time_limit(300);

        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        echo '<div class="wrap">';
        echo '<h1>VIN Importer</h1>';
        echo '<p>Utilities for interacting with multiple vehicle datasets.</p>';

        $this->adminActionsList();

        $this->adminVehicleCount();

        $this->adminPossibleFileList();

        $this->adminFileCheck();

        $this->adminFileInfo();

        echo '<pre>';

        switch ($_GET['action'] ?? null) {
            case 'delete_all_vehicles':
                $this->adminVehiclesDelete();
                break;

            case 'import':
                // disable post meta cache during import
                wp_suspend_cache_addition(true);
                $this->adminFileImport();
                break;

            case 'refresh_files':
                $this->adminFilesRefresh();
                break;

            case 'get_all_headers':
                $this->adminFilesGetHeaders();
                break;
        }

        echo '</pre>';

        echo '</div>';
    }

    /**
     * Outputs the count of vehicles via AJAX.
     *
     * @return void
     */
    public function adminVehicleCountAjax(): void
    {
        // Retrieve the count of all vehicles
        $vehicle_count = count(get_posts(['post_type' => 'vehicle', 'posts_per_page' => -1]));

        // Return the result as a JSON response
        wp_send_json([
            'status' => 'success',
            'count' => $vehicle_count
        ]);
    }

    public function adminActionsList()
    {
        $actions = [
            'delete_all_vehicles' => 'Delete All Vehicles',
            'refresh_files' => 'Refresh Files',
            'get_all_headers' => 'Get All Headers',
        ];

        $out = '<p>';

        foreach ($actions as $action => $description) {
            $out .= '<a href="' . admin_url('admin.php?page=' . VIN_IMPORTER . '-tools&action=' . $action) . '" class="button">' . $description . '</a> ';
        }

        $out .= '</p>';

        echo $out;
    }

    /**
     * Check if file exists, get file info, get header row, get template match.
     * Sets $file, $file_header, $template
     *
     * @return void
     */
    public function adminFileCheck()
    {
        if (!isset($_GET['file'])) {
            return false;
        }

        // header with inline button
        echo '<h3 class="wp-heading-inline">File Info</h3>';
        echo '<a href="' . admin_url('admin.php?page=' . VIN_IMPORTER . '-tools&file=' . $_GET['file'] . '&action=import') . '" class="button-primary">Import</a>';

        $file = $_GET['file'] ?? null;
        $file_path = $this->library[$file];
        $file_info = pathinfo($file_path);

        if (!file_exists($file_path)) {
            echo '✘ File does not exist.';
            return false;
        }

        // get file info
        $this->file_data = $this->Files->getData($file_path);
        $file_size = size_format(filesize($file_path), 2);
        $file_date = date('Y-m-d H:i:s', filemtime($file_path));

        // do we have a template match?
        $templates = new Templates();
        $this->template = $templates->get($this->file_header_hash);

        echo '<pre>';
        echo 'Header Hash: ' . $this->file_header_hash . PHP_EOL;
        echo 'Template: ' . $this->template['description'] . PHP_EOL;
        echo '</pre>';

        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<tr>';
        echo '<th>File</th>';
        echo '<th>Size</th>';
        echo '<th>Date</th>';
        echo '<th>Rows</th>';
        echo '<th>Columns</th>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>' . $file_info['basename'] . '</td>';
        echo '<td>' . $file_size . '</td>';
        echo '<td>' . $file_date . '</td>';
        echo '<td>' . count($this->file_data) . '</td>';
        echo '<td>' . count($this->file_data[0]) . '</td>';
        echo '</tr>';
        echo '</table>';

        echo '<br>';
    }

    public function adminFileImport()
    {
        $import = $this->fileImport();

        // Vehicles importer
        $out = '✔︎ Vehicles Imported' . '<br>';

        foreach ($import as $key => $value) {
            if (!$value) {
                continue;
            }
            $out .= ucfirst($key) . ' ' . '<strong>' . $value . '</strong>' . $this->actions_separator;
        }

        $out = rtrim($out, $this->actions_separator);
        $this->adminNotice($out, 'notice-success');
    }

    public function adminFileInfo()
    {
        // if template is found, show it
        if (isset($this->template)) {
            echo '<h3>' . $this->template['name'] . '</h3>';

            echo '<div style="display: flex;">';
            echo '<div style="width: 33%;">';

            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<tr>';
            echo '<th>Input</th>';
            echo '<th>Ouput</th>';
            echo '</tr>';

            foreach ($this->template['template'] as $key => $value) {
                if (is_array($value)) {
                    $match = array_intersect($value, $this->file_header);
                    echo '<tr>';
                    echo '<td>' . implode(',', $match) . '</td>';
                    echo '<td>' . $key . '</td>';
                    echo '</tr>';
                } else {
                    echo '<tr>';
                    echo '<td>' . $key . '</td>';
                    echo '<td>' . $value . '</td>';
                    echo '</tr>';
                }
            }

            echo '</table>';

            echo '</div>';  // end 33%
            echo '</div>';  // end flex
        }
    }

    public function adminFilesGetHeaders()
    {
        if (!isset($this->library)) {
            $this->library = $this->Files->getAll();
        }

        // output first row of all files
        foreach ($this->library as $key => $file) {
            $file_path = $this->library[$key];
            $file_handle = fopen($file_path, 'r');
            $file_data = fgetcsv($file_handle);
            fclose($file_handle);

            // base file name
            $file_info = pathinfo($file_path);
            echo $file_info['basename'] . PHP_EOL;
            echo implode(',', $file_data) . PHP_EOL;
            echo PHP_EOL;
        }
    }

    public function adminFilesRefresh()
    {
        delete_transient('vin_importer_files');
        $this->library = $this->Files->refresh();
        $this->adminNotice('Files refreshed.', 'notice-success');
    }

    public function adminNotice($message, $notice = 'notice-success')
    {
        echo '<div class="notice ' . $notice . ' is-dismissible">';
        echo '<p>' . $message . '</p>';
        echo '</div>';
    }

    public function adminPossibleFileList()
    {
        $this->library = $this->Files->getAll();

        echo '<p>' . (count($this->library) === 0 ? '🟡' : '🟢') . ' Files Found: ' . count($this->library) . '</p>';

        echo '<table class="wp-list-table widefat fixed striped">';

        echo '<tr>';
        echo '<th style="width: 200px;">File</th>';
        echo '<th style="width: 100px;">Size</th>';
        echo '<th style="width: 200px;">Date</th>';
        echo '<th style="width: 100px;">Action</th>';
        echo '</tr>';

        foreach ($this->library as $key => $file) {
            $file_info = pathinfo($file);
            $file_size = size_format(filesize($file), 2);
            $file_date = date('Y-m-d H:i:s', filemtime($file));

            echo '<tr>';
            // on hover, show full path
            echo '<td title="' . $file_info['dirname'] . '">' . $file_info['basename'] . '</td>';
            echo '<td>' . $file_size . '</td>';
            echo '<td>' . $file_date . '</td>';
            echo '<td>';
            echo '<a href="' . admin_url('admin.php?page=' . VIN_IMPORTER . '-tools&file=' . $key . '&action=import') . '">Import</a> • ';
            echo '<a href="' . admin_url('admin.php?page=' . VIN_IMPORTER . '-tools&file=' . $key . '&action=check') . '">Check</a></td>';
            echo '</tr>';
        }

        echo '</table>';
    }

    public function adminVehicleCount()
    {
        // $vehicle_count = count(get_posts(['post_type' => 'vehicle', 'posts_per_page' => -1]));
        // echo '<p>' . ($vehicle_count === 0 ? '🟨' : '🟩') . ' Vehicle Count: ' . $vehicle_count . '</p>';
        echo '<div id="vehicle-count"></div>'; // Placeholder for the AJAX result

    }

    public function adminVehiclesDelete()
    {
        $vehicles = get_posts(['post_type' => 'vehicle', 'posts_per_page' => -1]);
        foreach ($vehicles as $vehicle) {
            wp_delete_post($vehicle->ID, true);
        }

        if (count(get_posts(['post_type' => 'vehicle', 'posts_per_page' => -1])) === 0) {
            // add this to admin notice
            $out = '✔︎ All vehicles deleted.';
            $notice = 'notice-success';
        } else {
            $out = '✘ Error deleting vehicles.';
            $notice = 'notice-error';
        }

        $this->adminNotice($out, $notice);
    }

    public function allowUploadMimes($mimes)
    {
        $mimes['csv'] = 'text/csv';
        $mimes['tsv'] = 'text/tab-separated-values';
        $mimes['json'] = 'application/json';
        $mimes['xml'] = 'application/xml';

        return $mimes;
    }

    public function fileImport()
    {
        // get file_data
        $file_data = $this->file_data;
        $template = $this->template['template'];

        // remove header row
        // $file_data = array_slice($file_data, 1);
        unset($file_data[0]);

        // loop through data
        $vehicles_added = [];
        $vehicles_updated = [];
        foreach ($file_data as $data) {
            $vehicle = [];
            foreach ($template as $key => $value) {
                if (is_string($value)) {
                    $vehicle[$value] = $data[array_search($key, $this->file_header)];
                } elseif (is_array($value)) {
                    $match = array_intersect($value, $this->file_header);
                    $vehicle[$key] = $data[array_search($match[0], $this->file_header)];
                }
            }

            // check if vin exists
            $vin_exists = get_posts(['post_type' => 'vehicle', 'meta_key' => 'vin', 'meta_value' => $vehicle['vin']]);

            // TODO: Add setting to skip or update existing vehicles
            if (count($vin_exists) > 0) {
                $vehicle_id = $vin_exists[0]->ID;
                wp_update_post([
                    'ID' => $vehicle_id,
                    'post_title' => $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'],
                ]);
                $vehicles_updated[] = $vehicle_id;
            } else {
                // insert vehicle
                $vehicle_id = wp_insert_post([
                    'post_type' => 'vehicle',
                    'post_title' => $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'],
                    'post_status' => 'publish',
                ]);
                $vehicles_added[] = $vehicle_id;
            }

            // add meta
            foreach ($vehicle as $key => $value) {
                update_post_meta($vehicle_id, $key, $value);
            }

            // add taxonomy
            wp_set_object_terms($vehicle_id, $vehicle['make'], 'make');
            wp_set_object_terms($vehicle_id, $vehicle['model'], 'model');
            wp_set_object_terms($vehicle_id, $vehicle['trim'], 'trim');
            wp_set_object_terms($vehicle_id, $vehicle['year'], 'year');
        }

        return
            [
                'added' => count($vehicles_added),
                'updated' => count($vehicles_updated),
            ];
    }
}
