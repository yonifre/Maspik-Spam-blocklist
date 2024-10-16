<?php

namespace IdeoLogix\DigitalLicenseManagerClient\Http\Clients;

use IdeoLogix\DigitalLicenseManagerClient\Http\Responses\Base as BaseResponse;
use IdeoLogix\DigitalLicenseManagerClient\Http\Responses\Error;
use IdeoLogix\DigitalLicenseManagerClient\Http\Responses\Result;
use IdeoLogix\DigitalLicenseManagerClient\Utils\FileSystem;

class WordPress extends Base {

	/**
	 * The ID
	 * @var string
	 */
	protected $id = 'WordPress';

	/**
	 * The current version
	 * @var string
	 */
	protected $version = '1.0';

	/**
	 * HTTP GET implementation
	 *
	 * @param $path
	 * @param $data
	 *
	 * @return BaseResponse
	 */
	public function get( $path, $data = array() ) {

		$url      = $this->url( $path, $data );
		$response = wp_remote_request( $url, [
			'method'  => 'GET',
			'headers' => $this->get_headers()
		] );

		return $this->result( $response );
	}

	/**
	 * HTTP POST implementation
	 *
	 * @param $path
	 * @param array $data
	 * @param array $files
	 *
	 * @return BaseResponse
	 */
	public function post( $path, $data = array(), $files = array() ) {
		$url      = $this->url( $path, $data );
		$response = wp_remote_request( $url, [
			'method'  => 'POST',
			'body'    => $data,
			'headers' => $this->get_headers()
		] );

		return $this->result( $response );
	}

	/**
	 * HTTP PUT implementation
	 *
	 * @param $path
	 * @param array $data
	 * @param array $files
	 *
	 * @return BaseResponse
	 */
	public function put( $path, $data = array(), $files = array() ) {
		$url      = $this->url( $path, $data );
		$response = wp_remote_request( $url, [
			'method'  => 'PUT',
			'body'    => $data,
			'headers' => $this->get_headers()
		] );

		return $this->result( $response );
	}

	/**
	 * HTTP DELETE implementation
	 *
	 * @param $path
	 *
	 * @return BaseResponse
	 */
	public function delete( $path ) {

		$url      = $this->url( $path );
		$response = wp_remote_request( $url, [
			'method'  => 'DELETE',
			'headers' => $this->get_headers()
		] );

		return $this->result( $response );
	}

	/**
	 * Download specific url to file path
	 *
	 * @param $path
	 * @param $save_dir
	 * @param null $save_filename
	 * @param array $data
	 *
	 * @return BaseResponse
	 */
	public function download( $path, $save_dir, $save_filename = null, $data = array() ) {

		$url      = $this->url( $path, $data );
		$response = wp_remote_request( $url, [
			'method'  => 'GET',
			'headers' => $this->get_headers()
		] );

		if ( is_wp_error($response) ) {
			return new Error( 500, $response->get_error_message(), array() );
		} else {

			$body = wp_remote_retrieve_body( $response );
			$decoded  = $this->json_decode( $body );

			if ( $decoded ) {
				return $this->prepare_result( $decoded );
			}

			if ( ! FileSystem::mkdir_p( $save_dir ) ) {
				return new Error( 500, 'Unable to create the save directory.', array() );
			}
			$save_filename = $this->ensure_filename( $response, $save_filename );
			$save_path     = rtrim( $save_dir, '/' ) . DIRECTORY_SEPARATOR . $save_filename;

			$fp = fopen( $save_path, 'w+' );
			if ( ! is_writable( $save_path ) ) {
				return new Error( '400', sprintf( 'The path %s is not writable.', $save_path ) );
			}
			fwrite( $fp, $response->body );
			fclose( $fp );

			return new Result( true, [ 'path' => $save_path ] );
		}
	}

	/**
	 * Check if $filename is set, if it is set, return it. Otherwise try to find it from the Content-Disposition header.
	 * If it is still not present generate one based on the content type.
	 *
	 * @param $response
	 * @param $filename
	 *
	 * @return mixed
	 */
	private function ensure_filename( $response, $filename ) {

		$headers = wp_remote_retrieve_headers($response);

		if ( ! empty( $filename ) ) {
			return $filename;
		}
		$header = ! empty( $headers['Content-Disposition'] ) ? $headers['Content-Disposition'] : '';
		if ( preg_match( '~filename=(?|"([^"]*)"|\'([^\']*)\'|([^;]*))~', $header, $match ) ) {
			$filename = $match[1];
		} else {
			$filename = md5( $response->url ) . time();
		}

		return $filename;
	}

	/**
	 * Return formatted result
	 *
	 * @param $response
	 *
	 * @return Error|Result
	 */
	private function result( $response ) {

		if ( is_wp_error( $response ) ) {
			return new Error( 500, $response->get_error_message(), [] );
		}

		$body   = wp_remote_retrieve_body( $response );
		$result = json_decode( $body, true );

		return $this->prepare_result($result);
	}

	/**
	 * Prepares the result
	 *
	 * @param array $result
	 *
	 * @return Error|Result
	 */
	private function prepare_result($result) {
		if ( isset( $result['success'] ) ) {
			$success = (bool) $result['success'];;
			$data = array();
			if ( isset( $result['data'] ) ) {
				$data = (array) $result['data'];
			}

			return ( new Result( $success, $data ) );
		} else {
			$code    = isset( $result['code'] ) ? $result['code'] : 'server_error';
			$message = isset( $result['message'] ) ? $result['message'] : 'Unknown error.';
			$data    = isset( $result['data'] ) ? $result['data'] : array();

			return new Error( $code, $message, $data );
		}
	}

	/**
	 * Return the headers
	 *
	 * @return string[]
	 */
	private function get_headers() {
		return [
			'Authorization' => 'Basic ' . base64_encode( sprintf( '%s:%s', $this->consumer_key, $this->consumer_secret ) ),
		];
	}

	/**
	 * Is supported?
	 * @return bool
	 */
	public function is_supported() {
		return function_exists( '\wp_remote_request' );
	}
}