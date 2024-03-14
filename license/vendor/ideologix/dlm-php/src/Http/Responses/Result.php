<?php

namespace IdeoLogix\DigitalLicenseManagerClient\Http\Responses;

/**
 * Class Result
 * @package IdeoLogix\DigitalLicenseManagerClient\Http\Responses
 */
class Result extends Base {

	/**
	 * Is success?
	 * @var bool
	 */
	protected $success;

	/**
	 * The response data
	 * @var array
	 */
	protected $data;

	/**
	 * Result constructor.
	 *
	 * @param $success
	 * @param $data
	 */
	public function __construct( $success, $data ) {
		$this->success = $success;
		$this->data    = $data;
	}

	/**
	 * This is not error.
	 * @return false
	 */
	public function is_error() {
		return false;
	}

	/**
	 * Is success?
	 * @return bool
	 */
	public function is_success() {
		return (bool) $this->success;
	}

	/**
	 * Return the local path
	 * @return mixed|null
	 */
	public function get_path() {
		return isset( $this->data['path'] ) ? $this->data['path'] : null;
	}
}
