<?php

use TheWebSolver\License_Manager\API\Manager;

// Setting files.
require_once __DIR__ . '/tws-license-manager-client-master/Includes/API/Manager.php';
require_once __DIR__ . '/tws-license-manager-client-master/Includes/Component/Basic_Auth.php';
require_once __DIR__ . '/tws-license-manager-client-master/Includes/Component/Http_Client.php';
require_once __DIR__ . '/tws-license-manager-client-master/Includes/Component/OAuth.php';
require_once __DIR__ . '/tws-license-manager-client-master/Includes/Component/Options.php';
require_once __DIR__ . '/tws-license-manager-client-master/Includes/Component/Request.php';
require_once __DIR__ . '/tws-license-manager-client-master/Includes/Component/Response.php';


function test_client_manager() {
	// replace parameters with your own.
	$manager = new Manager( 'contact-forms-anti-spam', 'contact-forms-anti-spam.php' );

	$manager->validate_with(
		array(
			'license_key' => __( 'Enter a valid license key.', 'contact-forms-anti-spam' ),
			'email'       => __( 'Enter valid/same email address used at the time of purchase.', 'contact-forms-anti-spam' ),
			'order_id'    => __( 'Enter same/valid purchase order ID.', 'contact-forms-anti-spam' ),
			'slug'        => 'license-api-key',
		)
	)
	->authenticate_with(
			'ck_3fc0620008eb219e510b42d7a1164c7e0d28b2f1',
			'cs_1eef46aeae9ef30571491672fd14b9cfcaf50856'
	)
	 ->hash_with( '6f246e0205bc3babb8c55464a74735e8adc79446a0199cba1879fe5c73b25f3a' )
	 ->set_key_or_id( 'hundred95F284F74XF4HXGE18D27XZD' ) // uncomment this to get the given license key data.
	 //->set_key_or_id( '55' ) // uncomment this to get the first generator data.
	->connect_with( esc_url( 'https://wpmaspik.com' ), array( 'verify_ssl' => 2 ) ) // replace server url.
	->disable_form();

	if ( $manager->client->has_error() ) {
		$response = $manager->client->get_error();
	} else {
		$response = $manager->make_request_with( 'licenses' );
		 $response = $manager->make_request_with( 'generators' ); // uncomment this to request generators (comment above code if this is uncommented).
	}
}
add_action( 'admin_notices', 'test_client_manager' );






// The Web Solver License Manager Client Plugin class.


class Client_Plugin {
	/**
	 * The Server URL to perform API query.
	 *
	 * @var string
	 */
	const SERVER_URL = 'https://wpmaspik.com';


  
	/**
	 * The main menu slug where license form submenu page to be hooked. Defaults to dashboard menu.
	 *
	 * @var string
	 */
	const PARENT_SLUG = 'contact-forms-anti-spam';

	/**
	 * Used as the plugin prefix.
	 *
	 * Defaults to the plugin folder name where this client be included.
	 * Recommended to include this file to the root of the selling plugin.
	 *
	 * @var string
	 */
	public $dirname;

	/**
	 * License Manager.
	 *
	 * @var Manager
	 */
	public $manager;

	/**
	 * Response from server.
	 *
	 * @var stdClass|WP_Error
	 */
	private $response;

	/**
	 * Get instance.
	 *
	 * @return Client_Plugin
	 */
	public static function init() {
		static $plugin;

		if ( ! is_a( $plugin, get_class() ) ) {
			$plugin = new self();
		}

		return $plugin;
	}
  	public function show_license_notice() {
        $this->manager->show_notice( true );
    }


	/**
	 * Private constructor to prevent direct instantiation.
	 */
	private function __construct() {
		//require_once __DIR__ . '/vendor/autoload.php';
		$this->dirname = basename( dirname( __FILE__ ) );

		if ( is_admin() ) {
			// Initialize the license manager client handler.
			$this->manager = new Manager( self::PARENT_SLUG, false, self::PARENT_SLUG );
			//$this->manager = new Manager( 'contact-forms-anti-spam', 'contact-forms-anti-spam.php' );

		add_action( 'after_setup_theme', array( $this, 'start' ), 5 );
		add_action( 'admin_notices', array( $this, 'show_license_notice' ) );
		}
	}

	/**
	 * Sets request validation parameters including consumer key and consumer secret.
	 */
	public function start() {
		$this->manager
		->validate_with(
			array(
				'license_key' => __( 'Enter a valid license key.', 'tws-license-manager-client' ),
				'email'       => __( 'Enter valid/same email address used at the time of purchase.', 'tws-license-manager-client' ),
				//'order_id'    => __( 'Enter same/valid purchase order ID.', 'tws-license-manager-client' ),
				'slug'        => 'license-api-key',
			)
		)
		->authenticate_with(
			'ck_3fc0620008eb219e510b42d7a1164c7e0d28b2f1',
			'cs_1eef46aeae9ef30571491672fd14b9cfcaf50856'
		)
		->hash_with( '6f246e0205bc3babb8c55464a74735e8adc79446a0199cba1879fe5c73b25f3a' )
		->connect_with(
			esc_url( self::SERVER_URL ),
			array(
				'timeout'           => 15,
				'namespace'         => 'lmfwc',
				'version'           => 'v2',

				// set 0 if no HTTPS verification needed (not recommended).
				'verify_ssl'        => 2,

				// Only works if passed as query in BasicAuth authentication (if site has SSL).
				// If set to false, $_SERVER['PHP_AUTH_USER'] & $_SERVER['PHP_AUTH_PW']
				// must be set for authorization on server site.
				// {@filesource license-manager-for-woocommerce/includes/api/Authentication.php}.
				'query_string_auth' => true,
			)
		)
		->disable_form( true );
	}

} // class end.

// Initialize the client plugin.
Client_Plugin::init();