<?php

declare(strict_types=1);

namespace WipyAutos\AutomotiveSdk\Admin;

use WipyAutos\AutomotiveSdk\Admin\File;
use WipyAutos\AutomotiveSdk\Admin\Files;
use WipyAutos\AutomotiveSdk\Import\Csv;
use WipyAutos\AutomotiveSdk\Import\Mapping;

class PageImport extends Page
{
    protected $page_slug = 'import';
    protected $page_title = 'Import';
    protected $menu_title = 'Import';
    protected $page_description = 'Utilities for interacting with multiple vehicle datasets. Files uploaded to your media library are filtered and listed below.';
    protected $sub_menu_position = 5;

    protected $tab_actions = [
        'import' => 'Import',
        'tools' => 'Tools',
    ];

    public $actions_separator = ' • ';

    public File $file;

    public $Files;

    public function __construct()
    {
        parent::__construct();

        add_action('admin_enqueue_scripts', [$this, 'adminAdditionalScripts']);
        add_action('admin_footer', [$this, 'adminInlineJs']);
        add_action('wp_ajax_get_vehicle_count', [$this, 'adminVehicleCountAjax']);
        add_action('wp_ajax_nopriv_get_vehicle_count', [$this, 'adminVehicleCountAjax']); // for non-logged-in users
        add_action('wp_ajax_start_vehicle_import', [$this, 'startVehicleImportAjax']);
        add_action('wp_ajax_process_vehicle_import_batch', [$this, 'processVehicleImportBatchAjax']);

        $this->Files = new Files();
    }

    public function adminAdditionalScripts(): void
    {
        wp_enqueue_script('vehicles-import-js', ASDK_DIR_URL . 'assets/js/vehicles-import.js', ['jquery'], null, true);
        wp_localize_script('vehicles-import-js', 'vehiclesImport', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);
    }

    /**
     * Start the vehicle import process and return the total number of rows.
     */
    public function startVehicleImportAjax(): void
    {
        check_ajax_referer('vehicle_import_nonce', 'nonce');

        if (!$this->adminLoadFile()) {
            wp_send_json_error('File not found.', 400);
        }

        $file = $_REQUEST['file'] ?? null;
        $profile = $_REQUEST['profile'] ?? 'universal'; // Default to universal if no profile is provided

        $import = new Csv();
        $import->setFile($file);

        // Fetch mapping based on profile
        if (is_numeric($profile)) {
            $mapping = (new Mapping())->getMapping((int)$profile);
        } else {
            $mapping = Mapping::universalMapping();
        }

        $import->setMapping($mapping); // Apply the mapping to the import process

        wp_send_json_success(['total' => $this->file->getTotalRows()]);
    }

    /**
     * Process a batch of vehicles for import.
     */
    public function processVehicleImportBatchAjax(): void
    {
        check_ajax_referer('vehicle_import_nonce', 'nonce');

        if (!$this->adminLoadFile()) {
            wp_send_json_error('File not found.', 400);
        }

        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
        $profile = $_REQUEST['profile'] ?? 'universal';

        $import = new Csv();
        $import->setFile($_REQUEST['file']);

        // Fetch mapping based on profile
        if (is_numeric($profile)) {
            $mapping = (new Mapping())->getMapping((int)$profile);
        } else {
            $mapping = Mapping::universalMapping();
        }

        $import->setMapping($mapping); // Apply the mapping to the import process

        $results = $import->fileImport($offset, $limit);
        wp_send_json_success($results);
    }

    public function adminContent(): void
    {
        // switch tabs
        switch ($this->current_tab) {
            case 'tools':
                $tools = new PageImportTools();
                $tools->adminContent();
                break;

            default:
                $this->doAction();
                break;
        }
    }

    public function doAction()
    {
        switch ($_GET['action'] ?? null) {
            case 'preview':
                $this->adminFileHeader();
                $this->adminFileView();
                break;

            case 'header':
                $this->adminFileInfo();
                break;

            default:
                $this->adminPossibleFileList();
                break;
        }
    }

    public function adminCounts(): void
    {
        echo '<div>';
        echo '<ul class="subsubsub">';
        echo '<li class="all"><a href="' . $this->generatePageUrl('import') . '">Files</a> (' . count($this->Files->getAll()) . ')</li>';
        echo '<li class="published">';
        echo '<a href="' . admin_url('edit.php?post_type=vehicle') . '">Vehicles</a> ';
        echo '<span class="count" id="vehicle-count">Loading...</span>';
        echo '</li>';
        echo '</ul>';
        echo '</div>';
        echo '<div class="clear"></div>';
    }

