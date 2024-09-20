<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Admin;

class PageOptions extends Page
{
    private string $current_tab;
    private string $default_tab;

    protected $page_slug = 'options';
    protected $page_title = 'Options';
    protected $menu_title = 'Options';
    protected $page_description = 'Manage settings and options for the plugin.';

    protected array $tabs = [
        'dealer' => 'Dealer',
        'license' => 'License',
    ];

    public function __construct()
    {
        parent::__construct();
        add_action('admin_init', [$this, 'registerSettings']);

        // set default tab
        $this->default_tab = array_key_first($this->tabs);

        // get current tab
        $tab = $_REQUEST['tab'] ?? $this->default_tab;

        // set current tab
        $this->current_tab = in_array($tab, array_keys($this->tabs)) ? $tab : $this->default_tab;
    }

    public function adminContent(): void
    {
        $current_tab = $_GET['tab'] ?? 'dealer';
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($this->tabs as $tab_key => $tab_label) {
            $active_class = ($current_tab === $tab_key) ? 'nav-tab-active' : '';
            echo '<a href="' . esc_url($this->generatePageUrl('', ['tab' => $tab_key])) . '" class="nav-tab ' . esc_attr($active_class) . '">' . esc_html($tab_label) . '</a>';
        }
        echo '</h2>';

        $this->renderTabContent($current_tab);
    }

    /**
     * Renders the content for the current tab.
     *
     * @param string $tab The current tab.
     */
    protected function renderTabContent(string $tab): void
    {
        switch ($tab) {
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
                'section_title' => __('Dealer Settings', 'automotivesdk'),
                'fields' => [
                    [
                        'name' => 'dealer_name',
                        'type' => 'text',
                        'label' => __('Dealer Name', 'automotivesdk'),
                    ],
                    [
                        'name' => 'dealer_address',
                        'type' => 'text',
                        'label' => __('Dealer Address', 'automotivesdk'),
                    ],
                    [
                        'name' => 'dealer_description',
                        'type' => 'textarea',
                        'label' => __('Dealer Description', 'automotivesdk'),
                    ],
                ],
            ],
            'license' => [
                'section_title' => __('License Settings', 'automotivesdk'),
                'fields' => [
                    [
                        'name' => 'license_key',
                        'type' => 'text',
                        'label' => __('License Key', 'automotivesdk'),
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
