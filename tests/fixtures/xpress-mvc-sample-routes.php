<?php
/**
 * XPress MVC Sample Routes declaration
 *
 * @package    XPress
 * @subpackage MVC
 * @author     Trasgo Furioso
 * @license    GPLv2
 * @since      0.2.0
 */

xpress_mvc_register_routes( function() {
	xpress_mvc_register_route( 'test-default-value', '/tests/default_value', array(
		'methods' => 'GET',
		'callback' => '__return_null',
	) );

	xpress_mvc_register_route( 'test-same-url-route', '/tests/default_value', array(
		'methods'  => 'POST',
		'callback' => '__return_null',
	) );

	xpress_mvc_register_route( 'test-head-request', '/tests/head_request', array(
		'methods'  => 'GET',
		'callback' => '__return_true',
	) );
} );
