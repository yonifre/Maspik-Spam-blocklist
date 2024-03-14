<?php

namespace IdeoLogix\DigitalLicenseManagerClient\Http\Requests;

use IdeoLogix\DigitalLicenseManagerClient\Http\Interfaces\Resource;
use IdeoLogix\DigitalLicenseManagerClient\Http\Responses\Base as HttpResponse;
use IdeoLogix\DigitalLicenseManagerClient\Http\Requests\Base as BaseRequest;

/**
 * Class Generators
 * @package IdeoLogix\DigitalLicenseManagerClient\Http\Requests
 */
class Generators extends BaseRequest implements Resource {

	/**
	 * Return list of resources
	 *
	 * @param array $args
	 *
	 * @return HttpResponse
	 */
	public function get( $args = array() ) {
		return $this->http->get( "wp-json/dlm/v1/generators", $args );
	}

	/**
	 *  Find resource
	 *
	 * @param $id
	 * @param array $args
	 *
	 * @return HttpResponse
	 */
	public function find( $id, $args = array() ) {
		return $this->http->get( "wp-json/dlm/v1/generators/{$id}", $args );
	}

	/**
	 * Create resource
	 *
	 * @param array $data
	 *
	 * @return HttpResponse
	 */
	public function create( $data = array() ) {
		return $this->http->post( "wp-json/dlm/v1/generators", $data );
	}

	/**
	 * Update resource
	 *
	 * @param $id
	 * @param array $data
	 *
	 * @return HttpResponse
	 */
	public function update( $id, $data = array() ) {
		return $this->http->put( "wp-json/dlm/v1/generators/{$id}", $data );
	}

	/**
	 * Delete resource
	 *
	 * @param $id
	 *
	 * @return HttpResponse
	 */
	public function delete( $id ) {
		return $this->http->delete( "wp-json/dlm/v1/generators/{$id}" );
	}

	/**
	 * Generate resources
	 *
	 * @param $id - The generator ID
	 * @param array $data
	 *
	 * @return HttpResponse
	 */
	public function generate( $id, $data = array() ) {
		return $this->http->post( "wp-json/dlm/v1/generators/{$id}/generate", $data );
	}

}
