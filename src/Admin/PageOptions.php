<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Admin;

class PageOptions extends Page
{
    protected $page_slug = 'options';
    protected $page_title = 'Options';
    protected $menu_title = 'Options';
    protected $sub_menu_position = 15;

    protected $tab_actions = [
        'dealer' => 'Dealer',
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
        return [
            'dealer' => [
                'section_title' => __('Dealer Settings', 'automotive-sdk'),
                'fields' => [
                    [
                        'name' => 'dealer_name',
                        'type' => 'text',
                        'label' => __('Dealer Name', 'automotive-sdk'),
                    ],
                    [
                        'name' => 'dealer_address',
                        'type' => 'text',
                        'label' => __('Dealer Address', 'automotive-sdk'),
                    ],
                    [
                        'name' => 'dealer_description',
                        'type' => 'textarea',
                        'label' => __('Dealer Description', 'automotive-sdk'),
                    ],
                ],
            ],
            'license' => [
                'section_title' => __('License Settings', 'automotive-sdk'),
                'fields' => [
                    [
                        'name' => 'license_key',
                        'type' => 'text',
                        'label' => __('License Key', 'automotive-sdk'),
                    ],
                ],
            ],
        ];
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
