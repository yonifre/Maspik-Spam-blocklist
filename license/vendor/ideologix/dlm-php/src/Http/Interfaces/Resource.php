<?php


namespace IdeoLogix\DigitalLicenseManagerClient\Http\Interfaces;


use IdeoLogix\DigitalLicenseManagerClient\Http\Responses\Base as HttpResponse;

interface Resource {

	/**
	 * Return list of resources
	 *
	 * @param array $args
	 *
	 * @return HttpResponse
	 */
	public function get( $args = array() );

	/**
	 *  Find resource
	 *
	 * @param $id
	 * @param array $args
	 *
	 * @return HttpResponse
	 */
	public function find( $id, $args = array() );

	/**
	 * Create resource
	 *
	 * @param array $data
	 *
	 * @return HttpResponse
	 */
	public function create( $data );

	/**
	 * Update resource
	 *
	 * @param $id
	 * @param array $data
	 *
	 * @return HttpResponse
	 */
	public function update( $id, $data = array() );

	/**
	 * Delete resource
	 *
	 * @param $id
	 *
	 * @return HttpResponse
	 */
	public function delete( $id );

}
