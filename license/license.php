<?php

if ( ! defined( 'ABSPATH' ) ) {
	die; // Prevent direct access.
}

/**
 * Migrate the old license to new structure utilizing Digital License Manager API.
 * @note After some time, this can be removed, assuming all the users were migrated.
 */
add_action( 'admin_init', function () {

	if ( ! get_option( 'maspik_licensing_version' ) ) {
		$old_data = maybe_unserialize( get_option( 'contact-forms-anti-spam-license-data' ) );
		if ( ! is_object( $old_data ) ) {
			update_option( 'maspik_licensing_version', 2 );
		} else {
			update_option( 'maspik_dlm_license', array(
				'key'        => $old_data->licenseKey,
				'token'      => null,
				'expires_at' => $old_data->expires_at,
			) );
			update_option( 'maspik_licensing_version', 2 );
		}
	}
} );


/**
 * Require Digital License Manager simple license activation API.
 * @note - I extended the Simple Checker Main class for customization purposes.
 */
require_once dirname( __FILE__ ) . '/vendor/autoload.php';
if ( ! class_exists( 'Maspik_License_Checker' ) ) {
	class Maspik_License_Checker extends \IdeoLogix\DigitalLicenseManagerSimpleChecker\Main {
	}
}


/**
 * Returns the DLMs simple license checker instance
 * @return Maspik_License_Checker
 */
if ( ! function_exists( 'maspik_license_checker' ) ) {
	function maspik_license_checker() {

		static $checker;

		if ( ! $checker ) {
			$checker = new \Maspik_License_Checker( [
				'name'            => 'Maspik - Spam blacklist',
				'logo'            => trailingslashit( plugin_dir_url( __FILE__ ) ) . 'assets/logo.png',
				'prefix'          => 'maspik_',
				'context'         => 'plugin',
				'public_path'     => str_replace('/', DIRECTORY_SEPARATOR, dirname( __FILE__ ).'/vendor/ideologix/dlm-wp-simple-checker/public/'),
				'public_url'      => trailingslashit( plugin_dir_url( __FILE__ ) ) . 'vendor/ideologix/dlm-wp-simple-checker/public/',
				'consumer_key'    => 'ck_3fc0620008eb219e510b42d7a1164c7e0d28b2f1',
				'consumer_secret' => 'cs_1eef46aeae9ef30571491672fd14b9cfcaf50856',
				'api_url'         => 'https://wpmaspik.com/',
				'menu'            => [
					'page_title' => 'License Activation',
					'menu_title' => 'License Activation',
					'parent_slug' => 'maspik',
					'capaibility' => 'manage_options',
				],
				'cron'           => [
					'interval' => 'twicedaily',
				]
			] );
		}

		return $checker;
	}
}


/**
 * Initialize it!
 */
maspik_license_checker();