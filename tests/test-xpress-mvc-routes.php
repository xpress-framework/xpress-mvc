<?php
/**
 * XPress MVC Routes Test
 *
 * @package    XPress
 * @subpackage MVC
 * @author     Trasgo Furioso
 * @license    GPLv2
 * @since      0.2.0
 */

/**
 * Routes Test class.
 */
class XPress_MVC_Routes_Test extends WP_UnitTestCase {
	/**
	 * Creates XPress_MVC_Controller subclass.
	 */
	public function setUp() {
		require_once 'fixtures/class-xpress-mvc-sample-routes.php';
		new XPress_MVC_Sample_Routes();
		// Reset MVC server to ensure only our routes are registered.
		$GLOBALS['xpress_mvc_server'] = null;
		$this->server = xpress_mvc_get_server();
	}

	/**
	 * Test if the method ok returns a valid response.
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
}
