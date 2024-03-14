<?php

namespace IdeoLogix\DigitalLicenseManagerClient\Http\Requests;

use IdeoLogix\DigitalLicenseManagerClient\Http\Interfaces\Resource;
use IdeoLogix\DigitalLicenseManagerClient\Http\Requests\Base as BaseRequest;
use IdeoLogix\DigitalLicenseManagerClient\Http\Responses\Base as BaseResponse;

/**
 * Class Software
 * @package IdeoLogix\DigitalLicenseManagerClient\Http\Requests
 */
class Software extends BaseRequest implements Resource {

	/**
	 * Return list of resources
	 *
	 * @param array $args
	 *
	 * @return BaseResponse
	 * @throws \Exception
	 */
	public function get( $args = array() ) {
		throw new \Exception( 'Not implemented yet.' );
	}

	/**
	 *  Find resource
	 *
	 * @param $id
	 * @param array $args
	 *
	 * @return BaseResponse
	 */
	public function find( $id, $args = array() ) {
		return $this->http->get( "wp-json/dlm/v1/software/{$id}", $args );
	}

	/**
	 * Create resource
	 *
	 * @param array $data
	 *
	 * @return BaseResponse
	 * @throws \Exception
	 */
	public function create( $data ) {
		throw new \Exception( 'Not implemented yet.' );
	}

	/**
	 * Update resource
	 *
	 * @param $id
	 * @param array $data
	 *
	 * @return BaseResponse
	 * @throws \Exception
	 */
	public function update( $id, $data = array() ) {
		throw new \Exception( 'Not implemented yet.' );
	}

	/**
	 * Delete resource
	 *
	 * @param $id
	 *
	 * @return BaseResponse
	 * @throws \Exception
	 */
	public function delete( $id ) {
		throw new \Exception( 'Not implemented yet.' );
	}

	/**
	 * Retrieve the contents of a software file from the licensing server
	 *
	 * @param $activation_token
	 * @param $path_to_save
	 *
	 * @return BaseResponse
	 */
	public function download_latest( $activation_token, $path_to_save ) {
		$url = sprintf( 'wp-json/dlm/v1/software/download/%s?consumer_key=%s&consumer_secret=%s', $activation_token, $this->http->get_consumer_key(), $this->http->get_consumer_secret() );

		return $this->http->download( $url, $path_to_save );
	}

}
