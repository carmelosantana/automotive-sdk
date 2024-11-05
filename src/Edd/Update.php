<?php

declare(strict_types=1);

namespace WipyAutos\AutomotiveSdk\Edd;

/*
Description: Illustrates how to include an updater in your plugin for EDD Software Licensing
Author: Pippin Williamson
Author URI: http://pippinsplugins.com
*/

/**
 * For further details please visit http://docs.easydigitaldownloads.com/article/383-automatic-upgrades-for-wordpress-plugins
 */
class Update
{
	// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
	const ASDK_EDD_STORE_URL = 'https://wipyautos.com'; // you should use your own CONSTANT name, and be sure to replace it throughout this file

	const ASDK_EDD_AUTHOR = 'Thing Press';

	// the download ID for the product in Easy Digital Downloads
	const ASDK_EDD_ITEM_ID = 12; // you should use your own CONSTANT name, and be sure to replace it throughout this file

	// the name of the product in Easy Digital Downloads
	const ASDK_EDD_ITEM_NAME = ASDK_TITLE; // you should use your own CONSTANT name, and be sure to replace it throughout this file

	// license option
	const ASDK_EDD_LICENSE = 'license_automotive_sdk';

	const ASDK_EDD_PLUGIN_LICENSE_PAGE = 'automotive-sdk-options';

	public function __construct()
	{
		add_action('init', [$this, 'asdk_edd_plugin_updater']);
		add_action('admin_init', [$this, 'asdk_edd_activate_license']);
		add_action('admin_init', [$this, 'asdk_edd_deactivate_license']);
		add_action('admin_notices', [$this, 'asdk_edd_admin_notices']);
	}

	/**
	 * Initialize the updater. Hooked into `init` to work with the
	 * wp_version_check cron job, which allows auto-updates.
	 */
	public function asdk_edd_plugin_updater()
	{
		if (! class_exists('EDD_SL_Plugin_Updater')) {
			// load our custom updater
			include dirname(__FILE__) . '/EDD_SL_Plugin_Updater.php';
		}

		// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
		$doing_cron = defined('DOING_CRON') && DOING_CRON;
		if (! current_user_can('manage_options') && ! $doing_cron) {
			return;
		}

		// retrieve our license key from the DB
		$license_key = trim(get_option(self::ASDK_EDD_LICENSE, ''));

		// setup the updater
		$edd_updater = new EDD_SL_Plugin_Updater(
			self::ASDK_EDD_STORE_URL,
			__FILE__,
			array(
				'version' => ASDK_VERSION,                    // current version number
				'license' => $license_key,             // license key (used get_option above to retrieve from DB)
				'item_id' => self::ASDK_EDD_ITEM_ID,       // ID of the product
				'author'  => self::ASDK_EDD_AUTHOR, // author of this plugin
				'beta'    => false,
			)
		);
	}


