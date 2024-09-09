<?php
/*
Plugin Name: VIN Importer
Plugin URI: https://tools.vin
Description: Import vehicles from various sources.
Version: 0.1.0
Author: Carmelo Santana
Author URI: https://carmelosantana.com
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Defines
define('VIN_IMPORTER', 'vin-importer');
define('VIN_IMPORTER_TITLE', 'VIN Importer');
define('VIN_IMPORTER_DIR_URL', plugin_dir_url(__FILE__));
define('VIN_IMPORTER_DIR_PATH', plugin_dir_path(__FILE__));

// Composer
if (!file_exists($composer = plugin_dir_path(__FILE__) . 'vendor/autoload.php')) {
    trigger_error(
        sprintf(
            /* translators: %s: plugin name */
            esc_html__('Error locating %s autoloader. Please run <code>composer install</code>.', 'vin-importer'),
            esc_html__('VIN Importer', 'vin-importer')
        ),
        E_USER_ERROR
    );
}
require $composer;

add_action('plugins_loaded', function () {
    new \CarmeloSantana\VinImporter\Loader();

    // flush permalinks on plugin activation
    register_activation_hook(__FILE__, 'flush_rewrite_rules');
});
