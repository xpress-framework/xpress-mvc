<?php
/**
 * Plugin Name: Xpress MVC
 * Version: 0.1.0
 * Plugin URI: https://github.com/xpress-framework/xpress-mvc
 * Description: Implements a MVC-like platform in a WordPress site.
 * Author: Xpress Framework
 * Author URI: https://github.com/xpress-framework
 * Requires at least: 4.8
 * Tested up to: 4.8.1
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain: xpress-mvc
 * Domain Path: /lang/
 *
 * @package    Xpress
 * @subpackage MVC
 */

// Require necessary classes.
require_once 'classes/class-xpress-mvc-no-route.php';
require_once 'classes/class-xpress-mvc-response.php';
require_once 'classes/class-xpress-mvc-server.php';
require_once 'classes/class-xpress-mvc-controller.php';

// Hook to parse_request to handle the MVC requests.
add_action( 'parse_request', 'xpress_mvc_loaded' );

/**
 * Loads the Xpress MVC.
 *
 * @since 0.1.0
 */
function xpress_mvc_loaded() {
	// Initialize the server.
	$server = xpress_mvc_get_server();

	// Fire off the request.
	$server->serve_request();
}

/**
 * Retrieves the current Xpress MVC server instance.
 *
 * Instantiates a new instance if none exists already.
 *
 * @since 0.1.0
 *
 * @global Xpress_MVC_Server $xpress_mvc_server Xpress MVC server instance.
 *
 * @return Xpress_MVC_Server Xpress MVC server instance.
 */
function xpress_mvc_get_server() {
	/* @var WP_REST_Server $wp_rest_server */
	global $xpress_mvc_server;

	if ( empty( $xpress_mvc_server ) ) {
		$xpress_mvc_server = new Xpress_MVC_Server;

		/**
		 * Fires when preparing to serve an API request.
		 *
		 * Endpoint objects should be created and register their hooks on this action rather
		 * than another action to ensure they're only loaded when needed.
		 *
		 * @since 0.1.0
		 *
		 * @param Xpress_MVC_Server $wp_rest_server Server object.
		 */
		do_action( 'xpress_mvc_init', $xpress_mvc_server );
	}

	return $xpress_mvc_server;
}

/**
 * Ensures a REST response is a response object (for consistency).
 *
 * This implements WP_HTTP_Response, allowing usage of `set_status`/`header`/etc
 * without needing to double-check the object. Will also allow WP_Error to indicate error
 * responses, so users should immediately check for this value.
 *
 * @since 0.1.0
 *
 * @param WP_Error|WP_HTTP_Response|mixed $response Response to check.
 * @return Xpress_MVC_Response|mixed If response generated an error, WP_Error, if response
 *                                is already an instance, WP_HTTP_Response, otherwise
 *                                returns a new Xpress_MVC_Response instance.
 */
function xpress_mvc_ensure_response( $response ) {
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	if ( $response instanceof WP_HTTP_Response ) {
		return $response;
	}
	return new Xpress_MVC_Response( $response );
}

/**
 * Builds a permalink for a given route id and an array of arguments.
 *
 * @since 0.1.0
 *
 * @param string $route_id  Route to build the permalink.
 * @param array  $arguments An array where each argument is a key and contains the value to be used in the route url.
 * @return string|null      The route permalink with the arguments populated or null if invalid $route_id.
 */
function xpress_mvc_get_route_permalink( $route_id, $arguments = array() ) {
	return xpress_mvc_get_server()->get_route_permalink( $route_id, $arguments );
}

class TestController extends Xpress_MVC_Controller {
	function register_routes() {
		$this->register_route( 'single-test', '/test/(?P<id>\d+)', array(
			'methods' => 'GET',
			'callback' => array( $this, 'single_test' ),
			'args' => array(
				'x' => array(
					'validate_callback' => function($param, $request, $key) {
						return ! is_numeric( $param );
					}
				),
			),
		) );
	}

	function single_test( WP_REST_Request $request ) {
 		if ( $request->get_param( 'id' ) == 404 )
 			return $this->not_found();

		return $request->get_params();
	}
}
new TestController();