	/**
	 * Activates the license key.
	 *
	 * @return void
	 */
	public function asdk_edd_activate_license()
	{
		// listen for our activate button to be clicked
		if (! isset($_POST['edd_license_activate'])) {
			return;
		}

		// run a quick security check
		if (! check_admin_referer('asdk_edd_nonce', 'asdk_edd_nonce')) {
			return; // get out if we didn't click the Activate button
		}

		// retrieve the license from the database
		$license = trim(get_option(self::ASDK_EDD_LICENSE));
		if (! $license) {
			$license = ! empty($_POST[self::ASDK_EDD_LICENSE]) ? sanitize_text_field($_POST[self::ASDK_EDD_LICENSE]) : '';
		}
		if (! $license) {
			return;
		}

		// data to send in our API request
		$api_params = array(
			'edd_action'  => 'activate_license',
			'license'     => $license,
			'item_id'     => self::ASDK_EDD_ITEM_ID,
			'item_name'   => rawurlencode(self::ASDK_EDD_ITEM_NAME), // the name of our product in EDD
			'url'         => home_url(),
			'environment' => function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production',
		);

		// Call the custom API.
		$response = wp_remote_post(
			self::ASDK_EDD_STORE_URL,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		// make sure the response came back okay
		if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {

			if (is_wp_error($response)) {
				$message = $response->get_error_message();
			} else {
				$message = __('An error occurred, please try again.');
			}
		} else {

			$license_data = json_decode(wp_remote_retrieve_body($response));

			if (false === $license_data->success) {

				switch ($license_data->error) {

					case 'expired':
						$message = sprintf(
							/* translators: the license key expiration date */
							__('Your license key expired on %s.', 'edd-sample-plugin'),
							date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp')))
						);
						break;

					case 'disabled':
					case 'revoked':
						$message = __('Your license key has been disabled.', 'edd-sample-plugin');
						break;

					case 'missing':
						$message = __('Invalid license.', 'edd-sample-plugin');
						break;

					case 'invalid':
					case 'site_inactive':
						$message = __('Your license is not active for this URL.', 'edd-sample-plugin');
						break;

					case 'item_name_mismatch':
						/* translators: the plugin name */
						$message = sprintf(__('This appears to be an invalid license key for %s.', 'edd-sample-plugin'), self::ASDK_EDD_ITEM_NAME);
						break;

					case 'no_activations_left':
						$message = __('Your license key has reached its activation limit.', 'edd-sample-plugin');
						break;

					default:
						$message = __('An error occurred, please try again.', 'edd-sample-plugin');
						break;
				}
			}
		}

		// Check if anything passed on a message constituting a failure
		if (! empty($message)) {
			$redirect = add_query_arg(
				array(
					'page'          => self::ASDK_EDD_PLUGIN_LICENSE_PAGE,
					'sl_activation' => 'false',
					'message'       => rawurlencode($message),
					'tab'           => 'license',
				),
				admin_url('admin.php')
			);

			wp_safe_redirect($redirect);
			exit();
		}

		// $license_data->license will be either "valid" or "invalid"
		if ('valid' === $license_data->license) {
			update_option(self::ASDK_EDD_LICENSE, $license);
		}
		update_option('asdk_edd_license_status', $license_data->license);
		$args = [
			'page' => self::ASDK_EDD_PLUGIN_LICENSE_PAGE,
			'tab' => 'license',
		];
		$redirect = add_query_arg($args, admin_url('admin.php'));
		wp_safe_redirect($redirect);
		exit();
	}


	/**
	 * Deactivates the license key.
	 * This will decrease the site count.
	 *
	 * @return void
	 */
	public function asdk_edd_deactivate_license()
	{

		// listen for our activate button to be clicked
		if (isset($_POST['edd_license_deactivate'])) {

			// run a quick security check
			if (! check_admin_referer('asdk_edd_nonce', 'asdk_edd_nonce')) {
				return; // get out if we didn't click the Activate button
			}

			// retrieve the license from the database
			$license = trim(get_option(self::ASDK_EDD_LICENSE));

			// data to send in our API request
			$api_params = array(
				'edd_action'  => 'deactivate_license',
				'license'     => $license,
				'item_id'     => self::ASDK_EDD_ITEM_ID,
				'item_name'   => rawurlencode(self::ASDK_EDD_ITEM_NAME), // the name of our product in EDD
				'url'         => home_url(),
				'environment' => function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production',
			);

			// Call the custom API.
			$response = wp_remote_post(
				self::ASDK_EDD_STORE_URL,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);

			// make sure the response came back okay
			if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {

				if (is_wp_error($response)) {
					$message = $response->get_error_message();
				} else {
					$message = __('An error occurred, please try again.');
				}

				$redirect = add_query_arg(
					array(
						'page'          => self::ASDK_EDD_PLUGIN_LICENSE_PAGE,
						'sl_activation' => 'false',
						'message'       => rawurlencode($message),
						'tab'           => 'license',
					),
					admin_url('admin.php')
				);

				wp_safe_redirect($redirect);
				exit();
			}

			// decode the license data
			$license_data = json_decode(wp_remote_retrieve_body($response));

			// $license_data->license will be either "deactivated" or "failed"
			if ('deactivated' === $license_data->license) {
				delete_option('asdk_edd_license_status');
			}

			$args = [
				'page' => self::ASDK_EDD_PLUGIN_LICENSE_PAGE,
				'tab' => 'license',
			];
			$redirect = add_query_arg($args, admin_url('admin.php'));
			wp_safe_redirect($redirect);
			exit();
		}
	}

	/**
	 * Checks if a license key is still valid.
	 * The updater does this for you, so this is only needed if you want
	 * to do something custom.
	 *
	 * @return void
	 */
	public function asdk_edd_check_license()
	{

		$license = trim(get_option(self::ASDK_EDD_LICENSE));

		$api_params = array(
			'edd_action'  => 'check_license',
			'license'     => $license,
			'item_id'     => self::ASDK_EDD_ITEM_ID,
			'item_name'   => rawurlencode(self::ASDK_EDD_ITEM_NAME),
			'url'         => home_url(),
			'environment' => function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production',
		);

		// Call the custom API.
		$response = wp_remote_post(
			self::ASDK_EDD_STORE_URL,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		if (is_wp_error($response)) {
			return false;
		}

		$license_data = json_decode(wp_remote_retrieve_body($response));

		if ('valid' === $license_data->license) {
			echo 'valid';
			exit;
			// this license is still valid
		} else {
			echo 'invalid';
			exit;
			// this license is no longer valid
		}
	}

	/**
	 * This is a means of catching errors from the activation method above and displaying it to the customer
	 */
	function asdk_edd_admin_notices()
	{
		if (isset($_GET['sl_activation']) && ! empty($_GET['message'])) {

			switch ($_GET['sl_activation']) {

				case 'false':
					$message = urldecode($_GET['message']);
?>
					<div class="error">
						<p><?php echo wp_kses_post($message); ?></p>
					</div>
<?php
					break;

				case 'true':
				default:
					$message = __('License activated! 🎉', 'edd-sample-plugin');
					break;
			}
		}
	}
}
