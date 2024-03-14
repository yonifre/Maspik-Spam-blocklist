<?php

/**
 * Class DLM_TestCase
 */
class BaseTestCase extends \PHPUnit\Framework\TestCase {

	/**
	 * The API client
	 * @var \IdeoLogix\DigitalLicenseManagerClient\Service
	 */
	protected $api;

	/**
	 * DLM_TestCase constructor.
	 *
	 * @param string|null $name
	 * @param array $data
	 * @param string $dataName
	 *
	 * @throws Exception
	 */
	public function __construct( ?string $name = null, array $data = [], $dataName = '' ) {
		parent::__construct( $name, $data, $dataName );

		if ( file_exists( dirname( __FILE__ ) . '/credentials.php' ) ) {
			require_once dirname( __FILE__ ) . '/credentials.php';
		}

		$url = defined( 'DLM_API_URL' ) ? DLM_API_URL : null;
		$ck  = defined( 'DLM_API_CK' ) ? DLM_API_CK : null;
		$cs  = defined( 'DLM_API_CS' ) ? DLM_API_CS : null;

		if ( is_null( $url ) || is_null( $ck ) || is_null( $cs ) ) {
			exit( "API credentials not set up yet. Please create credentials.php in tests/ and define DLM_API_CK, DLM_API_CS, DLM_API_URL for access to the api.\n" );
		}

		$this->api = new \IdeoLogix\DigitalLicenseManagerClient\Service( $url, $ck, $cs );

	}

}
