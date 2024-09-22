<?php

declare(strict_types=1);

namespace WpAutos\AutomotiveSdk\Admin;

use WpAutos\AutomotiveSdk\Vehicle\Data as VehicleData;

class PageDashboard extends Page
{
    protected $page_description = 'Welcome to the Automotive SDK Dashboard.';
    protected $page_title = 'Dashboard';
    protected $menu_title = 'Automotive SDK';
    protected $sub_menu_position = 0;

    public function __construct()
    {
        parent::__construct();
    }

    public function adminMenu(): void
    {
        $this->addAdminMenuSeparator(50);
        add_menu_page(
            $this->page_title,
            $this->menu_title,
            'manage_options',
            ASDK,
            [$this, 'adminPage'],
            $this->page_icon,
            $this->parent_menu_position
        );

        add_submenu_page(
            ASDK,
            $this->page_title,
            'Dashboard',
            'manage_options',
            ASDK,
            [$this, 'adminPage'],
            $this->sub_menu_position
        );
    }

    public function adminContent(): void
    {
        $this->renderVehicleCounts();
        $this->renderVersion();
    }

    protected function renderVehicleCounts(): void
    {
        $vehicles_transient = get_transient('automotivesdk_vehicles');
        if (false === $vehicles_transient) {
            $vehicles = new VehicleData();
            $all_vehicles = $vehicles->queryVehicles();
            set_transient('automotivesdk_vehicles', $all_vehicles, 60 * 60);
        } else {
            $all_vehicles = $vehicles_transient;
        }

        echo '<h3>Vehicle Counts</h3>';
        echo '<p><strong>Total Vehicles</strong> ' . count($all_vehicles) . '</p>';
    }

    protected function renderVersion(): void
    {
        echo '<h3>Version</h3>';
        echo '<p><code>' . esc_html(ASDK_VERSION) . '</code></p>';
    }
}
