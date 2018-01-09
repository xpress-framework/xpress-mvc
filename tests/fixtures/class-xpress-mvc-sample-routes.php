<?php
/**
 * XPress MVC Sample Routes Class
 *
 * @package    XPress
 * @subpackage MVC
 * @author     Trasgo Furioso
 * @license    GPLv2
 * @since      0.2.0
 */

/**
 * The XPress_MVC_Sample_Routes class declaration.
 */
class XPress_MVC_Sample_Routes extends XPress_MVC_Routes {
	/**
	 * Register the test routes.
	 */
	function register_routes() {
		// WordPress REST Server ported tests.
		$this->register_route( 'test-default-value', '/tests/default_value', array(
			'methods' => 'GET',
			'callback' => '__return_null',
		) );

		$this->register_route( 'test-same-url-route', '/tests/default_value', array(
			'methods'  => 'POST',
			'callback' => '__return_null',
		) );

		$this->register_route( 'test-head-request', '/tests/head_request', array(
			'methods'  => 'GET',
			'callback' => '__return_true',
		) );
	}
}
