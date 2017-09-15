<?php
/**
 * XPress MVC Response Class
 *
 * @package    XPress
 * @subpackage MVC
 * @author     Thiago Benvenuto
 * @license    GPLv2
 * @since      0.1.0
 */

/**
 * Defines a response.
 */
class XPress_MVC_Response extends WP_REST_Response {

	/**
	 * The template to be rendered.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	public $template = null;

	/**
	 * Static constructor that accepts an original WP_HTTP_Response and copy its values to the new XPress_MVC_Response.
	 *
	 * @access public
	 *
	 * @param WP_HTTP_Response $original The original WP_HTTP_Response to copy values from.
	 * @return  XPress_VMC_Response
	 */
	public static function from_wp_http_response( WP_HTTP_Response $original ) {
		return new XPress_MVC_Response( $original->get_data(), $original->get_status(), $original->get_headers() );
 	}
}
