<?php
/*
Plugin Name: Automotive SDK
Plugin URI: https://wipyautos.com
Description: Automotive inventory management system for WordPress.
Version: 0.1.6
Author: Carmelo Santana
Author URI: https://carmelosantana.com
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Defines
define('ASDK', 'automotive-sdk');
define('ASDK_TITLE', 'Automotive SDK');
define('ASDK__FILE__', __FILE__);
define('ASDK_DIR_URL', plugin_dir_url(__FILE__));
define('ASDK_DIR_PATH', plugin_dir_path(__FILE__));
define('ASDK_ASSETS_URL', ASDK_DIR_URL . 'assets/');
define('ASDK_ASSETS_PATH', ASDK_DIR_PATH . 'assets/');
define('ASDK_SRC', ASDK_DIR_PATH . 'src/');
define('ASDK_VERSION', '0.1.6');

// Composer
if (!file_exists($composer = plugin_dir_path(__FILE__) . 'vendor/autoload.php')) {
    trigger_error(
        sprintf(
            /* translators: %s: plugin name */
            esc_html__('Error locating %s autoloader. Please run <code>composer install</code>.', 'automotive-sdk'),
            esc_html__('Wipy Autos', 'automotive-sdk')
        ),
        E_USER_ERROR
    );
}
require $composer;

add_action('plugins_loaded', function () {
    new \WipyAutos\AutomotiveSdk\Loader();

    // flush permalinks on plugin activation
    register_activation_hook(__FILE__, 'flush_rewrite_rules');
});
