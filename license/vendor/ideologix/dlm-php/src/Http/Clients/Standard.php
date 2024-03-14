<?php

namespace IdeoLogix\DigitalLicenseManagerClient\Http\Clients;

use IdeoLogix\DigitalLicenseManagerClient\Http\Responses\Base as BaseResponse;
use IdeoLogix\DigitalLicenseManagerClient\Http\Responses\Error;
use IdeoLogix\DigitalLicenseManagerClient\Http\Responses\Result;

use IdeoLogix\DigitalLicenseManagerClient\Utils\FileSystem;
use WpOrg\Requests\Auth\Basic;
use WpOrg\Requests\Requests;
use WpOrg\Requests\Response;

class Standard extends Base {

	/**
	 * The curl http client
	 * @var string
	 */
	protected $id = 'Requests';

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
		$response = Requests::get( $url, $this->get_headers(), $this->get_options() );
		$decoded  = $this->json_decode( $response->body );

		if ( ! $response->success && ! $decoded ) {
			return new Error( $response->status_code, $response->body, array() );
		} else {
			return $this->result( $decoded );
		}
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
		$url      = $this->url( $path );
		$response = Requests::post( $url, $this->get_headers(), $data, $this->get_options() );
		$decoded  = $this->json_decode( $response->body );

		if ( ! $response->success && ! $decoded ) {
			return new Error( $response->status_code, $response->body, array() );
		} else {
			return $this->result( $decoded );
		}
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
		$url      = $this->url( $path );
		$response = Requests::put( $url, $this->get_headers(), $data, $this->get_options() );
		$decoded  = $this->json_decode( $response->body );

		if ( ! $response->success && ! $decoded ) {
			return new Error( $response->status_code, $response->body, array() );
		} else {
			return $this->result( $decoded );
		}
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
		$response = Requests::delete( $url, $this->get_headers(), $this->get_options() );
		$decoded  = $this->json_decode( $response->body );

		if ( ! $response->success && ! $decoded ) {
			return new Error( $response->status_code, $response->body, array() );
		} else {
			return $this->result( $decoded );
		}
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
		$response = Requests::get( $url, $this->get_headers(), $this->get_options() );
		$decoded  = $this->json_decode( $response->body );

		if ( ! $response->success && ! $decoded ) {
			return new Error( $response->status_code, $response->body, array() );
		} else {

			if ( $decoded ) {
				return $this->result( $decoded );
			} else {
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
	}

	/**
	 * Is supported?
	 * @return bool
	 */
	public function is_supported() {
		return true;
	}

	/**
	 * Check if $filename is set, if it is set, return it. Otherwise try to find it from the Content-Disposition header.
	 * If it is still not present generate one based on the content type.
	 *
	 * @param Response $response
	 * @param $filename
	 *
	 * @return mixed
	 */
	private function ensure_filename( $response, $filename ) {
		if ( ! empty( $filename ) ) {
			return $filename;
		}
		$header = ! empty( $response->headers['Content-Disposition'] ) ? $response->headers['Content-Disposition'] : '';
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
	 * @param array $result
	 *
	 * @return Error|Result
	 */
	private function result( $result ) {

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
	 * Return the requests options.
	 * @return array|Basic[]
	 */
	private function get_options() {
		try {
			$options = array(
				'auth' => new Basic( array( $this->consumer_key, $this->consumer_secret ) )
			);
		} catch ( \Exception $e ) {
			$options = array();
		}

		return $options;
	}

	/**
	 * Return the default headers.
	 * @return array
	 */
	private function get_headers() {
		return array( 'User-Agent' => $this->user_agent );
	}
}
