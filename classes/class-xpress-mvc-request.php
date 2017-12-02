<?php
/**
 * XPress MVC Request Class
 *
 * @package    XPress
 * @subpackage MVC
 * @author     Thiago Benvenuto
 * @license    GPLv2
 * @since      0.2.0
 */

/**
 * Defines a request with error handling.
 */
class XPress_MVC_Request extends WP_REST_Request {

	/**
	 * Contains all the errors found during validation of request parameters.
	 *
	 * @access protected
     *
	 * @since 0.2.0
	 * @var string
	 */
	protected $errors = array();

	/**
	 * Check if there are errors in the request parameters.
	 * @return boolean True if errors are found, false if not.
	 */
	public function has_errors() {
		return ! empty( $this->errors );
	}

	/**
	 * Return all errors.
	 * @return array An array where each parameter is the key and contains an array of errors.
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Validates and sanitizes the parameters of the request.
	 */
	public function process_params() {
		$check_validated = $this->has_valid_params();

		if ( is_wp_error( $check_validated ) ) {
			$this->parse_rest_invalid_params( $check_validated );
		} else {
			$check_sanitized = $this->sanitize_params();
			if ( is_wp_error( $check_sanitized ) ) {
				$this->parse_rest_invalid_params( $check_sanitized );
			}
		}
	}

	/**
	 * Retrieves merged parameters from the request.
	 *
	 * The equivalent of get_param(), but returns all parameters for the request.
	 * Handles merging all the available values into a single array.
	 *
	 * @since 0.2.0
	 *
	 * @return array Map of key to value.
	 */
	public function get_params() {
		$params = parent::get_params();

		unset( $params['_method'] );

		return $params;
	}

	/**
	 * Parses error data from rest_invalid_param error in a WP_Error object.
	 * @param  WP_Error $error The WP_Error object containing the error data.
	 */
	protected function parse_rest_invalid_params( WP_Error $error ) {
		$error_data = $error->get_error_data( 'rest_invalid_param' );

		if ( ! empty( $error_data ) && array_key_exists( 'params', $error_data ) ) {
			foreach ( $error_data['params'] as $param_name => $error_message ) {
				if ( ! isset( $errors[ $param_name ] ) ) {
					$this->errors[ $param_name ] = array();
				}

				if ( is_array( $error_message ) ) {
					$this->errors[ $param_name ] = array_merge( $this->errors[ $param_name ], $error_message );
				} else {
					$this->errors[ $param_name ][] = $error_message;
				}
			}
		}
	}
}
