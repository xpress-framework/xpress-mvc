<?php
/**
 * XPress MVC Sample Cotroller Class
 *
 * @package    XPress
 * @subpackage MVC
 * @author     Thiago Benvenuto
 * @license    GPLv2
 * @since      0.2.0
 */

/**
 * The XPress_MVC_Sample_Controller class declaration.
 */
class XPress_MVC_Sample_Controller extends XPress_MVC_Controller {

	/**
	 * Register the test routes.
	 */
	function register_routes() {
		// WordPress REST Server ported tests.
		$this->register_route( 'test-default-value', '/tests/default_value', array(
			'methods' => array( 'GET' ),
			'callback' => '__return_null',
			'args'     => array(
				'foo'  => array(
					'default'  => 'bar',
				),
			),
		) );

		$this->register_route( 'test-optional-value', '/tests/optional_value', array(
			'methods'  => array( 'GET' ),
			'callback' => '__return_null',
			'args'     => array(
				'foo'  => array(),
			),
		) );

		$this->register_route( 'test-head-request', '/tests/head_request', array(
			'methods'  => array( 'GET' ),
			'callback' => '__return_true',
		) );

		// XPress tests.
		$this->register_route( 'test-invalid-argument', '/tests/invalid_argument', array(
			'methods' => array( 'GET' ),
			'callback' => array( $this, 'return_params_and_errors' ),
			'args'     => array(
				'foo'  => array(
					'validate_callback' => function( $param, $request, $key ) {
						return is_numeric( $param );
					},
				),
			),
		) );

		$this->register_route( 'test-same-url-route', '/tests/default_value', array(
			'methods'  => array( 'POST' ),
			'callback' => '__return_null',
		) );
	}

	/**
	 * Callback to be used in tests.
	 * @param  WP_REST_Request $request The request object.
	 * @return array                    The array will contain: params, has_errors and errors, bypassed from the request object.
	 */
	public function return_params_and_errors( XPress_MVC_Request $request ) {
		return array(
			'params'     => $request->get_params(),
			'has_errors' => $request->has_errors(),
			'errors'     => $request->get_errors(),
		);
	}
}
