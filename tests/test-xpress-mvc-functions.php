<?php
/**
 * XPress MVC Functions Test
 *
 * @package    XPress
 * @subpackage MVC
 * @author     Thiago Benvenuto
 * @license    GPLv2
 * @since      0.2.0
 */

/**
 * Functions Test class.
 */
class XPress_MVC_Functions_Test extends WP_UnitTestCase {
	/**
	 * Test xpress_mvc_ensure_response with WP_Error.
	 */
	function test_ensure_response_with_wp_error() {
		$error = new WP_Error( 'test', 'This is a test error.' );

		$response = xpress_mvc_ensure_response( $error );

		$this->assertInstanceOf( WP_Error::class, $response );
		$this->assertEquals( $error, $response );
	}

	/**
	 * Test xpress_mvc_ensure_response with WP_HTTP_Response.
	 */
	function test_ensure_response_with_wp_http_response() {
		$data = array(
			'key' => 'value',
		);
		$wp_http_response = new WP_HTTP_Response( $data, 200 );

		$response = xpress_mvc_ensure_response( $wp_http_response );

		$this->assertInstanceOf( XPress_MVC_Response::class, $response );
		$this->assertEquals( $wp_http_response->get_data(), $response->get_data() );
		$this->assertEquals( $wp_http_response->get_status(), $response->get_status() );
		$this->assertEquals( $wp_http_response->get_headers(), $response->get_headers() );
	}

	/**
	 * Test xpress_mvc_ensure_response with data.
	 */
	function test_ensure_response_with_data() {
		$data = array(
			'key' => 'value',
		);

		$response = xpress_mvc_ensure_response( $data );

		$this->assertInstanceOf( XPress_MVC_Response::class, $response );
		$this->assertEquals( $data, $response->get_data() );
	}
}
