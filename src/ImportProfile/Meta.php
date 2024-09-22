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
        add_action('save_post_import_profile', [$this, 'saveMetaBox']);
        add_action('save_post_import_profile', [$this, 'saveFile']);
    }

    /**
     * Registers the meta boxes for Import Profile posts.
     */
    public function registerMetaBox(): void
    {
        // Register metabox on the side for files
        add_meta_box(
            'import_files',
            __('CSV Files', 'wp-autos'),
            [$this, 'renderFilesMetaBox'],
            'import-profile',
            'side',
            'default'
        );

        // Register meta box for vehicle mapping
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
     * Renders the file selection meta box with support for multiple file selection.
     *
     * @param \WP_Post $post The post object.
     */
    public function renderFilesMetaBox(\WP_Post $post): void
    {
        $files_instance = new Files();
        $files = $files_instance->getAll();

        // Get the saved file(s) from post meta
        $selected_files = get_post_meta($post->ID, '_csv_file', true) ?: [];

        // If a file query parameter is provided, automatically select the file
        $file_from_query = $_GET['file'] ?? '';
        if ($file_from_query && !in_array($file_from_query, $selected_files)) {
            $selected_files[] = $file_from_query;
        }

        // Render the select box for multiple file selection
?>
        <select name="csv_file[]" multiple="multiple" style="width: 100%;">
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
     *
     * @param \WP_Post $post The post object.
     */
    public function renderMetaBox(\WP_Post $post): void
    {
        // Get the saved mapping from post meta
        $mapping = get_post_meta($post->ID, '_csv_meta_mapping', true) ?: [];

        // Get vehicle fields
        $vehicleFields = new VehicleFields();
        $fields = $vehicleFields->getFields();

        // Assume the selected file headers are already loaded
        $csv_headers = ['Header1', 'Header2', 'Header3']; // Placeholder

        // Render the table with meta fields and CSV headers
    ?>
        <table class="form-table">
            <thead>
                <tr>
                    <th><?php _e('Meta Field', 'wp-autos'); ?></th>
                    <th><?php _e('CSV Column', 'wp-autos'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fields as $meta_key => $meta_info): ?>
                    <tr>
                        <td><?php echo esc_html($meta_key); ?></td>
                        <td>
                            <select name="csv_meta_mapping[<?php echo esc_attr($meta_key); ?>][csv]" class="widefat">
                                <option value=""><?php _e('Select a CSV column', 'wp-autos'); ?></option>
                                <?php foreach ($csv_headers as $header): ?>
                                    <option value="<?php echo esc_attr($header); ?>" <?php selected($mapping[$meta_key]['csv'] ?? '', $header); ?>>
                                        <?php echo esc_html($header); ?>
                                    </option>
                                <?php endforeach; ?>
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
     *
     * @param int $post_id The post ID.
     */
    public function saveMetaBox(int $post_id): void
    {
        if (array_key_exists('csv_meta_mapping', $_POST)) {
            update_post_meta($post_id, '_csv_meta_mapping', $_POST['csv_meta_mapping']);
        }
    }

    /**
     * Saves the selected files when the post is saved.
     *
     * @param int $post_id The post ID.
     */
    public function saveFile(int $post_id): void
    {
        if (array_key_exists('csv_file', $_POST)) {
            update_post_meta($post_id, '_csv_file', $_POST['csv_file']);
        }
    }
}