    public function adminLoadFile(): bool
    {
        $file = $_REQUEST['file'] ?? null;

        if (!$file) {
            return false;
        }

        $this->file = new File();
        $this->file->load($file);

        if (!$this->file->isLoaded()) {
            return false;
        }

        return true;
    }

    public function adminFileHeader()
    {
        if (!$this->adminLoadFile()) {
            echo '✘ File not found.';
            return;
        }

        $file = $_GET['file'] ?? null;
        $file = sanitize_text_field($file);

        echo '<h3>' . esc_html($this->file->getFileName());

        // make-mapping
        $custom_url = add_query_arg([
            'post_type' => 'import-profile',
            'file' => $file,
        ], admin_url('post-new.php'));


        $import_profiles = get_posts([
            'post_type' => 'import-profile',
            'posts_per_page' => -1,
        ]);

        echo ' <select name="import_profile" id="import_profile">';
        echo '<option value="">Select Import Profile</option>';
        echo '<optgroup label="System">';
        echo '<option value="universal">Universal</option>';
        echo '<optgroup label="Posts">';
        foreach ($import_profiles as $profile) {
            echo '<option value="' . $profile->ID . '">' . $profile->post_title . '</option>';
        }
        echo '</optgroup>';
        echo '</select>';

        echo ' <a href="#" class="start-import-link button-primary" data-file="' . esc_attr($file) . '" data-nonce="' . wp_create_nonce('vehicle_import_nonce') . '">Import</a>';
        echo ' <a href="' . esc_url($custom_url) . '" class="button">Make Mapping</a>';
        echo '</h3>';

        echo '<pre>';
        echo 'Header Hash: ' . $this->file->getHeaderHash() . PHP_EOL;
        echo 'Template: ' . $this->file->getTemplate()['name'] . PHP_EOL;
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
        echo '<td>' . $this->file->getFileName() . '</td>';
        echo '<td>' . size_format($this->file->getFileSize(), 2) . '</td>';
        echo '<td>' . date('Y-m-d H:i:s', $this->file->getFileModificationDate()) . '</td>';
        echo '<td>' . $this->file->getTotalRows() . '</td>';
        echo '<td>' . $this->file->getTotalColumns() . '</td>';
        echo '</tr>';
        echo '</table>';

        echo '<br>';
    }

