<?php
/**
 * XPress MVC Controller Test
 *
 * @package    XPress
 * @subpackage MVC
 * @author     Thiago Benvenuto
 * @license    GPLv2
 * @since      0.2.0
 */

/**
 * Controller Test class.
 */
class XPress_MVC_Controller_Test extends WP_UnitTestCase {
	/**
	 * Creates XPress_MVC_Controller subclass.
	 */
	public function setUp() {
		require_once 'fixtures/class-xpress-mvc-sample-controller.php';
	}

	/**
	 * Create an instance.
	 */
	function test_new_instance() {
		$this->assertInstanceOf( XPress_MVC_Sample_Controller::class, new XPress_MVC_Sample_Controller() );
	}

	/**
	 * Test if the method Ok returns a valid response.
	 */
	function test_ok() {
		$controller = new XPress_MVC_Sample_Controller();

		$data = array(
			'key' => 'value',
		);

		$template = 'test_template';

		// Test empty ok response.
		$response = $controller->ok();
		$this->assertInstanceOf( XPress_MVC_Response::class, $response );

		// Test ok response with data.
		$response = $controller->ok( $data );
		$this->assertInstanceOf( XPress_MVC_Response::class, $response );
		$this->assertEquals( $data, $response->get_data() );

		// Test ok response with template.
		$response = $controller->ok( null, $template );
		$this->assertInstanceOf( XPress_MVC_Response::class, $response );
		$this->assertEquals( $template, $response->template );
	}

	/**
	 * Test if the method Created returns a valid response.
	 */
	function test_created() {
		$controller = new XPress_MVC_Sample_Controller();
		$location = '/test_location/123';

		$data = array(
			'key' => 'value',
		);

		$template = 'test_template';

		// Test empty ok response.
		$response = $controller->created( $location );
		$this->assertInstanceOf( XPress_MVC_Response::class, $response );
		$this->assertArrayHasKey( 'Location', $response->get_headers() );
		$this->assertEquals( $location, $response->get_headers()['Location'] );
		$this->assertEquals( 201, $response->get_status() );

		// Test created response with data.
		$response = $controller->created( $location, $data );
		$this->assertInstanceOf( XPress_MVC_Response::class, $response );
		$this->assertArrayHasKey( 'Location', $response->get_headers() );
		$this->assertEquals( $location, $response->get_headers()['Location'] );
		$this->assertEquals( 201, $response->get_status() );
		$this->assertEquals( $data, $response->get_data() );

		// Test created response with template.
		$response = $controller->created( $location, null, $template );
		$this->assertInstanceOf( XPress_MVC_Response::class, $response );
		$this->assertArrayHasKey( 'Location', $response->get_headers() );
		$this->assertEquals( $location, $response->get_headers()['Location'] );
		$this->assertEquals( 201, $response->get_status() );
		$this->assertEquals( $template, $response->template );
	}

	// TODO: Implement all response methods + register_route test.
}
