<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\ImportProfile;

use WpAutos\AutomotiveSdk\Api\Vehicles\VehicleFields;

class Meta
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'registerMetaBox']);
        add_action('save_post_import_profile', [$this, 'saveMetaBox']);
    }

    /**
     * Registers a meta box for Import Profile posts.
     */
    public function registerMetaBox(): void
    {
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
     * Renders the meta box content.
     * This is where we’ll dynamically load vehicle fields for CSV mapping.
     *
     * @param \WP_Post $post The post object.
     */
    public function renderMetaBox(\WP_Post $post): void
    {
        // Get the saved mapping from post meta
        $mapping = get_post_meta($post->ID, '_csv_meta_mapping', true) ?: [];

        // Dynamically get vehicle fields
        $vehicleFields = new VehicleFields();
        $fields = $vehicleFields->getFields();

        // Render the meta box content
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
                            <input type="text" name="csv_meta_mapping[<?php echo esc_attr($meta_key); ?>][csv]"
                                value="<?php echo esc_attr($mapping[$meta_key]['csv'] ?? ''); ?>"
                                placeholder="<?php _e('CSV Header', 'wp-autos'); ?>">
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
}
