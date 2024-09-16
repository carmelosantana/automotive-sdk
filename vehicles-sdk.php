<?php
/*
Plugin Name: Vehicles SDK
Plugin URI: https://wpautos.dev
Description: Automotive inventory management system for WordPress.
Version: 0.1.2
Author: Carmelo Santana
Author URI: https://carmelosantana.com
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Defines
define('VSDK', 'vehicles-sdk');
define('VSDK_TITLE', 'Vehicles SDK');
define('VSDK__FILE__', __FILE__);
define('VSDK_DIR_URL', plugin_dir_url(__FILE__));
define('VSDK_DIR_PATH', plugin_dir_path(__FILE__));
define('VSDK_ASSETS_URL', VSDK_DIR_URL . 'assets/');
define('VSDK_ASSETS_PATH', VSDK_DIR_PATH . 'assets/');

// Composer
if (!file_exists($composer = plugin_dir_path(__FILE__) . 'vendor/autoload.php')) {
    trigger_error(
        sprintf(
            /* translators: %s: plugin name */
            esc_html__('Error locating %s autoloader. Please run <code>composer install</code>.', 'vehicles-sdk'),
            esc_html__('WP Autos', 'vehicles-sdk')
        ),
        E_USER_ERROR
    );
}
require $composer;

add_action('plugins_loaded', function () {
    new \WpAutos\VehiclesSdk\Loader();

    // flush permalinks on plugin activation
    register_activation_hook(__FILE__, 'flush_rewrite_rules');
});
