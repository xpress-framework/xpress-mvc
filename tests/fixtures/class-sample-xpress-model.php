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

if ( ! class_exists( 'Sample_XPress_Model' ) ) {
	class Sample_XPress_Model extends XPress_MVC_Model {
		static protected $schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'sample-xpress-model',
			'type'       => 'object',
			'properties' => array(
				'first_name' => array(
					'description' => 'Unique identifier for the object.',
					'type'        => 'string',
				),
			),
		);

		static function get( $id ) {}

		static function find( $params ) {}

		public function save() {}

		public function delete() {}
	}
}
