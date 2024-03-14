<?php

namespace IdeoLogix\DigitalLicenseManagerClient\Http\Responses;

/**
 * Class Error
 * @package IdeoLogix\DigitalLicenseManagerClient\Http\Responses
 */
class Error extends Base {

	/**
	 * The code
	 * @var numeric
	 */
	protected $code;

	/**
	 * The message
	 * @var string
	 */
	protected $message;

	/**
	 * The data
	 * @var array
	 */
	protected $data;

	/**
	 * Error constructor.
	 *
	 * @param $code
	 * @param $message
	 * @param array $data
	 */
	public function __construct( $code, $message, $data = array() ) {
		$this->code    = $code;
		$this->message = $message;
		$this->data    = $data;
	}

	/**
	 * Returns the code
	 * @return float|int
	 */
	public function get_code() {
		return $this->code;
	}

	/**
	 * Returns the message
	 * @return string
	 */
	public function get_message() {
		return $this->message;
	}

	/**
	 * Is error?
	 * @return bool
	 */
	public function is_error() {
		return true;
	}
}