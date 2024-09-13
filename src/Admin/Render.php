<?php

declare(strict_types=1);

namespace WpAutos\Vehicles\Admin\Render;

class Render
{
    /**
     * Renders a list of admin action links as tabs.
     *
     * @param array $actions List of actions with 'page' and 'description' keys.
     * @param string|null $current_tab The current active tab.
     * @return void
     */
    public function adminActionsList(array $actions = [], ?string $current_tab = null): void
    {
        if (empty($actions)) {
            return;
        }

        // Use the current tab from the URL if not provided
        $current_tab ??= $_GET['page'] ?? '';

        echo '<h2 class="nav-tab-wrapper">';
        foreach ($actions as $action) {
            $this->renderTab($action, $current_tab);
        }
        echo '</h2>';
    }

    /**
     * Renders a single tab link for the admin page.
     *
     * @param array $action Action array with 'page' and 'description' keys.
     * @param string $current_tab The current active tab.
     * @return void
     */
    private function renderTab(array $action, string $current_tab): void
    {
        $is_active = ($current_tab === $action['page']) ? 'nav-tab-active' : '';
        echo '<a href="' . esc_url(admin_url('admin.php?page=' . $action['page'])) . '" class="nav-tab ' . esc_attr($is_active) . '">' . esc_html($action['description']) . '</a>';
    }

    /**
     * Placeholder function for rendering admin content.
     *
     * @return void
     */
    public function adminContent(): void
    {
        // Intended for extended use.
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

    /**
     * Renders the header of the admin page with a title and optional description.
     *
     * @param string $title Title of the admin page.
     * @param string|null $description Optional description text.
     * @param array $actions Optional list of actions for tabs.
     * @return void
     */
    public function adminHeader(string $title = '', ?string $description = null, array $actions = []): void
    {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html($title) . '</h1>';

        if (!empty($description)) {
            echo '<p>' . esc_html($description) . '</p>';
        }

        $this->adminActionsList($actions);

        echo '<hr class="wp-header-end">';
    }

    /**
     * Renders a complete admin page with header, content, and footer.
     *
     * @param string $title Title of the page.
     * @param string|null $description Optional description for the page.
     * @param array $actions Optional list of actions.
     * @return void
     */
    public function adminPage(string $title = '', ?string $description = null, array $actions = []): void
    {
        $this->adminHeader($title, $description, $actions);
        $this->adminContent();
        $this->adminFooter();
    }
}
