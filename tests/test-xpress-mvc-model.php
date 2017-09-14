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
	 * Creates XPress_MVC_Model subclass with schema.
	 */
	public function setUp() {
		require_once 'fixtures/class-sample-xpress-model.php';
	}

	/**
	 * Create an instance.
	 */
	function test_new_instance() {
		$this->assertInstanceOf( Sample_XPress_Model::class, Sample_XPress_Model::new() );
	}

	/**
	 * Set valid attributes from schema.
	 */
	function test_set_attributes() {
		$model = Sample_XPress_Model::new();
		$this->assertTrue( isset( $model->first_name ) );
		$this->assertFalse( isset( $model->invalid_param ) );
	}

	/**
	 * Set attribute value only for valid attributes.
	 */
	function test_set_attribute_value() {
		$model = Sample_XPress_Model::new();
		$this->expectException( XPressInvalidModelAttributeException::class );
		$model->invalid_param = 'John';
	}

	/**
	 * Get previously set valid attribute.
	 */
	function test_get_attribute_value() {
		$model = Sample_XPress_Model::new();
		$model->first_name = 'John';
		$this->assertEquals( 'John', $model->first_name );
	}

	/**
	 * Set values at instance creation.
	 */
	function test_set_attribute_value_at_new() {
		$model = Sample_XPress_Model::new( array(
			'first_name' => 'John',
		) );
		$this->assertEquals( 'John', $model->first_name );
	}

	/**
	 * Empty valid attribute returns null.
	 */
	function test_get_empty_attribute_value() {
		$model = Sample_XPress_Model::new();
		$this->assertNull( $model->first_name );
	}

	/**
	 * Invalid attribute call throws exception.
	 */
	function test_get_invalid_attribute_value() {
		$model = Sample_XPress_Model::new();
		$this->expectException( XPressInvalidModelAttributeException::class );
		$model->invalid_param;
	}

	/**
	 * Updates attributes and return true.
	 */
	function test_update_attributes() {
		$model = Sample_XPress_Model::new();
		$model->first_name = 'John';
		$new_values = array(
			'first_name' => 'Mary',
		);
		$this->assertTrue( $model->update( $new_values ) );
		$this->assertEquals( 'Mary', $model->first_name );
	}

	/**
	 * Fail to update all attributes if any attribute is invalid.
	 */
	function test_fail_update_attributes() {
		$model = Sample_XPress_Model::new();
		$model->first_name = 'John';
		$new_values = array(
			'first_name' => 'Mary',
			'invalid_param' => 'Doe',
		);
		try {
			$model->update( $new_values );
		} catch ( XPressInvalidModelAttributeException $e ) {
			$this->assertEquals( 'John', $model->first_name );
		}
	}

	/**
	 * Returns model schema
	 */
	function test_return_schema() {
		$schema = Sample_XPress_Model::get_schema();
		$this->assertInternalType( 'array', $schema );
		$this->assertArrayHasKey( '$schema', $schema );
		$this->assertArrayHasKey( 'title', $schema );
		$this->assertArrayHasKey( 'type', $schema );
		$this->assertArrayHasKey( 'properties', $schema );
		$this->assertEquals( 'sample-xpress-model', $schema['title'] );
	}

	/**
	 * Validates model against schema using standard WordPress validation
	 */
	function test_validate() {
		$model = Sample_XPress_Model::new( array(
			'first_name' => 'John',
			'age'        => 35,
		) );
		$this->assertTrue( $model->is_valid() );
		$model = Sample_XPress_Model::new( array(
			'first_name' => 1,
			'age'        => 35,
		) );
		$this->assertFalse( $model->is_valid() );
		$model = Sample_XPress_Model::new( array(
			'first_name' => null,
			'age'        => 35,
		) );
		$this->assertFalse( $model->is_valid() );
		$model = Sample_XPress_Model::new( array(
			'first_name' => 'John',
			'age'        => array( '35' ),
		) );
		$this->assertFalse( $model->is_valid() );
		$model = Sample_XPress_Model::new();
		$this->assertFalse( $model->is_valid() );
	}

	/**
	 * Populates errors if model is invalid
	 */
	function test_model_errors() {
		// Validation errors
		$model = Sample_XPress_Model::new( array(
			'first_name' => 1,
			'age'        => array( '35' ),
		) );
		$model->is_valid();
		$this->assertCount( 2, $model->get_errors() );
		$this->assertNotEmpty( $model->get_errors()['first_name'] );
		$this->assertNotEmpty( $model->get_errors()['age'] );
		$this->assertEquals( 'First Name is not of type string.', $model->get_errors()['first_name'] );
		$this->assertEquals( 'Age is not of type number.', $model->get_errors()['age'] );

		// Required fields
		$model = Sample_XPress_Model::new();
		$model->is_valid();
		$this->assertNotEmpty( $model->get_errors()['first_name'] );
		$this->assertEquals( 'First Name is required.', $model->get_errors()['first_name'] );
		$model = Sample_XPress_Model::new( array(
			'first_name' => '',
		) );
		$model->is_valid();
		$this->assertNotEmpty( $model->get_errors()['first_name'] );
		$this->assertEquals( 'First Name is required.', $model->get_errors()['first_name'] );
		$model = Sample_XPress_Model::new( array(
			'first_name' => null,
		) );
		$model->is_valid();
		$this->assertNotEmpty( $model->get_errors()['first_name'] );
		$this->assertEquals( 'First Name is required.', $model->get_errors()['first_name'] );
	}
}

