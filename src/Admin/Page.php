<?php

declare(strict_types=1);

namespace WpAutos\VehiclesSdk\Admin;

class Page
{
    protected $parent_slug = 'edit.php?post_type=vehicle';
    protected $page_title = 'Vehicles SDK';
    protected $menu_title = 'Vehicles SDK';
    protected $page_slug = VSDK;
    protected $page_description = 'Automotive data management.';
    protected $page_actions = [];

    public function __construct()
    {
        add_action('admin_menu', [$this, 'adminMenu']);
        add_action('admin_enqueue_scripts', [$this, 'adminEnqueue']);
    }

    public function adminEnqueue()
    {
        wp_enqueue_style('vehicles-sdk-admin', VSDK_DIR_URL . 'assets/css/vehicles-sdk.css');
        wp_enqueue_script('vehicles-sdk-admin', VSDK_DIR_URL . 'assets/js/vehicles-sdk.js', ['jquery'], null, true);
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

    public function adminActionsList(): void
    {
        if (empty($this->page_actions)) {
            return;
        }

        // Use the current tab from the URL if not provided
        $current_tab = $_GET['page'] ?? '';

        echo '<h2 class="nav-tab-wrapper">';
        foreach ($this->page_actions as $action) {
            $this->renderTab($action, $current_tab);
        }
        echo '</h2>';
    }

    /**
     * Placeholder function for rendering admin content.
     *
     * @return void
     */
    public function adminContent(): void {}

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
        echo '<h1>' . esc_html($this->page_title) . '</h1>';

        if (!empty($this->page_description)) {
            echo '<p>' . esc_html($this->page_description) . '</p>';
        }

        $this->adminActionsList();

        echo '<hr class="wp-header-end">';
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

    public function generateAjaxUrl(string $action): string
    {
        return admin_url('admin-ajax.php?action=' . $action);
    }

    public function generatePageSlug(string $page = ''): string
    {
        $slug = empty($page) ? VSDK . '-' . $this->page_slug : VSDK . '-' . $page;

        return $slug;
    }

    public function generatePageUrl(string $page, array $args = []): string
    {
        $args['page'] = $this->generatePageSlug($page);

        return add_query_arg($args, admin_url($this->parent_slug));
    }

    private function renderTab(array $action, string $current_tab): void
    {
        $is_active = ($current_tab === $this->generatePageSlug($action['page'])) ? 'nav-tab-active' : '';
        echo '<a href="' . esc_url($this->generatePageUrl($action['page'])) . '" class="nav-tab ' . esc_attr($is_active) . '">' . esc_html($action['description']) . '</a>';
    }
}
