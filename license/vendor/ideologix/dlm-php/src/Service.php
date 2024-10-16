<?php

namespace IdeoLogix\DigitalLicenseManagerClient;

use IdeoLogix\DigitalLicenseManagerClient\Http\Clients\Base as BaseHttpClient;
use IdeoLogix\DigitalLicenseManagerClient\Http\Clients\Curl;
use IdeoLogix\DigitalLicenseManagerClient\Http\Clients\WordPress;
use IdeoLogix\DigitalLicenseManagerClient\Http\Requests\Generators;
use IdeoLogix\DigitalLicenseManagerClient\Http\Requests\Licenses;
use IdeoLogix\DigitalLicenseManagerClient\Http\Requests\Software;

/**
 * Class API
 * @package IdeoLogix\DigitalLicenseManagerClient
 */
class Service {

	/**
	 * The current HTTP Client
	 * @var null|BaseHttpClient
	 */
	private $http_client = null;

	/**
	 * List of native HTTP clients
	 * @var string[]
	 */
	private $http_clients = array(
		WordPress::class,
		Curl::class
	);

	/**
	 * The Licenses Requests
	 * @var Licenses
	 */
	private $licenses;

	/**
	 * The Generators Requests
	 * @var Generators
	 */
	private $generators;

	/**
	 * The Software Requests
	 * @var Software
	 */
	private $software;

	/**
	 * API constructor.
	 *
	 * @param $url
	 * @param $consumer_key
	 * @param $consumer_secret
	 *
	 * @throws \Exception
	 */
	public function __construct( $url, $consumer_key, $consumer_secret ) {

		$this->http_client = $this->find_http_client( $url, $consumer_key, $consumer_secret );
		if ( null === $this->http_client ) {
			throw new \Exception( 'Could not find suitable HTTP client.' );
		}

		$this->set_requests();
	}

	/**
	 * Set the HTTP client
	 *
	 * @param $http_client
	 *
	 * @throws \Exception
	 */
	public function set_http_client( $http_client ) {
		if ( ! ( $http_client instanceof BaseHttpClient ) ) {
			throw new \Exception( 'Invalid HTTP client' );
		}
		$this->http_client = $http_client;
		$this->set_requests();
	}

	/**
	 * Return the HTTP client
	 * @return BaseHttpClient|mixed|null
	 */
	public function get_http_client() {
		return $this->http_client;
	}

	/**
	 * Set the HTTP requests
	 */
	public function set_requests() {
		$this->licenses   = new Licenses( $this->http_client );
		$this->generators = new Generators( $this->http_client );
		$this->software   = new Software( $this->http_client );
	}

	/**
	 * Find http client.
	 *
	 * - Loops over the native HTTP Clients array and chooses the first supported one.
	 *
	 * @param $url
	 * @param $consumer_key
	 * @param $consumer_secret
	 *
	 * @return mixed
	 */
	private function find_http_client( $url, $consumer_key, $consumer_secret ) {
		foreach ( $this->http_clients as $http_client_name ) {
			$client = new $http_client_name( $url, $consumer_key, $consumer_secret );
			if ( $client->is_supported() ) {
				return $client;
			}
		}

		return null;
	}

	/**
	 * The Licenses related requests
	 * @return Licenses
	 */
	public function licenses() {
		return $this->licenses;
	}

	/**
	 * The Generators related requests
	 * @return Generators
	 */
	public function generators() {
		return $this->generators;
	}

	/**
	 * The Licenses related requests
	 * @return Software
	 */
	public function software() {
		return $this->software;
	}

}