    public function adminFileImport()
    {
        if (!$this->adminLoadFile()) {
            echo '✘ File not found.';
            return;
        }

        $file = $_GET['file'] ?? null;
        $import = new Csv();
        $import = $import->setFile($file);
        $results = $import->fileImport();

        if (!$results) {
            $this->adminNotice('✘ Error importing file.', 'notice-error');
            return;
        }

        // Vehicles importer
        $out = '✔︎ Vehicles ';

        foreach ($results as $key => $value) {
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
        if (!$this->adminLoadFile()) {
            echo '✘ File not found.';
            return;
        }

        if ($this->file->getTemplate()) {
            echo '<h3>' . $this->file->getTemplate()['name'] . '</h3>';

            echo '<div style="display: flex;">';
            echo '<div style="width: 33%;">';

            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<tr>';
            echo '<th>Input</th>';
            echo '<th>Ouput</th>';
            echo '</tr>';

            foreach ($this->file->getTemplate()['template'] as $key => $value) {
                if (is_array($value)) {
                    $match = array_intersect($value, $this->file->getHeader());
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

    /**
     * Display a paginated view of a CSV file.
     * Fetches and displays the CSV data in a paginated table format.
     *
     * @return void
     */
    public function adminFileView(): void
    {
        if (!$this->adminLoadFile()) {
            echo '✘ File not found.';
            return;
        }

        // Define pagination variables
        $per_page = 10;  // Rows per page
        $current_page = max(0, isset($_GET['paged']) ? (int)$_GET['paged'] : 1);  // Current page number

        // Get paginated file data
        $file_data = $this->Files->getData($this->file->getFilePath(), ',', $current_page, $per_page);
        if (!$file_data) {
            echo '✘ Error loading file data.';
            return;
        }

        // Retrieve total rows and calculate total pages
        $total_items = count($this->Files->getData($this->file->getFilePath()));  // Get the full data to calculate total rows
        // Remove 1 for header row
        $total_items--;

        $this->renderPagination($total_items, $per_page, $current_page, $file_data);

        echo '<div class="scrollwrapper">';
        echo '<table class="wp-list-table widefat fixed striped" style="width: auto;">';
        echo '<style>';
        echo 'table.wp-list-table thead th { position: sticky; top: 2em; background: #fff; z-index: 1; }';
        echo '</style>';
        echo '<thead><tr>';

        foreach ($this->file->getHeader() as $header) {
            echo '<th>' . esc_html($header) . '</th>';
        }
        echo '</tr></thead><tbody>';

        // Output the paginated rows
        foreach (array_slice($file_data, 1) as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                // trim cell to 150 characters
                $cell = substr($cell, 0, 150);
                echo '<td>' . esc_html($cell) . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</div>';

        $this->renderPagination($total_items, $per_page, $current_page, $file_data);
    }

    public function renderPagination($total_items, $per_page, $current_page, $file_data = []): void
    {
        $total_pages = ceil($total_items / $per_page);

        if ($total_items === (count($file_data) - 1)) {
            echo '<p>Displaying ' . esc_html($total_items) . ' rows.</p>';
        } else {
            echo '<p>Displaying ' . count($file_data) . ' of ' . esc_html($total_items) . ' rows.</p>';

            echo paginate_links([
                'base' => $this->generatePageUrl('', ['action' => 'import', 'file' => $_GET['file']]),
                'format' => '&paged=%#%',
                'current' => $current_page,
                'total' => $total_pages,
            ]);
        }
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
                            document.getElementById('vehicle-count').innerHTML = '(' + data.count + ')';
                        }
                    });
            }
        </script>
<?php
    }

    public function adminPossibleFileList()
    {
        echo '<div class="scrollwrapper">';
        echo '<table class="wp-list-table widefat fixed striped" style="width: 100%;">';
        echo '<thead>';
        echo '<tr>';
        echo '<th style="width: 200px;">File</th>';
        echo '<th style="width: 100px;">Size</th>';
        echo '<th style="width: 200px;">Date</th>';
        echo '<th style="width: 100px;">Rows</th>';
        echo '<th style="width: 100px;">Columns</th>';
        echo '</tr>';
        echo '</thead><tbody>';

        $row_actions = [
            'preview' => [
                'label' => 'Import',
            ],
            'header' => [
                'label' => 'Header',
            ],
            'make-mapping' => [
                'label' => 'Make Mapping',
                'url' => 'post-new.php',
                'post_type' => 'import-profile',  // Adjusted to point to the correct post type
            ],
        ];

        foreach ($this->Files->getAll() as $key => $file) {
            $file = new File();
            $file->load($key);

            echo '<tr>';
            echo '<td>' . esc_html($file->getFileName()) . '<br>';
            echo '<div class="row-actions">';

            // Generate the import link
            $row = '';
            $nonce = wp_create_nonce('vehicle_import_nonce');
            // $import_link = '<a href="#" class="start-import-link" data-file="' . esc_attr($key) . '" data-nonce="' . esc_attr($nonce) . '">Import</a> | ';
            // $row = $import_link;
            foreach ($row_actions as $action => $data) {
                $args = [
                    'action' => $action,
                    'file' => $key,  // Pass the file hash as a query parameter
                    'nonce' => wp_create_nonce($action . $key),
                ];

                // Check if the 'url' key is set, use the provided URL
                if (isset($data['url'])) {
                    // Use the provided URL and append necessary query parameters like the file hash
                    $custom_url = add_query_arg([
                        'post_type' => $data['post_type'] ?? '',  // Ensure post_type is set if needed
                        'file' => $key,  // File hash
                    ], admin_url($data['url']));

                    $row .= '<a href="' . esc_url($custom_url) . '" class="' . esc_attr($data['class'] ?? '') . '">' . esc_html($data['label']) . '</a> | ';
                } else {
                    // Fallback to the default URL generation if no custom URL is set
                    $row .= '<a href="' . esc_url($this->generateTabUrl('import', $args)) . '" class="' . esc_attr($data['class'] ?? '') . '">' . esc_html($data['label']) . '</a> | ';
                }
            }
            $row = rtrim($row, ' | ');
            echo $row;

            echo '</div>';
            echo '</td>';
            echo '<td>' . size_format($file->getFileSize(), 2) . '</td>';
            echo '<td>' . date('Y-m-d H:i:s', $file->getFileModificationDate()) . '</td>';
            echo '<td>' . $file->getTotalRows() . '</td>';
            echo '<td>' . $file->getTotalColumns() . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
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
}
