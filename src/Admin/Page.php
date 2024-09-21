<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Admin;

class Page
{
    protected $parent_slug = ASDK;
    protected $page_icon = 'dashicons-database';
    protected $page_title = 'Automotive SDK';
    protected $menu_title = 'Automotive SDK';
    protected $menu_position = 50;
    protected $page_slug = ASDK;
    protected $page_description = '';

    /**
     * Actions:
     * - Can be set via ?page or ?tab query parameters.
     * - Page actions create new sub pages.
     * - Tab actions are used to switch between tabs on the same page.
     * 
     */
    protected $page_actions = [];
    protected $tab_actions = [];

    protected $tab_type;
    protected $current_tab;
    protected $default_tab;

    public function __construct()
    {
        add_action('admin_menu', [$this, 'adminMenu']);
        add_action('admin_enqueue_scripts', [$this, 'adminEnqueue']);

        $this->setupTabType();
        $this->setupCurrentTab();
    }

    /**
     * Adds an admin menu separator at the specified position.
     * 
     * Original author: https://wordpress.stackexchange.com/a/2674
     *
     * @param int $position The position to add the separator.
     * @return void
     */
    public function addAdminMenuSeparator(int $position): void
    {
        global $menu;
        $index = 0;

        foreach ($menu as $offset => $section) {
            if (substr($section[2], 0, 9) === 'separator') {
                $index++;
            }
            if ($offset >= $position) {
                $menu[$position] = ['', 'read', "separator{$index}", '', 'wp-menu-separator'];
                break;
            }
        }
        ksort($menu);
    }

    public function adminActionsList(): void
    {
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($this->getActions() as $slug => $title) {
            switch ($this->tab_type) {
                case 'page':
                    $this->renderPageTab($slug, $title);
                    break;

                case 'tab':
                    $this->renderTab($slug, $title);
                    break;
            }
        }
        echo '</h2>';
        echo '<div style="height: 1rem;"></div>';
    }

    /**
     * Placeholder function for rendering admin content.
     *
     * @return void
     */
    public function adminContent(): void {}

    /**
     * Placeholder function for rendering counters.
     *
     * @return void
     */
    public function adminCounts(): void {}

    public function adminEnqueue()
    {
        add_thickbox();
        wp_enqueue_style('automotive-sdk-admin', ASDK_DIR_URL . 'assets/css/automotive-sdk.css');
        wp_enqueue_script('automotive-sdk-admin', ASDK_DIR_URL . 'assets/js/automotive-sdk.js', ['jquery'], null, true);
    }

    /**
     * Renders the footer for the admin page.
     *
     * @return void
     */
    public function adminFooter(): void
    {
        echo '<div class="tablenav bottom"></div>';
        echo '</div>';
    }

    public function adminHeader(): void
    {
        echo '<div class="wp-autos wrap">';
        echo '<h1 class="wp-heading-inline">' . esc_html($this->page_title);
        echo '</h1>';

        $this->adminCounts();

        $this->adminProgress();

        if (!empty($this->page_description)) {
            echo '<p>' . esc_html($this->page_description) . '</p>';
        }

        if (!empty($this->getActions())) {
            $this->adminActionsList();
        }

        echo '<hr class="wp-header-end">';
    }

    public function adminMenu()
    {
        add_submenu_page(
            $this->parent_slug,
            $this->page_title,
            $this->menu_title,
            'manage_options',
            $this->generatePageSlug(),
            [$this, 'adminPage']
        );
    }

    private function iterateMenuPosition(): int
    {
        $this->menu_position++;

        return $this->menu_position;
    }

    public function adminNotice($message, $notice = 'notice-success')
    {
        echo '<div class="notice ' . $notice . ' is-dismissible">';
        echo '<p>' . $message . '</p>';
        echo '</div>';
    }

    public function adminPage(): void
    {
        $this->adminHeader();
        $this->adminContent();
        $this->adminFooter();
    }

    public function adminProgress()
    {
        echo '<div style="display: flex; align-items: center;">';
        echo '<div class="progress-wrapper">';
        echo ' <div id="progress-bar" class="progress-bar" style="width: 0%;"></div>';
        echo '</div>';
        echo '</div>';
    }

    public function generateAjaxUrl(string $action): string
    {
        return admin_url('admin-ajax.php?action=' . $action);
    }

    public function generatePageSlug(string $page = ''): string
    {
        $slug = empty($page) ? ASDK . '-' . $this->page_slug : ASDK . '-' . $page;

        return $slug;
    }

    public function generatePageUrl(string $page, array $args = []): string
    {
        $args = [
            'page' => $this->generatePageSlug($page),
        ];
        return add_query_arg($args, admin_url('admin.php'));
    }

    public function generateTabUrl(string $page, array $additional = []): string
    {
        $args = [
            'page' => $this->generatePageSlug($this->page_slug),
            'tab' => $page,
        ];
        
        return add_query_arg(array_merge($args, $additional), admin_url('admin.php'));
    }

    public function shutdown(): void
    {
        exit;
    }

    private function renderTab(string $tab_key, string $tab_label): void
    {
        $is_active = ($this->current_tab === $tab_key) ? 'nav-tab-active' : '';
        echo '<a href="' . esc_url($this->generateTabUrl($tab_key)) . '" class="nav-tab ' . esc_attr($is_active) . '">' . esc_html($tab_label) . '</a>';
    }

    private function renderPageTab(string $page, string $label): void
    {
        $is_active = ($this->current_tab === $this->generatePageSlug($page)) ? 'nav-tab-active' : '';
        echo '<a href="' . esc_url($this->generatePageUrl($page)) . '" class="nav-tab ' . esc_attr($is_active) . '">' . esc_html($label) . '</a>';
    }

    private function getActions(): array
    {
        switch ($this->tab_type) {
            case 'page':
                return $this->page_actions;

            case 'tab':
                return $this->tab_actions;
        }

        return [];
    }

    private function setupTabType()
    {
        if (!empty($this->page_actions)) {
            $this->tab_type = 'page';
        } elseif (!empty($this->tab_actions)) {
            $this->tab_type = 'tab';
        }
    }

    private function setupCurrentTab(): void
    {
        $actions = $this->getActions();

        // set default tab
        $this->default_tab = array_key_first($actions);

        // get current tab
        $tab = isset($_REQUEST[$this->tab_type]) ? sanitize_text_field($_REQUEST[$this->tab_type]) : $this->default_tab;

        // set current tab
        $this->current_tab = in_array($tab, array_keys($actions)) ? $tab : $this->default_tab;
    }
}
