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

}
