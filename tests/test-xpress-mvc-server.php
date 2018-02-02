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
		require 'fixtures/xpress-mvc-sample-routes.php';
		$this->server = xpress_mvc_get_server();
	}

	/**
	 * Test routes registration.
	 */
	function test_register_routes() {
		$routes = $this->server->get_routes();

		$this->assertEquals( 2, count( $routes ) );

		$this->assertArrayHasKey( '/tests/head_request', $routes );
		$this->assertArrayHasKey( '/tests/default_value', $routes );

		$this->assertEquals( 2, count( $routes['/tests/default_value'] ) );

		$this->assertArrayHasKey( 'GET', $routes['/tests/default_value'][0]['methods'] );
		$this->assertEquals( 'test-default-value', $routes['/tests/default_value'][0]['route_id'] );
		$this->assertEquals( '__return_null', $routes['/tests/default_value'][0]['callback'] );

		$this->assertArrayHasKey( 'POST', $routes['/tests/default_value'][1]['methods'] );
		$this->assertEquals( 'test-same-url-route', $routes['/tests/default_value'][1]['route_id'] );
		$this->assertEquals( '__return_null', $routes['/tests/default_value'][1]['callback'] );
	}

	/**
	 * Test routes unregistration.
	 */
	function test_unregister_routes() {
		$this->server = xpress_mvc_get_server();

		$routes = $this->server->get_routes();

		$this->assertEquals( 2, count( $routes ) );

		$this->assertArrayNotHasKey( '/tests/delete_route', $routes );
	}

	/**
	 * Test if a default value is used when not present in request.
	 */
	public function test_default_param() {
		$request = new XPress_MVC_Request( 'GET', '/tests/default_value' );

		$response = $this->server->dispatch( $request );

		$this->assertInstanceOf( 'XPress_MVC_Response', $response );
		$this->assertEquals( 200, $response->get_status() );
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
	 * Test if GET route answers to HEAD requests.
	 */
	public function test_head_request_handled_by_get() {
		$request = new XPress_MVC_Request( 'HEAD', '/tests/head_request' );

		$response = $this->server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
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
