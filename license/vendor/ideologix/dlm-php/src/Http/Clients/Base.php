<?php

namespace IdeoLogix\DigitalLicenseManagerClient\Http\Clients;

use IdeoLogix\DigitalLicenseManagerClient\Http\Responses\Base as BaseResponse;

/**
 * Class BaseClient
 * @package IdeoLogix\DigitalLicenseManagerClient
 */
abstract class Base {

	/**
	 * The ID of the client
	 * @var string
	 */
	protected $id;

	/**
	 * The client version
	 * @var string
	 */
	protected $version;

	/**
	 * The API url
	 * @var string
	 */
	protected $url;

	/**
	 * The API consumer key
	 * @var string
	 */
	protected $consumer_key;

	/**
	 * The API consumer secret
	 * @var string
	 */
	protected $consumer_secret;

	/**
	 * The API user agent
	 * @var string
	 */
	protected $user_agent = null;

	/**
	 * The API request timeout
	 * @var int
	 */
	protected $timeout = 5;

	/**
	 * BaseClient constructor.
	 *
	 * @param $url
	 * @param $consumer_key
	 * @param $consumer_secret
	 * @param string $user_agent
	 */
	public function __construct( $url, $consumer_key, $consumer_secret, $user_agent = '' ) {
		$this->url             = $url;
		$this->consumer_key    = $consumer_key;
		$this->consumer_secret = $consumer_secret;
		$this->user_agent      = empty( $user_agent ) ? sprintf( 'DLM %s Client v%s (%s)', $this->id, $this->version, $this->id ) : $user_agent;
	}


	/**
	 * Generate full api url
	 *
	 * @param $path
	 * @param array $params
	 *
	 * @return string
	 */
	public function url( $path, $params = array() ) {
		$url = rtrim( $this->url, '/' ) . '/' . ltrim( $path, '/' );
		$url = strtok( $url, '?' );

		if ( ! empty( $params ) ) {
			$url = sprintf( '%s?%s', $url, http_build_query( $params ) );
		}

		return $url;
	}

	/**
	 * Decodes JSON
	 *
	 * @param $data
	 *
	 * @return mixed|null
	 */
	protected function json_decode($data) {
		$response = json_decode($data, true);
		if( json_last_error() === JSON_ERROR_NONE ) {
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * HTTP GET implementation
	 *
	 * @param $path
	 * @param $data
	 *
	 * @return BaseResponse
	 */
	abstract public function get( $path, $data = array() );

	/**
	 * HTTP POST implementation
	 *
	 * @param $path
	 * @param array $data
	 * @param array $files
	 *
	 * @return BaseResponse
	 */
	abstract public function post( $path, $data = array(), $files = array() );

	/**
	 * HTTP PUT implementation
	 *
	 * @param $path
	 * @param array $data
	 * @param array $files
	 *
	 * @return BaseResponse
	 */
	abstract public function put( $path, $data = array(), $files = array() );

	/**
	 * HTTP DELETE implementation
	 *
	 * @param $path
	 *
	 * @return BaseResponse
	 */
	abstract public function delete( $path );

	/**
	 * Download specific url to file path
	 *
	 * @param $path
	 * @param $save_dir
	 * @param null $save_filename
	 * @param array $data
	 *
	 * @return string|BaseResponse
	 */
	abstract public function download( $path, $save_dir, $save_filename = null,  $data = array() );

	/**
	 * Is supported?
	 * @return bool
	 */
	abstract public function is_supported();

	/**
	 * Return consumer key
	 * @return string
	 */
	public function get_consumer_key() {
		return $this->consumer_key;
	}

	/**
	 * Return consumer secret
	 * @return string
	 */
	public function get_consumer_secret() {
		return $this->consumer_secret;
	}

}
