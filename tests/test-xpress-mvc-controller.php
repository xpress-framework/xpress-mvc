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
	 * Ensure the XPress_MVC_Controller is abstract.
	 */
	function test_ensure_abstract_controller() {
		$this->expectException( Error::class );
		$this->assertInstanceOf( XPress_MVC_Controller::class, new XPress_MVC_Controller() );
	}

	/**
	 * Create an instance.
	 */
	function test_new_instance() {
		$this->assertInstanceOf( XPress_MVC_Sample_Controller::class, new XPress_MVC_Sample_Controller() );
	}

	/**
	 * Test if the method ok returns a valid response.
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
	 * Test if the method created returns a valid response.
	 */
	function test_created() {
		$controller = new XPress_MVC_Sample_Controller();
		$location = '/test_location/123';

		$data = array(
			'key' => 'value',
		);

		$template = 'test_template';

		// Test empty created response.
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

	/**
	 * Test if the method redirect returns a valid response.
	 */
	function test_redirect() {
		$controller = new XPress_MVC_Sample_Controller();
		$location = '/test_location/123';

		// Test temporary redirect.
		$response = $controller->redirect( $location, false );
		$this->assertInstanceOf( XPress_MVC_Response::class, $response );
		$this->assertArrayHasKey( 'Location', $response->get_headers() );
		$this->assertEquals( $location, $response->get_headers()['Location'] );
		$this->assertEquals( 302, $response->get_status() );

		// Test permanent redirect.
		$response = $controller->redirect( $location, true );
		$this->assertInstanceOf( XPress_MVC_Response::class, $response );
		$this->assertArrayHasKey( 'Location', $response->get_headers() );
		$this->assertEquals( $location, $response->get_headers()['Location'] );
		$this->assertEquals( 301, $response->get_status() );
	}

	/**
	 * Test if the method not_found returns a valid response.
	 */
	function test_not_found() {
		$controller = new XPress_MVC_Sample_Controller();

		$data = array(
			'key' => 'value',
		);

		$template = 'test_template';

		// Test empty not_found response.
		$response = $controller->not_found();
		$this->assertInstanceOf( XPress_MVC_Response::class, $response );
		$this->assertEquals( 404, $response->get_status() );
		$this->assertEquals( '404', $response->template );

		// Test not_found response with data.
		$response = $controller->not_found( $data );
		$this->assertInstanceOf( XPress_MVC_Response::class, $response );
		$this->assertEquals( 404, $response->get_status() );
		$this->assertEquals( $data, $response->get_data() );

		// Test not_found response with template.
		$response = $controller->not_found( null, $template );
		$this->assertInstanceOf( XPress_MVC_Response::class, $response );
		$this->assertEquals( 404, $response->get_status() );
		$this->assertEquals( $template, $response->template );
	}

	/**
	 * Test if the method not_found returns a valid response.
	 */
	function test_error() {
		$controller = new XPress_MVC_Sample_Controller();

		$data = array(
			'key' => 'value',
		);

		$template = 'test_template';

		// Test empty error response.
		$response = $controller->error();
		$this->assertInstanceOf( XPress_MVC_Response::class, $response );
		$this->assertEquals( 500, $response->get_status() );
		$this->assertEquals( '404', $response->template );

		// Test error response with data.
		$response = $controller->error( $data );
		$this->assertInstanceOf( XPress_MVC_Response::class, $response );
		$this->assertEquals( 500, $response->get_status() );
		$this->assertEquals( $data, $response->get_data() );

		// Test error response with template.
		$response = $controller->error( null, $template );
		$this->assertInstanceOf( XPress_MVC_Response::class, $response );
		$this->assertEquals( 500, $response->get_status() );
		$this->assertEquals( $template, $response->template );
	}

	// TODO: Implement register_route test.
}
