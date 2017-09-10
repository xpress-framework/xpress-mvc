<?php
/**
 * Class XPress_MVC_Model
 *
 * @package
 */

/**
 * Model test case.
 */
class XPress_MVC_Model_Test extends WP_UnitTestCase {
	/**
	 * Create an instance.
	 */
	function test_new_instance() {
		$this->assertInstanceOf( XPress_MVC_Model::class, XPress_MVC_Model::new() );
	}

	/**
	 * Set valid attributes.
	 */
	function test_set_attributes() {
		XPress_MVC_Model::set_attributes( array(
			'first_name',
		) );
		$model = XPress_MVC_Model::new();
		$this->assertTrue( isset( $model->first_name ) );
		$this->assertFalse( isset( $model->last_name ) );
	}

	/**
	 * Set attribute value only for valid attributes.
	 */
	function test_set_attribute_value() {
		XPress_MVC_Model::set_attributes( array(
			'first_name',
		) );
		$model = XPress_MVC_Model::new();
		$model->first_name = 'John';
		$this->expectException( XPressInvalidModelAttributeException::class );
		$model->last_name = 'John';
	}

	/**
	 * Get previously set valid attribute.
	 */
	function test_get_attribute_value() {
		XPress_MVC_Model::set_attributes( array(
			'first_name',
		) );
		$model = XPress_MVC_Model::new();
		$model->first_name = 'John';
		$this->assertEquals( 'John', $model->first_name );
	}

	/**
	 * Empty valid attribute returns null.
	 */
	function test_get_empty_attribute_value() {
		XPress_MVC_Model::set_attributes( array(
			'first_name',
		) );
		$model = XPress_MVC_Model::new();
		$this->assertNull( $model->first_name );
	}

	/**
	 * Invalid attribute call throws exception.
	 */
	function test_get_invalid_attribute_value() {
		XPress_MVC_Model::set_attributes( array(
			'first_name',
		) );
		$model = XPress_MVC_Model::new();
		$this->expectException( XPressInvalidModelAttributeException::class );
		$model->last_name;
	}
}

