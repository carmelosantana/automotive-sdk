<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\ImportProfile;

use WpAutos\AutomotiveSdk\Admin\Files;
use WpAutos\AutomotiveSdk\Api\Vehicles\VehicleFields;

class Meta
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'registerMetaBox']);
        add_action('save_post_import-profile', [$this, 'saveMetaBox']);
        add_action('wp_ajax_get_file_headers', [$this, 'getFileHeaders']); // AJAX handler
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']); // Enqueue JavaScript
    }

    /**
     * Registers the meta boxes for Import Profile posts.
     */
    public function registerMetaBox(): void
    {
        // Register meta box for file selection
        add_meta_box(
            'import_files',
            __('CSV Files', 'wp-autos'),
            [$this, 'renderFilesMetaBox'],
            'import-profile',
            'side',
            'default'
        );

        // Register meta box for CSV to Meta Mapping
        add_meta_box(
            'import_profile_mapping',
            __('CSV to Meta Mapping', 'wp-autos'),
            [$this, 'renderMetaBox'],
            'import-profile',
            'normal',
            'high'
        );
    }

    /**
     * Enqueues JavaScript for handling file changes and AJAX requests.
     */
    public function enqueueScripts(): void
    {
        wp_enqueue_script('meta-ajax-script', plugins_url('/assets/js/meta-import-profile.js', ASDK__FILE__), ['jquery'], null, true);
        wp_localize_script('meta-ajax-script', 'metaAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);
    }

    /**
     * Renders the file selection meta box.
     */
    public function renderFilesMetaBox(\WP_Post $post): void
    {
        $files_instance = new Files();
        $files = $files_instance->getAll();

        // Get the saved files from post meta (allow multiple selections)
        $selected_files = get_post_meta($post->ID, '_csv_file', true) ?: [];

?>
        <select name="csv_file[]" id="csv_file" multiple="multiple" style="width: 100%;">
            <option value=""><?php _e('Select files', 'wp-autos'); ?></option>
            <?php foreach ($files as $md5 => $path): ?>
                <option value="<?php echo esc_attr($md5); ?>" <?php echo in_array($md5, $selected_files) ? 'selected' : ''; ?>>
                    <?php echo esc_html(basename($path)); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p><?php _e('Select one or more CSV files for mapping.', 'wp-autos'); ?></p>
    <?php
    }

    /**
     * Renders the meta box for CSV to Meta Mapping.
     */
    public function renderMetaBox(\WP_Post $post): void
    {
        $mapping = get_post_meta($post->ID, '_csv_meta_mapping', true) ?: [];
        $vehicleFields = new VehicleFields();
        $fields = $vehicleFields->getFields();
    ?>
        <table class="form-table">
            <thead>
                <tr>
                    <th><?php _e('Meta Field', 'wp-autos'); ?></th>
                    <th><?php _e('CSV Column', 'wp-autos'); ?></th>
                </tr>
            </thead>
            <tbody id="meta-mapping">
                <?php foreach ($fields as $meta_key => $meta_info): ?>
                    <tr>
                        <td><?php echo esc_html($meta_key); ?></td>
                        <td>
                            <select name="csv_meta_mapping[<?php echo esc_attr($meta_key); ?>][csv]" class="widefat meta-dropdown">
                                <?php
                                $selected = $mapping[$meta_key]['csv'] ?? '';
                                $selected = esc_attr($selected);

                                // if not empty, add the selected attribute
                                if (!empty($selected)) {
                                    echo '<option value="' . $selected . '" selected>' . $selected . '</option>';
                                }
                                ?>
                                <option value=""><?php _e('Select a CSV column', 'wp-autos'); ?></option>
                                <!-- This will be populated dynamically based on selected file(s) -->
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
<?php
    }

    /**
     * Saves the meta box data when the post is saved.
     */
    public function saveMetaBox(int $post_id): void
    {
        if (!isset($_POST['post_type']) or 'import-profile' !== $_POST['post_type']) {
            return;
        }

        // Save selected files
        if (isset($_POST['csv_file']) and is_array($_POST['csv_file'])) {
            $csv_file = array_map('sanitize_text_field', $_POST['csv_file']);
            update_post_meta($post_id, '_csv_file', $csv_file);
        }

        // Save CSV to Meta mapping
        if (isset($_POST['csv_meta_mapping']) and is_array($_POST['csv_meta_mapping'])) {
            $csv_meta_mapping = array_map(function ($mapping) {
                return array_map('sanitize_text_field', $mapping);
            }, $_POST['csv_meta_mapping']);

            update_post_meta($post_id, '_csv_meta_mapping', $csv_meta_mapping);
        }
    }

    /**
     * AJAX handler to get file headers.
     */
    public function getFileHeaders(): void
    {
        if (!isset($_POST['files']) || empty($_POST['files'])) {
            wp_send_json_error('No files selected');
            return;
        }

        $files = (array) $_POST['files'];  // Get the array of file hashes
        $headers_by_file = [];

        foreach ($files as $file_hash) {
            $file = new \WpAutos\AutomotiveSdk\Admin\File();
            $file->load($file_hash);  // Load the file using its hash

            if ($file->isLoaded()) {
                // Group headers by file base name
                $file_name = basename($file->getFilePath());
                $headers_by_file[$file_name] = $file->getHeader();  // Assume getHeader() returns an array of headers
            }
        }

        wp_send_json_success(['headers_by_file' => $headers_by_file]);  // Return headers grouped by file base name
    }
}
