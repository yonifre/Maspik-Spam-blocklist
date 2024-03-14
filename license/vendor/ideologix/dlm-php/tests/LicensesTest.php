<?php

require_once dirname( __FILE__ ) . '/BaseTestCase.php';

class LicensesTest extends BaseTestCase {

	private function getInstance() {
		return $this->api->licenses();
	}

	public function testInstanceClass() {
		$this->assertInstanceOf( 'IdeoLogix\DigitalLicenseManagerClient\Http\Requests\Licenses', $this->getInstance() );
	}

	public function testLicensesCrud() {

		// 1. CREATE
		$key      = 'YUPI-YUPI-YUPI-' . mt_rand( 1000, 9999 );
		$response = $this->getInstance()->create( [
			'order_id'          => 10,
			'product_id'        => 10,
			'user_id'           => 1,
			'license_key'       => $key,
			'valid_for'         => 365,
			'activations_limit' => 1,
			'status'            => $this->getInstance()::STATUS_ACTIVE
		] );
		$this->assertNotTrue( $response->is_error() );
		$this->assertEquals( $key, $response->get_data( 'license_key' ) );
		$response = $this->getInstance()->create( [
			'order_id'          => 10,
			'product_id'        => 10,
			'user_id'           => 1,
			'license_key'       => $key,
			'valid_for'         => 365,
			'activations_limit' => 1,
			'status'            => $this->getInstance()::STATUS_ACTIVE
		] );
		$this->assertTrue( $response->is_error() );

		// 2. UPDATE
		$response = $this->getInstance()->update( $key, [
			'order_id'          => 15,
			'product_id'        => 15,
			'user_id'           => 2,
			'activations_limit' => 2,
			'status'            => $this->getInstance()::STATUS_SOLD,
		] );
		$this->assertEquals( 15, $response->get_data( 'order_id' ) );
		$this->assertEquals( 15, $response->get_data( 'product_id' ) );
		$this->assertEquals( 2, $response->get_data( 'user_id' ) );
		$this->assertEquals( 2, $response->get_data( 'activations_limit' ) );
		$this->assertEquals( $this->getInstance()::STATUS_SOLD, $response->get_data( 'status' ) );

		// 3. FIND
		$response = $this->getInstance()->find( $key );
		$this->assertNotTrue( $response->is_error() );
		$this->assertEquals( $key, $response->get_data( 'license_key' ) );
		$this->assertIsArray( $response->get_data() );
		$this->assertFalse( $response->get_data( 'is_expired' ) );
		$response = $this->getInstance()->find( 'XXXX-XXXX-XXXX-' . mt_rand( 1000, 9999 ) );
		$this->assertTrue( $response->is_error() );
		$this->assertIsString( $response->get_message() );
		$this->assertIsString( $response->get_code() );

		// 4. GET
		$response = $this->getInstance()->get();
		$this->assertNotTrue( $response->is_error() );
		$this->assertIsArray( $response->get_data() );
		$this->assertIsBool( count( $response->get_data() ) >= 1 );

		// 5. ACTIVATE
		// (NOTE: This requires the hard validation turned off. Eg:
		// add_filter('dlm_rest_api_license_activation_require_software_param', '__return_false');
		$response1 = $this->getInstance()->activate( $key, [ 'label' => 'site1.com' ] );
		$this->assertNotTrue( $response1->is_error() );
		$token1    = $response1->get_data( 'token' );
		$response2 = $this->getInstance()->activate( $key, [ 'label' => 'site2.com' ] );
		$this->assertNotTrue( $response2->is_error() );
		$token2    = $response2->get_data( 'token' );
		$response3 = $this->getInstance()->activate( $key, [ 'label' => 'site3.com' ] );
		$this->assertTrue( $response3->is_error() );

		// 6. VALIDATE
		foreach ( array( 'site1.com' => $token1, 'site2.com' => $token2 ) as $label => $token ) {
			$response = $this->getInstance()->validate( $token );
			$this->assertNotTrue( $response->is_error() );
			$this->assertEquals( $label, $response->get_data( 'label' ) );
		}

		// 7. DEACTIVATE
		foreach ( array( 'site1.com' => $token1, 'site2.com' => $token2 ) as $label => $token ) {
			$response = $this->getInstance()->deactivate( $token );
			$this->assertNotTrue( $response->is_error() );
			$this->assertEquals( $label, $response->get_data( 'label' ) );
		}

		// 5. DELETE
		$response = $this->getInstance()->delete( $key );
		$this->assertNotTrue( $response->is_error() );
		$response = $this->getInstance()->find( $key );
		$this->assertTrue( $response->is_error() );

	}
}
