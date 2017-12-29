<?php
/**
 * XPress Sample MVC Model Class
 *
 * @package    XPress
 * @subpackage MVC
 * @author     Trasgo Furioso
 * @license    GPLv2
 * @since      0.2.0
 */

class Sample_XPress_Model extends XPress_MVC_Model {
	/**
	 * Model schema.
	 *
	 * @since 0.2.0
	 * @var array
	 */
	static protected $schema = array(
		'first_name' => array(
			'description' => 'First Name',
			'type'        => 'string',
			'required'    => true,
		),
		'age' => array(
			'description'       => 'Age',
			'type'              => 'number',
			'validate_callback' => 'static::is_valid_age',
		),
	);

	/**
	 * Return a model instance for a specific item.
	 *
	 * @since 0.2.0
	 *
	 * @return XPress_MVC_Model instance.
	 */
	static function get( $id ) {}

	/**
	 * Return a model instance collection filtered by the params.
	 *
	 * @since 0.2.0
	 *
	 * @return array XPress_MVC_Model instance collection.
	 */
	static function find( $params ) {}

	/**
	 * Persists the current model instance.
	 *
	 * @since 0.2.0
	 *
	 * @return XPress_MVC_Model instance.
	 */
	public function save() {}

	/**
	 * Deleted the current model instance.
	 *
	 * @since 0.2.0
	 *
	 * @return XPress_MVC_Model instance.
	 */
	public function delete() {}

	/**
	 * Validates is value is a valid age.
	 *
	 * @param  $value numeric value.
	 * @return true/WP_Error true if valid, WP_Error if invalid.
	 */
	static function is_valid_age( $value ) {
		if ( 0 < $value && $value < 120 ) {
			$is_valid = true;
		} else {
			$is_valid = new WP_Error( 'xpress_mvc_invalid_param', sprintf( __( '%1$s is not a valid age.' ), $value ) );
		}
		return $is_valid;
	}
}
