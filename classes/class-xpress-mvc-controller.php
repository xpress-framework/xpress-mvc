<?php
/**
 * XPress MVC Controller Class
 *
 * @package    XPress
 * @subpackage MVC
 * @author     Thiago Benvenuto
 * @license    GPLv2
 * @since      0.1.0
 */

/**
 * Base class for a XPress MVC controller.
 * If used as it is, it does nothing. Should be extended to a new class that implements the real controller logic.
 */
abstract class XPress_MVC_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'xpress_mvc_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the routes served by the controller.
	 */
	public function register_routes() {
	}

	/**
	 * Registers a XPress MVC route.
	 *
	 * @since 0.1.0
	 *
	 * @param string $route_id The id of the route. Should be unique across entire WordPress instance. Will be
	 *                         used to create permalinks and load templates.
	 * @param string $route    The base URL for route you are adding.
	 * @param array  $args     Optional. Either an array of options for the endpoint, or an array of arrays for
	 *                         multiple methods. Default empty array.
	 * @param bool   $override Optional. If the route already exists, should we override it? True overrides,
	 *                         false merges (with newer overriding if duplicate keys exist). Default false.
	 * @return bool True on success, false on error.
	 */
	function register_route( $route_id, $route, $args = array(), $override = false ) {
		if ( empty( $route_id ) ) {
			_doing_it_wrong( 'xpress_register_route', __( 'Routes must be have a unique identifier.' ), '0.1.0' );
			return false;
		}

		if ( empty( $route ) ) {
			_doing_it_wrong( 'xpress_register_route', __( 'Route must be specified.' ), '0.1.0' );
			return false;
		}

		if ( isset( $args['args'] ) ) {
			$common_args = $args['args'];
			unset( $args['args'] );
		} else {
			$common_args = array();
		}

		if ( isset( $args['callback'] ) ) {
			// Upgrade a single set to multiple.
			$args = array( $args );
		}

		$defaults = array(
			'methods'         => 'GET',
			'callback'        => null,
			'args'            => array(),
		);
		foreach ( $args as $key => &$arg_group ) {
			if ( ! is_numeric( $key ) ) {
				// Route option, skip here.
				continue;
			}

			$arg_group = array_merge( $defaults, $arg_group );
			$arg_group['args'] = array_merge( $common_args, $arg_group['args'] );
		}

		$full_route = '/' . trim( $route, '/' );
		xpress_mvc_get_server()->register_route( $route_id, $full_route, $args, $override );
		return true;
	}

	/**
	 * Returns a 200 OK XPress_MVC_Response
	 *
	 * @since 0.1.0
	 *
	 * @param mixed  $data     The data to be returned as the view model.
	 * @param string $template The template filename (no folder, no extension) to render.
	 *
	 * @return XPress_MVC_Response
	 */
	public function ok( $data = null, $template = null ) {
		$response = xpress_mvc_ensure_response( $data );

		$response->template = $template;

		return $response;
	}

	/**
	 * Returns a 201 Created XPress_MVC_Response
	 *
	 * @since 0.1.0
	 *
	 * @param string $location The value to be send in the Location header.
	 * @param mixed  $data     The data to be returned as the view model.
	 * @param string $template The template filename (no folder, no extension) to render.
	 *
	 * @return XPress_MVC_Response
	 */
	public function created( $location, $data = null, $template = null ) {
		$response = xpress_mvc_ensure_response( $data );

		$response->header( 'Location', $location, true );
		$response->status = 201;
		$response->template = $template;

		return $response;
	}

	/**
	 * Returns a 301/302 Redirect XPress_MVC_Response
	 *
	 * @since 0.1.0
	 *
	 * @param string $location  The value to be send in the Location header.
	 * @param bool   $permanent Whether the response should be a permanent (301) or temporary (302) redirect.
	 *
	 * @return XPress_MVC_Response
	 */
	public function redirect( $location, $permanent = false ) {
		$response = xpress_mvc_ensure_response( null );

		$response->header( 'Location', $location, true );
		$response->status = $permanent ? 301 : 302;

		return $response;
	}

	/**
	 * Returns a 404 Not Found XPress_MVC_Response
	 *
	 * @since 0.1.0
	 *
	 * @param mixed  $data     The data to be returned as the view model.
	 * @param string $template The template filename (no folder, no extension) to render.
	 *
	 * @return XPress_MVC_Response
	 */
	public function not_found( $data = null, $template = null ) {
		$response = xpress_mvc_ensure_response( $data );

		$response->status = 404;
		$response->template = $template ?: '404';

		return $response;
	}

	/**
	 * Returns a 500 Error XPress_MVC_Response
	 *
	 * @since 0.1.0
	 *
	 * @param mixed  $data     The data to be returned as the view model.
	 * @param string $template The template filename (no folder, no extension) to render.
	 *
	 * @return XPress_MVC_Response
	 */
	public function error( $data = null, $template = null ) {
		$response = xpress_mvc_ensure_response( $data );

		$response->status = 500;
		// Using 404 because WordPress doesn't have an official 500 error template.
		// To override, use a $template from the controller.
		$response->template = $template ?: '404';

		return $response;
	}

} // XPress_MVC_Controller
