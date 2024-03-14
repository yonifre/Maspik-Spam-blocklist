<?php

require_once dirname( __FILE__ ) . '/BaseTestCase.php';

class GeneratorsTest extends BaseTestCase {

	private function getInstance( $type = 'generators' ) {
		if ( 'licenses' === $type ) {
			return $this->api->licenses();
		}

		return $this->api->generators();
	}

	public function testInstanceClass() {
		$this->assertInstanceOf( 'IdeoLogix\DigitalLicenseManagerClient\Http\Requests\Generators', $this->getInstance() );
	}

	public function testGeneratorsCrud() {

		// 1. CREATE
		$response = $this->getInstance()->create( [
			'name'              => 'Example Generator',
			'charset'           => 'ABCD1234',
			'chunks'            => 4,
			'chunk_length'      => 4,
			'activations_limit' => 3,
			'separator'         => '-',
			'expires_in'        => 366,
		] );
		$this->assertNotTrue( $response->is_error() );
		$this->assertEquals( 'Example Generator', $response->get_data( 'name' ) );
		$this->assertEquals( 366, $response->get_data( 'expires_in' ) );
		$this->assertEquals( 3, $response->get_data( 'activations_limit' ) );
		$genId = $response->get_data( 'id' );

		// 2. UPDATE
		$response = $this->getInstance()->update( $genId, [
			'name'              => 'Example Generator #3',
			'charset'           => 'ABCDEFGH123456789',
			'chunks'            => 3,
			'chunk_length'      => 5,
			'activations_limit' => 1,
			'expires_in'        => 365,
		] );
		$this->assertNotTrue( $response->is_error() );
		$this->assertEquals( 'Example Generator #3', $response->get_data( 'name' ) );
		$this->assertEquals( 'ABCDEFGH123456789', $response->get_data( 'charset' ) );
		$this->assertEquals( 3, $response->get_data( 'chunks' ) );
		$this->assertEquals( 5, $response->get_data( 'chunk_length' ) );
		$this->assertEquals( 1, $response->get_data( 'activations_limit' ) );
		$this->assertEquals( 365, $response->get_data( 'expires_in' ) );

		// 3. FIND
		$response = $this->getInstance()->find( $genId );
		$this->assertNotTrue( $response->is_error() );
		$this->assertEquals( $genId, $response->get_data( 'id' ) );
		$this->assertEquals( 'Example Generator #3', $response->get_data( 'name' ) );

		// 4. GET
		$response = $this->getInstance()->get();
		$this->assertNotTrue( $response->is_error() );
		$this->assertTrue( count( $response->get_data() ) > 0 );

		// 5. GENERATE
		$response = $this->getInstance()->generate( $genId, array(
			'amount' => 5,
			'status' => $this->getInstance( 'licenses' )::STATUS_ACTIVE,
			'save'   => 1,
		) );
		$this->assertNotTrue( $response->is_error() );
		$this->assertIsArray( $response->get_data() );
		if ( is_array( $response->get_data() ) ) {
			foreach ( $response->get_data() as $license_key ) {
				$x = $this->getInstance( 'licenses' )->delete( $license_key );
				$this->assertNotTrue( $x->is_error() );
			}
		}

		// 6. DELETE
		$response = $this->getInstance()->delete( $genId );
		$this->assertNotTrue( $response->is_error() );
		$response = $this->getInstance()->find( $genId );
		$this->assertTrue( $response->is_error() );
	}

}
