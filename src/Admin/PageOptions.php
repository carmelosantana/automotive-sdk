<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Admin;

class PageOptions extends Page
{
    protected $page_slug = 'options';
    protected $page_title = 'Options';
    protected $menu_title = 'Options';
    protected $page_description = 'Manage settings and options for the plugin.';

    // Define the tabs
    protected $tabs = [
        'general' => 'General',
        'advanced' => 'Advanced',
        'display' => 'Display'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function adminContent(): void
    {
        $current_tab = $_GET['tab'] ?? 'general';
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
            case 'general':
                $this->renderGeneralTab();
                break;
            case 'advanced':
                $this->renderAdvancedTab();
                break;
            case 'display':
                $this->renderDisplayTab();
                break;
            default:
                $this->renderGeneralTab();
                break;
        }
    }

    /**
     * Render the General tab content.
     */
    protected function renderGeneralTab(): void
    {
        echo '<h3>General Settings</h3>';
        echo '<p>General settings go here.</p>';
    }

    /**
     * Render the Advanced tab content.
     */
    protected function renderAdvancedTab(): void
    {
        echo '<h3>Advanced Settings</h3>';
        echo '<p>Advanced settings go here.</p>';
    }

    /**
     * Render the Display tab content.
     */
    protected function renderDisplayTab(): void
    {
        echo '<h3>Display Settings</h3>';
        echo '<p>Display settings go here.</p>';
    }
}
