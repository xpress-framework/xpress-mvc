<?php
/**
 * XPress MVC Model Class
 *
 * @package    XPress
 * @subpackage MVC
 * @author     Trasgo Furioso
 * @license    GPLv2
 * @since      0.2.0
 */

/**
 * Base class for a XPress MVC model.
 * If used as it is, it does nothing. Should be extended to a new class that implements the real model logic.
 */
class XPress_MVC_Model {
	/**
	 * Model schema. MUST define in subclass.
	 *
	 * @since 0.2.0
	 * @var array
	 */
	static protected $schema;

	/**
	 * Model attributes.
	 *
	 * @since 0.2.0
	 * @var array
	 */
	protected $attributes;

	/**
	 * Class constructor. Reads schema and sets attributes.
	 *
	 * @since 0.2.0
	 *
	 * @return XPress_MVC_Model instance.
	 */
	public function __construct( $attributes = array() ) {
		if ( ! empty( $attributes ) ) {
			$this->update( $attributes );
		}
	}

	/**
	 * Returns a new model instance.
	 *
	 * @since 0.2.0
	 *
	 * @return XPress_MVC_Model instance.
	 */
	static function new( $attributes = array() ) {
		return new static( $attributes );
	}

	/**
	 * Returns true if attribute is present in valid_attributes
	 *
	 * @param string $attribute Attribute slug.
	 *
	 * @since 0.2.0
	 *
	 * @return boolean
	 */
	public function __isset( $attribute ) {
		return array_key_exists( $attribute, static::$schema['properties'] );
	}

	/**
	 * Sets attribute value.
	 *
	 * @param string $attribute Attribute slug.
	 *
	 * @param mixed $value Attribute value.
	 *
	 * @since 0.2.0
	 *
	 * @return null
	 */
	public function __set( $attribute, $value ) {
		if ( $this->__isset( $attribute ) ) {
			$this->attributes[ $attribute ] = $value;
		} else {
			throw new XPressInvalidModelAttributeException( $attribute );
		}
	}

	/**
	 * Gets attribute value.
	 *
	 * @param string $attribute Attribute slug.
	 *
	 * @since 0.2.0
	 *
	 * @return mixed Attribute value.
	 */
	public function __get( $attribute ) {
		if ( $this->__isset( $attribute ) ) {
			return $this->attributes[ $attribute ];
		} else {
			throw new XPressInvalidModelAttributeException( $attribute );
		}
	}

	/**
	 * Updates attributes value.
	 *
	 * @param array $attributes Attributes to update.
	 *
	 * @since 0.2.0
	 *
	 * @return boolean
	 */
	public function update( $attributes ) {
		$invalid_attributes = array();
		foreach ( $attributes as $attribute => $value ) {
			if ( ! $this->__isset( $attribute ) ) {
				$invalid_attributes[] = $attribute;
			}
		}
		if ( empty( $invalid_attributes ) ) {
			$this->attributes = $attributes;
			return true;
		} else {
			throw new XPressInvalidModelAttributeException( join( ', ', $invalid_attributes ) );
		}

	}

	/**
	 * Returns the model schema
	 *
	 * @since 0.2.0
	 *
	 * @return array Model schema
	 */
	static function get_schema() {
		return static::$schema;
	}
} // XPress_MVC_Model
