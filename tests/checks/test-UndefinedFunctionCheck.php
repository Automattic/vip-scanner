<?php

require_once( 'CodeCheckTestBase.php' );

class UndefinedFunctionTest extends CodeCheckTestBase {

	public function testUndefinedFunction() {
		// FIXME: So far, only calls to undefined free standing functions are
		// detected. The check and tests need to be modfied to also include
		// undefined class methods etc.
		$expected_errors =  array(
				array( 'slug' => 'undefined-function', 'level' => 'blocker', 'description' => 'Undefined function found: undefined_function1', 'file' => 'UndefinedFunctionTest.inc', 'lines' => 26 ),
				array( 'slug' => 'undefined-function', 'level' => 'blocker', 'description' => 'Undefined function found: undefined_function2', 'file' => 'UndefinedFunctionTest.inc', 'lines' => 27 ),
				array( 'slug' => 'undefined-function', 'level' => 'blocker', 'description' => 'Undefined function found: undefined_function3', 'file' => 'UndefinedFunctionTest.inc', 'lines' => 28 ),
		);
		$actual_errors = $this->checkFile( 'UndefinedFunctionTest.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}
}