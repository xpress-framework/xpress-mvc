<?php
/**
 * XPress Invalid Model Attribute Exception
 *
 * @package    XPress
 * @subpackage MVC
 * @author     Trasgo Furioso
 * @license    GPLv2
 * @since      0.2.0
 */

class XPressInvalidModelAttributeException extends Exception {
	public function __construct( $message, $code = 1, Exception $previous = null ) {
		$message = 'Invalid attribute: ' . $message;
		parent::__construct( $message, $code, $previous );
	}
}
