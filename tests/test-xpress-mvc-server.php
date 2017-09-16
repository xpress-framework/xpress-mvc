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
 * Server Test class.
 */
class XPress_MVC_Server_Test extends WP_UnitTestCase {
	/**
	 * Initialize XPress_MVC_Server.
	 */
	public function setUp() {
		require_once 'fixtures/class-xpress-mvc-sample-controller.php';
		$this->controller = new XPress_MVC_Sample_Controller();

		// Reset MVC server to ensure only our routes are registered.
		$GLOBALS['xpress_mvc_server'] = null;
		$this->server = xpress_mvc_get_server();
	}

	/**
	 * Test if a default value is used when not present in request.
	 */
	public function test_default_param() {
		$request = new XPress_MVC_Request( 'GET', '/tests/default_value' );
		$default = array(
			'foo' => 'bar',
		);

		$response = $this->server->dispatch( $request );

		$this->assertInstanceOf( 'XPress_MVC_Response', $response );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( $default, $request->get_params() );
		$this->assertEquals( 'bar', $request['foo'] );
	}

	/**
	 * Test if when define, a default value is overriden.
	 */
	public function test_default_param_is_overridden() {
		$request = new XPress_MVC_Request( 'GET', '/tests/default_value' );
		$request->set_query_params( array(
			'foo' => 123,
		) );

		$response = $this->server->dispatch( $request );

		$this->assertInstanceOf( 'XPress_MVC_Response', $response );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( '123', $request['foo'] );
	}

	/**
	 * Test if an optional value is not return when not present in request.
	 */
	public function test_optional_param() {
		$request = new XPress_MVC_Request( 'GET', '/tests/optional_value' );
		$request->set_query_params( array() );

		$response = $this->server->dispatch( $request );

		$this->assertInstanceOf( 'XPress_MVC_Response', $response );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayNotHasKey( 'foo', (array) $request );
	}

	/**
	 * Test if GET route answers to HEAD requests.
	 */
	public function test_head_request_handled_by_get() {
		$request = new XPress_MVC_Request( 'HEAD', '/tests/head_request' );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
	}


	/**
	 * Ensure request is sent to callback in case of invalid arguments.
	 */
	public function test_invalid_argument() {
		$request = new XPress_MVC_Request( 'GET', '/tests/invalid_argument' );
		$request->set_query_params( array(
		 	'foo' => 'abc',
		) );
		$errors = array(
			'foo' => array(
				'Invalid parameter.',
		  	),
		);
		$result = array(
			'params'     => $request->get_params(),
			'has_errors' => true,
			'errors'     => $errors,
		);

		$response = $this->server->dispatch( $request );

		$this->assertInstanceOf( 'XPress_MVC_Response', $response );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( $result, $response->get_data() );
	}

	/**
	 * Ensures the system finds a route by its route id.
	 */
	public function test_get_route_permalink() {
		$permalink = $this->server->get_route_permalink( 'test-default-value' );
		$this->assertContains( '/tests/default_value', $permalink );

		$permalink = $this->server->get_route_permalink( 'test-same-url-route' );
		$this->assertContains( '/tests/default_value', $permalink );

		$permalink = $this->server->get_route_permalink( 'invalid-route-id' );
		$this->assertNull( $permalink );
	}
}
