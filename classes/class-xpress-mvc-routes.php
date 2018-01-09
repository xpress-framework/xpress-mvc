<?php
/**
 * XPress MVC Routes Class
 *
 * @package    XPress
 * @subpackage MVC
 * @author     Trasgo Furioso
 * @license    GPLv2
 * @since      0.1.0
 */

/**
 * Base class for a XPress MVC route.
 */
class XPress_MVC_Routes {
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
			'route_id' => $route_id,
			'methods'  => 'GET',
			'callback' => null,
			'args'     => array(),
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
} // XPress_MVC_Routes
