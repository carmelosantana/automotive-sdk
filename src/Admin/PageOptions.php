<?php

declare(strict_types=1);

namespace WipyAutos\AutomotiveSdk\Admin;

use WipyAutos\AutomotiveSdk\Options;

class PageOptions extends Page
{
    protected $page_slug = 'options';
    protected $page_title = 'Options';
    protected $menu_title = 'Options';
    protected $sub_menu_position = 15;

    protected $tab_actions = [
        'dealer' => 'Dealer',
        'legal' => 'Legal',
        'output' => 'Output',
        'cache' => 'Cache',
        'license' => 'License',
    ];

    public function __construct()
    {
        parent::__construct();
        add_action('admin_init', [$this, 'registerSettings']);
    }

    public function adminContent(): void
    {
        switch ($this->current_tab) {
            default:
                $this->renderOptions();
                break;
        }
    }

    /**
     * Registers the settings fields in WordPress.
     */
    public function registerSettings(): void
    {
        $fields = $this->defineFields();

        foreach ($fields as $section => $data) {
            if ($this->current_tab !== $section) {
                continue;
            }

            add_settings_section(
                "automotivesdk_{$section}_section",
                $data['section_title'],
                null,
                'automotivesdk-options'
            );

            foreach ($data['fields'] as $field) {
                add_settings_field(
                    $field['name'],
                    $field['label'],
                    [$this, 'renderField'],
                    'automotivesdk-options',
                    "automotivesdk_{$section}_section",
                    $field
                );

                // Ensure the settings are registered under the correct options group
                register_setting('automotivesdk_options', $field['name']);
            }
        }
    }

    /**
     * Defines the fields for the settings page.
     *
     * @return array An array of field definitions.
     */
    protected function defineFields(): array
    {
        return Options::get();
    }

    /**
     * Renders the input fields for the settings page.
     *
     * @param array $field The field definition.
     */
    public function renderField(array $field): void
    {
        $value = get_option($field['name'], '');

        switch ($field['type']) {
            case 'checkbox':
                echo '<input type="checkbox" name="' . esc_attr($field['name']) . '" value="1" ' . checked($value, '1', false) . ' />';
                break;

            case 'post_checkbox':
                $args = [
                    'post_type' => $field['post_type'],
                    'posts_per_page' => -1,
                ];
                $posts = get_posts($args);

                foreach ($posts as $post) {
                    $meta_key = $field['name'] . '_' . $post->ID;
                    $meta_value = get_option($meta_key, '');
                    echo '<input type="checkbox" name="' . esc_attr($meta_key) . '" value="1" ' . checked($meta_value, '1', false) . ' /> ' . esc_html($post->post_title) . '<br>';
                }
                break;

            case 'nonce':
                wp_nonce_field($field['name'], $field['name']);
                break;

            case 'number':
                echo '<input type="number" name="' . esc_attr($field['name']) . '" value="' . esc_attr($value) . '" />';
                break;

            case 'select':
                echo '<select name="' . esc_attr($field['name']) . '">';
                foreach ($field['options'] as $option_value => $option_label) {
                    echo '<option value="' . esc_attr($option_value) . '" ' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
                }
                echo '</select>';
                break;

            case 'submit':
                echo '<input type="submit" name="' . esc_attr($field['name']) . '" value="' . esc_attr($field['label']) . '" />';
                break;

            case 'post_select':
                $args = [
                    'post_type' => $field['post_type'],
                    'posts_per_page' => -1,
                ];
                $posts = get_posts($args);

                echo '<select name="' . esc_attr($field['name']) . '">';
                echo '<option value="">Select a post</option>';
                foreach ($posts as $post) {
                    echo '<option value="' . esc_attr($post->ID) . '" ' . selected($value, $post->ID, false) . '>' . esc_html($post->post_title) . '</option>';
                }
                echo '</select>';
                break;
            case 'text':
                echo '<input type="text" name="' . esc_attr($field['name']) . '" value="' . esc_attr($value) . '" />';
                break;
            case 'textarea':
                echo '<textarea name="' . esc_attr($field['name']) . '">' . esc_textarea($value) . '</textarea>';
                break;
        }
    }

    /**
     * Renders the options form.
     */
    protected function renderOptions(): void
    {
?>
        <form method="post" action="options.php">
            <?php
            settings_fields('automotivesdk_options');
            do_settings_sections('automotivesdk-options');
            echo '<input type="hidden" name="tab" value="' . esc_attr($_GET['tab'] ?? '') . '" />';
            submit_button();
            ?>
        </form>
<?php
    }
}
