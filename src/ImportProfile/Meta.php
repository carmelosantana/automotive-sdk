<?php

declare(strict_types=1);

namespace WipyAutos\AutomotiveSdk\ImportProfile;

use WipyAutos\AutomotiveSdk\Admin\Files;
use WipyAutos\AutomotiveSdk\Import\Mapping;
use WipyAutos\AutomotiveSdk\Vehicle\Fields as VehicleFields;

class Meta
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'registerMetaBox']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('save_post_import-profile', [$this, 'saveMetaBox']);
        add_action('wp_ajax_get_file_headers', [$this, 'getFileHeaders']);
        add_action('wp_ajax_get_universal_mapping', [$this, 'getUniversalMapping']);
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

        // Register meta box for CSV to Taxonomy Mapping
        add_meta_box(
            'import_profile_taxonomy_mapping',
            __('Taxonomy Mapping', 'wp-autos'),
            [$this, 'renderTaxonomyMetaBox'],
            'import-profile',
            'normal',
            'high'
        );

        // Register meta box for CSV to Meta Mapping
        add_meta_box(
            'import_profile_mapping',
            __('Meta Mapping', 'wp-autos'),
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
        <p><?php _e('Select one or more CSV files for mapping.', 'wp-autos'); ?></p>
        <select name="csv_file[]" id="csv_file" multiple="multiple" style="width: 100%;">
            <option value=""><?php _e('Select files', 'wp-autos'); ?></option>
            <?php foreach ($files as $md5 => $path): ?>
                <option value="<?php echo esc_attr($md5); ?>" <?php echo in_array($md5, $selected_files) ? 'selected' : ''; ?>>
                    <?php echo esc_html(basename($path)); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p><small><?php _e('Changing files will reset selections and load new options.', 'wp-autos'); ?></small></p>
    <?php
    }

    /**
     * Renders the meta box for CSV to Taxonomy Mapping.
     */
    public function renderTaxonomyMetaBox(\WP_Post $post): void
    {
        // Retrieve the saved taxonomy mapping
        $taxonomy_mapping = get_post_meta($post->ID, '_csv_taxonomy_mapping', true) ?: [];

        // Get the taxonomies dynamically from the Fields class
        $taxonomies = \WipyAutos\AutomotiveSdk\Vehicle\Fields::getTaxonomies();

    ?>
        <table class="form-table">
            <tbody id="taxonomy-mapping">
                <?php foreach ($taxonomies as $taxonomy): ?>
                    <tr>
                        <td><?php echo esc_html($taxonomy['label']); ?></td>
                        <td><code><?php echo esc_html($taxonomy['name']); ?></code></td>
                        <td>
                            <select name="csv_taxonomy_mapping[<?php echo esc_attr($taxonomy['name']); ?>][csv]" class="widefat taxonomy-dropdown">
                                <?php
                                // Display the saved value if it exists
                                $selected = $taxonomy_mapping[$taxonomy['name']]['csv'] ?? '';
                                $selected = esc_attr($selected);

                                if (!empty($selected)) {
                                    echo '<option value="' . $selected . '" selected>' . $selected . '</option>';
                                }
                                ?>
                                <option value=""><?php _e('Select a CSV column', 'wp-autos'); ?></option>
                                <!-- Options populated dynamically -->
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php
    }

    /**
     * Renders the meta box for CSV to Meta Mapping.
     */
    public function renderMetaBox(\WP_Post $post): void
    {
        $mapping = get_post_meta($post->ID, '_csv_meta_mapping', true) ?: [];
        $fields = VehicleFields::getMetas();
    ?>
        <table class="form-table">
            <!-- <thead>
                <tr>
                    <th><?php _e('Field', 'wp-autos'); ?></th>
                    <th><?php _e('Meta', 'wp-autos'); ?></th>
                    <th><?php _e('CSV Column', 'wp-autos'); ?></th>
                </tr>
            </thead> -->
            <tbody id="meta-mapping">
                <?php
                foreach ($fields as $section):
                ?>
                    <tr>
                        <td colspan="3">
                            <strong><?php echo esc_html($section['description']); ?></strong>
                        </td>
                    </tr>
                    <?php
                    foreach ($section['fields'] as $field):
                        $meta_key = $field['name'];
                    ?>
                        <tr>
                            <td><?php echo esc_html($field['label']); ?></td>
                            <td><code><?php echo esc_html($meta_key); ?></code></td>
                            <td>
                                <select name="csv_meta_mapping[<?php echo esc_attr($meta_key); ?>][csv]" class="widefat meta-dropdown">
                                    <?php
                                    // Display the saved value if it exists
                                    $selected = $mapping[$meta_key]['csv'] ?? '';
                                    $selected = esc_attr($selected);

                                    if (!empty($selected)) {
                                        echo '<option value="' . $selected . '" selected>' . $selected . '</option>';
                                    }
                                    ?>
                                    <option value=""><?php _e('Select a CSV column', 'wp-autos'); ?></option>
                                    <!-- Options populated dynamically -->
                                </select>
                                <?php
                                // Handle extra fields for photo_urls and options
                                if ($meta_key === 'photo_urls' or $meta_key === 'options') {
                                    echo '<input type="text" name="csv_meta_mapping[' . $meta_key . '][delimiter]" value="' . ($mapping[$meta_key]['delimiter'] ?? '') . '" placeholder="Delimiter" class="small" />';
                                }

                                if ($meta_key === 'options') {
                                    echo '<input type="text" name="csv_meta_mapping[' . $meta_key . '][encase]" value="' . ($mapping[$meta_key]['encase'] ?? '') . '" placeholder="Encase Character" class="small" />';
                                }

                                if ($meta_key === 'photo_urls') {
                                    echo '<label><input type="checkbox" name="csv_meta_mapping[' . $meta_key . '][download]" value="1" ' . checked($mapping[$meta_key]['download'] ?? false, true, false) . ' /> ' . __('Download images', 'wp-autos') . '</label>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
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
        if (isset($_POST['csv_meta_mapping']) && is_array($_POST['csv_meta_mapping'])) {
            $csv_meta_mapping = array_map(function ($mapping) {
                // Sanitize and allow empty values
                $mapping['csv'] = sanitize_text_field($mapping['csv']);
                return $mapping;
            }, $_POST['csv_meta_mapping']);

            // Process extra fields like delimiter and encase, if they exist
            $csv_meta = [
                'delimiter',
                'encase',
                'download',
            ];
            foreach ($csv_meta as $meta) {
                if (isset($_POST['csv_meta_mapping'][$meta])) {
                    $csv_meta_mapping[$meta] = sanitize_text_field($_POST['csv_meta_mapping'][$meta]);
                }
            }

            update_post_meta($post_id, '_csv_meta_mapping', $csv_meta_mapping);
        }

        // Save CSV to Taxonomy mapping
        if (isset($_POST['csv_taxonomy_mapping']) && is_array($_POST['csv_taxonomy_mapping'])) {
            $csv_taxonomy_mapping = array_map(function ($mapping) {
                // Sanitize and allow empty values
                $mapping['csv'] = sanitize_text_field($mapping['csv']);
                return $mapping;
            }, $_POST['csv_taxonomy_mapping']);
            update_post_meta($post_id, '_csv_taxonomy_mapping', $csv_taxonomy_mapping);
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
            $file = new \WipyAutos\AutomotiveSdk\Admin\File();
            $file->load($file_hash);  // Load the file using its hash

            if ($file->isLoaded()) {
                // Group headers by file base name
                $file_name = basename($file->getFilePath());
                $headers_by_file[$file_name] = $file->getHeader();  // Assume getHeader() returns an array of headers
            }
        }

        wp_send_json_success(['headers_by_file' => $headers_by_file]);  // Return headers grouped by file base name
    }

    /**
     * AJAX handler to return the universal mapping for pre-selecting fields.
     */
    public function getUniversalMapping(): void
    {
        $Mapping = new Mapping();
        wp_send_json_success($Mapping::universalMapping());
    }
}
