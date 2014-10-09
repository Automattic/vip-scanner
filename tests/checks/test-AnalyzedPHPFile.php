<?php

class AnalyzedPHPFileTest extends WP_UnitTestCase {
	protected $_PHPShortTagsCheck;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/class-analyzed-php-file.php';
	}

	private function assert_object_property( $object, $key, $value ) {
		$this->assertArrayHasKey( $key, $object );
		$this->assertEquals( $value, $object[$key] );
	}

	/**
	 * This tests that AnalyzedFile is properly reading the basic hierarchy.
	 *
	 * It should see FirstClass and test_function within it, and namespace test_function
	 * as FirstClass::test_function.
	 *
	 * It should not see SecondClass or SecondClass::second_test_function.
	 */
	public function test_basic_hierarchy() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<EOT
<?php
class FirstClass extends ParentClass {
	function test_function() {}
}
?>
class SecondClass extends SecondParentClass {
	function second_test_function() {}
}
EOT
		);

		$namespaces = $analyzed_file->get_code_elements( 'namespaces' );
		$classes    = $analyzed_file->get_code_elements( 'classes' );
		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert no namespaces
		$this->assertEqualSets( array(), $namespaces );

		// Assert expected classes
		$this->assertEqualSets( array( '' ), array_keys( $classes ) );
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $classes[''] ) );

		// Assert the presence of the parent class
		$this->assert_object_property( $classes['']['FirstClass'], 'parentclass', 'ParentClass' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function' ), array_keys( $functions['FirstClass'] ) );
	}

	/**
	 * Tests that abstract classes and functions are properly identified.
	 */
	public function test_abstracts() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<EOT
<?php
abstract class FirstClass extends ParentClass {
	abstract function abstract_function();
	function non_abstract() {}
}
?>
EOT
		);

		$classes   = $analyzed_file->get_code_elements( 'classes' );
		$functions = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected classes
		$this->assertEqualSets( array( '' ), array_keys( $classes ) );
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $classes[''] ) );

		// Assert abstract bits
		$this->assert_object_property( $classes['']['FirstClass'], 'abstract', 'abstract' );

		// Assert the presence of the parent class
		$this->assert_object_property( $classes['']['FirstClass'], 'parentclass', 'ParentClass' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array(
			'FirstClass::abstract_function',
			'FirstClass::non_abstract',
		), array_keys( $functions['FirstClass'] ) );

		// Assert function is abstract
		$this->assert_object_property( $functions['FirstClass']['FirstClass::abstract_function'], 'abstract', 'abstract' );

		// Assert function is not abstract
		$this->assertEmpty( $functions['FirstClass']['FirstClass::non_abstract']['abstract'] );
	}

	/**
	 * Tests that static functions and members are properly identified.
	 */
	public function test_statics() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<EOT
<?php
class FirstClass {
	function test_function() {}
	static function static_test_function() {}
}
EOT
		);

		$classes   = $analyzed_file->get_code_elements( 'classes' );
		$functions = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected classes
		$this->assertEqualSets( array( '' ), array_keys( $classes ) );
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $classes[''] ) );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assert_object_property( $functions['FirstClass']['FirstClass::static_test_function'], 'static', 'static' );
		$this->assertTrue( empty( $functions['FirstClass']['FirstClass::test_function']['static'] ) );
	}

	/**
	 * Tests that the analyzer properly identifies the visibility of class members.
	 */
	public function test_visibilities() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<EOT
<?php
abstract class FirstClass extends ParentClass {
	private function private_function() {}
	private abstract function private_abstract_function();

	protected function protected_function() {}
	protected abstract function protected_abstract_function();

	public function public_function() {}
	public abstract function public_abstract_function();

	function second_public_function() {}
	abstract function second_public_abstract_function();
}
?>
EOT
		);

		$classes   = $analyzed_file->get_code_elements( 'classes' );
		$functions = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected classes
		$this->assertEqualSets( array( '' ), array_keys( $classes ) );
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $classes[''] ) );

		// Assert abstract bits
		$this->assert_object_property( $classes['']['FirstClass'], 'abstract', 'abstract' );

		// Assert the presence of the parent class
		$this->assert_object_property( $classes['']['FirstClass'], 'parentclass', 'ParentClass' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets(
			array(
				'FirstClass::private_function',
				'FirstClass::private_abstract_function',

				'FirstClass::protected_function',
				'FirstClass::protected_abstract_function',

				'FirstClass::public_function',
				'FirstClass::public_abstract_function',

				'FirstClass::second_public_function',
				'FirstClass::second_public_abstract_function',
			),
			array_keys( $functions['FirstClass'] )
		);

		// Assert function is abstract
		$this->assert_object_property( $functions['FirstClass']['FirstClass::private_abstract_function'], 'abstract', 'abstract' );
		$this->assert_object_property( $functions['FirstClass']['FirstClass::protected_abstract_function'], 'abstract', 'abstract' );
		$this->assert_object_property( $functions['FirstClass']['FirstClass::public_abstract_function'], 'abstract', 'abstract' );
		$this->assert_object_property( $functions['FirstClass']['FirstClass::second_public_abstract_function'], 'abstract', 'abstract' );

		// Assert function is not abstract
		$this->assertEmpty( $functions['FirstClass']['FirstClass::private_function']['abstract'] );
		$this->assertEmpty( $functions['FirstClass']['FirstClass::protected_function']['abstract'] );
		$this->assertEmpty( $functions['FirstClass']['FirstClass::public_function']['abstract'] );
		$this->assertEmpty( $functions['FirstClass']['FirstClass::second_public_function']['abstract'] );

		// Assert function visibility
		$this->assert_object_property( $functions['FirstClass']['FirstClass::private_function'], 'visibility', 'private' );
		$this->assert_object_property( $functions['FirstClass']['FirstClass::private_abstract_function'], 'visibility', 'private' );
		$this->assert_object_property( $functions['FirstClass']['FirstClass::protected_function'], 'visibility', 'protected' );
		$this->assert_object_property( $functions['FirstClass']['FirstClass::protected_abstract_function'], 'visibility', 'protected' );
		$this->assert_object_property( $functions['FirstClass']['FirstClass::public_function'], 'visibility', 'public' );
		$this->assert_object_property( $functions['FirstClass']['FirstClass::public_abstract_function'], 'visibility', 'public' );

		$this->assertTrue( empty( $functions['FirstClass']['FirstClass::second_public_function']['visibility'] ) || 'public' === $functions['FirstClass']['FirstClass::second_public_function']['visibility'] );
		$this->assertTrue( empty( $functions['FirstClass']['FirstClass::second_public_abstract_function']['visibility'] ) || 'public' === $functions['FirstClass']['FirstClass::second_public_abstract_function']['visibility'] );
	}

	/**
	 * Tests that functions and variables outside of a class are identified.
	 */
	public function test_global_elements() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {
	function test_function() {}
}

function second_test_function() {}

$somevar = "something";
EOT
		);

		$namespaces = $analyzed_file->get_code_elements( 'namespaces' );
		$classes    = $analyzed_file->get_code_elements( 'classes' );
		$functions  = $analyzed_file->get_code_elements( 'functions' );
		$variables  = $analyzed_file->get_code_elements( 'variables' );

		// Assert no namespaces
		$this->assertEqualSets( array(), $namespaces );

		// Assert expected classes
		$this->assertEqualSets( array( '' ), array_keys( $classes ) );
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $classes[''] ) );

		// Assert the presence of the parent class
		$this->assert_object_property( $classes['']['FirstClass'], 'parentclass', 'ParentClass' );

		// Assert expected functions
		$this->assertEqualSets( array( '', 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function' ), array_keys( $functions['FirstClass'] ) );
		$this->assertEqualSets( array( 'second_test_function' ), array_keys( $functions[''] ) );

		// Assert expected variables
		$this->assertEqualSets( array( '' ), array_keys( $variables ) );
		$this->assertEqualSets( array( 'somevar' ), array_keys( $variables[''] ) );
	}

	/**
	 * Tests that function arguments are properly identified
	 */
	public function test_function_argument_detection() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {
	function test_function($first_arg) {}
	function second_test_function($second_arg = 'something') {}
	function third_test_function() {}
}

function outer_test_function($third_arg) {}
function second_outer_test_function($fourth_arg = 'something') {}
function third_outer_test_function() {}

EOT
		);

		$namespaces = $analyzed_file->get_code_elements( 'namespaces' );
		$classes    = $analyzed_file->get_code_elements( 'classes' );
		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert no namespaces
		$this->assertEqualSets( array(), $namespaces );

		// Assert expected classes
		$this->assertEqualSets( array( '' ), array_keys( $classes ) );
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $classes[''] ) );

		// Assert the presence of the parent class
		$this->assert_object_property( $classes['']['FirstClass'], 'parentclass', 'ParentClass' );

		// Assert expected functions
		$this->assertEqualSets( array( '', 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets(
			array(
				'outer_test_function',
				'second_outer_test_function',
				'third_outer_test_function',
			),
			array_keys( $functions[''] )
		);
		$this->assertEqualSets(
			array(
				'FirstClass::test_function',
				'FirstClass::second_test_function',
				'FirstClass::third_test_function',
			),
			array_keys( $functions['FirstClass'] )
		);

		// Assert expected function arguments
		$this->assert_object_property( $functions['']['outer_test_function'], 'args', '$third_arg' );
		$this->assert_object_property( $functions['']['second_outer_test_function'], 'args', '$fourth_arg=\'something\'' );
		$this->assertEmpty( $functions['']['third_outer_test_function']['args'] );

		$this->assert_object_property( $functions['FirstClass']['FirstClass::test_function'], 'args', '$first_arg' );
		$this->assert_object_property( $functions['FirstClass']['FirstClass::second_test_function'], 'args', '$second_arg=\'something\'' );
		$this->assertEmpty( $functions['FirstClass']['FirstClass::third_test_function']['args'] );
	}

	/**
	 * Tests that we accurately detect namespaces, and that we fall over to namespaces
	 * properly.
	 */
	public function test_namespaces() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
namespace SomeNamespace;
const SOMECONST = 1;
function test_function() {}

namespace SomeNamespace\SubNamespace;

function second_test_function() {
	$var = namespace\SOMECONST;
	SomeNamespace\SubNamespace\other_test_func();
	namespace\other_func();
	throw new namespace\error( 'Error Message' );
}
EOT
		);

		$namespaces = $analyzed_file->get_code_elements( 'namespaces' );
		$classes = $analyzed_file->get_code_elements( 'classes' );
		$functions = $analyzed_file->get_code_elements( 'functions' );
		$constants = $analyzed_file->get_code_elements( 'constants' );
		$variables = $analyzed_file->get_code_elements( 'variables' );
		$function_calls = $analyzed_file->get_code_elements( 'function_calls' );

		// Assert expected
		$this->assertEqualSets( array(
			'SomeNamespace',
			'SomeNamespace\SubNamespace',
		), array_keys( $namespaces[''] ) );

		// Assert no classes
		$this->assertEmpty( $classes );

		// Assert expected functions
		$this->assertEqualSets( array( 'SomeNamespace', 'SomeNamespace\SubNamespace' ), array_keys( $functions ) );

		$this->assertEqualSets( array( 'SomeNamespace::test_function' ), array_keys( $functions['SomeNamespace'] ) );
		$this->assertEqualSets( array( 'SomeNamespace\SubNamespace::second_test_function' ), array_keys( $functions['SomeNamespace\SubNamespace'] ) );

		// Assert expected variables
		$this->assertEqualSets( array( 'SomeNamespace\SubNamespace::second_test_function' ), array_keys( $variables ) );
		$this->assertEqualSets( array( 'SomeNamespace\SubNamespace::second_test_function::var' ), array_keys( $variables['SomeNamespace\SubNamespace::second_test_function'] ) );

		// Assert expected constants
		$this->assertEqualSets( array( 'SomeNamespace' ), array_keys( $constants ) );
		$this->assertEqualSets( array( 'SomeNamespace::SOMECONST' ), array_keys( $constants['SomeNamespace'] ) );

		// Assert expected function calls
		$this->assertEqualSets( array( 'SomeNamespace\SubNamespace::second_test_function' ), array_keys( $function_calls ) );
		$this->assertEqualSets( array(
			'namespace\other_func',
			'namespace\error',
		), array_keys( $function_calls['SomeNamespace\SubNamespace::second_test_function'] ) );
	}

	/**
	 * Tests that we properly detect function calls within functions and outside.
	 */
	public function test_function_call_detection() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {
	function test_function() {
		apply_filters( 'some_arg', 'other_arg' );
	}
}

function second_test_function() {
	// Test the same function call within a single block of code
	do_action( 'some_other_arg', 'other_other_arg' );
	do_action      (       'some_other_other_arg'    ,     'other_other_other_arg'     )     ;
}

wp_die();
EOT
		);

		$classes        = $analyzed_file->get_code_elements( 'classes' );
		$functions      = $analyzed_file->get_code_elements( 'functions' );
		$function_calls = $analyzed_file->get_code_elements( 'function_calls' );

		// Assert expected classes
		$this->assertEqualSets( array( '' ), array_keys( $classes ) );
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $classes[''] ) );

		// Assert the presence of the parent class
		$this->assert_object_property( $classes['']['FirstClass'], 'parentclass', 'ParentClass' );

		// Assert expected functions
		$this->assertEqualSets( array( '', 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function' ), array_keys( $functions['FirstClass'] ) );
		$this->assertEqualSets( array( 'second_test_function' ), array_keys( $functions[''] ) );

		// Assert expected function calls
		$this->assertEqualSets( array( '', 'FirstClass::test_function', 'second_test_function' ), array_keys( $function_calls ) );

		$this->assertEmpty( $function_calls['']['wp_die']['args'] );
		$this->assertEqualSets( array( '\'some_arg\'', '\'other_arg\'' ), $function_calls['FirstClass::test_function']['apply_filters']['args'] );
		$this->assertEqualSets( array( '\'some_other_arg\'', '\'other_other_arg\'' ), $function_calls['second_test_function']['do_action'][0]['args'] );
		$this->assertEqualSets( array( '\'some_other_other_arg\'', '\'other_other_other_arg\''), $function_calls['second_test_function']['do_action'][1]['args'] );
	}

	/**
	 * Test that a number of special function are recognized. These functions are
	 * considered part of the PHP language, and have specific tokens.
	 */
	public function test_special_function_call_detection() {
		$special_functions = array(
			'eval',
			'empty',
			'die',
			'exit',
			'include',
			'include_once',
			'require',
			'require_once',
			'isset',
			'list',
			'print',
			'unset',

			// Halt compiler needs to be last -- it doesn't play nice with the other kids.
			'__halt_compiler',
		);

		$content = '<?php';
		foreach ( $special_functions as $special_function ) {
			$content .= "\n$special_function();";
		}

		$analyzed_file = new AnalyzedPHPFile( 'test.php', $content );

		$function_calls = $analyzed_file->get_code_elements( 'function_calls' );

		// Assert expected function calls
		$this->assertEqualSets( array( '' ), array_keys( $function_calls ) );

		foreach ( $special_functions as $special_function ) {
			$this->assertTrue( is_array( $function_calls[''][$special_function] ) );
		}
	}

	/**
	 * Tests that functions used in variable assignments are properly detected.
	 */
	public function test_complex_variable_assignment() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
$var = func();
$var2 = second_func() . "sometext";
$var3 = third_func() + fourth_func();
$var4 = fifth_func() + $some_obj->some_func();
open_function_call();
EOT
		);

		$functions      = $analyzed_file->get_code_elements( 'functions' );
		$function_calls = $analyzed_file->get_code_elements( 'function_calls' );
		$variables      = $analyzed_file->get_code_elements( 'variables' );

		// Assert expected
		$this->assertEmpty( $functions );
		$this->assertEqualSets( array( '' ), array_keys( $function_calls ) );
		$this->assertEqualSets( array( '' ), array_keys( $variables ) );

		// Assert the expected variables
		$this->assertEqualSets(
			array(
				'var',
				'var2',
				'var3',
				'var4',
			),
			array_keys( $variables[''] )
		);

		// Assert the expected functions
		$this->assertEqualSets(
			array(
				'func',
				'second_func',
				'third_func',
				'fourth_func',
				'fifth_func',
				'some_obj::some_func',
				'open_function_call',
			),
			array_keys( $function_calls[''] )
		);

		// Assert that the function calls are assoc
		$this->assert_object_property( $function_calls['']['func'], 'in_var', 'var' );
		$this->assert_object_property( $function_calls['']['second_func'], 'in_var', 'var2' );
		$this->assert_object_property( $function_calls['']['third_func'], 'in_var', 'var3' );
		$this->assert_object_property( $function_calls['']['fourth_func'], 'in_var', 'var3' );
		$this->assert_object_property( $function_calls['']['fifth_func'], 'in_var', 'var4' );
		$this->assert_object_property( $function_calls['']['some_obj::some_func'], 'in_var', 'var4' );
		$this->assertTrue( empty( $function_calls['']['open_function_call']['in_var'] ) );
	}

	/**
	 * Tests that calls to static functions are properly identified. Also tests
	 * accesses to static member variables.
	 */
	public function test_static_accessors() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
function test_function() {
	self::do_func();
	$var = self::some_property;
	$var2 = SomeObj::do_other_func();
}
EOT
		);

		$functions      = $analyzed_file->get_code_elements( 'functions' );
		$function_calls = $analyzed_file->get_code_elements( 'function_calls' );

		// Assert expected functions
		$this->assertEqualSets( array( '' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'test_function' ), array_keys( $functions[''] ) );

		// Assert expectec function calls
		$this->assertEqualSets( array( 'test_function' ), array_keys( $function_calls ) );
		$this->assertEqualSets( array( 'self::do_func', 'SomeObj::do_other_func' ), array_keys( $function_calls['test_function'] ) );
		$this->assertEmpty( $function_calls['test_function']['self::do_func']['args'] );
		$this->assert_object_property( $function_calls['test_function']['SomeObj::do_other_func'], 'in_var', 'var2' );
	}

	/**
	 * Tests that the contents of braceless closures are properly parsed.
	 *
	 * Also tests that the paths to elements are proper
	 */
	public function test_braceless_closures() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
if ( $somevars ):
	function somefunc() {
		while ( true ):
			do_action( 'some_action' );
		endwhile;
	}

	foreach ( $somevars as $somevar ):
		apply_filters( 'some_filter', $somevar );
	endforeach;
endif;
EOT
		);

		$variables		= $analyzed_file->get_code_elements( 'variables' );
		$functions		= $analyzed_file->get_code_elements( 'functions' );
		$function_calls = $analyzed_file->get_code_elements( 'function_calls' );

		// Assert expected variables
		$this->assertEqualSets( array( '' ), array_keys( $variables ) );
		$this->assertEqualSets( array( 'somevars', 'somevar' ), array_keys( $variables[''] ) );

		// Assert expected function calls
		$this->assertEqualSets( array( '', 'somefunc' ), array_keys( $function_calls ) );
		$this->assertEqualSets( array( 'apply_filters' ), array_keys( $function_calls[''] ) );
		$this->assertEqualSets( array( 'do_action' ), array_keys( $function_calls['somefunc'] ) );

		// Assert expected functions
		$this->assertEqualSets( array( '' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'somefunc' ), array_keys( $functions[''] ) );
	}

	/**
	 * Tests that code blocks are properly followed.
	 *
	 * The second and third function definitions are important, as they test
	 * that not only are the functions seen by the parser, but that they're properly
	 * scoped. It is a common bug to have secondfunc() show up as somefunc::secondfunc().
	 */
	public function test_code_blocks() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
if ( $somevars ) {
	function somefunc( $arg ) {
		{
			while ( true ) {
				$variable = ( isset( $_GET[ $arg ] ) ? true : false );
			}
		}
	}

	function secondfunc() {
		do_action( 'some_action' );
	}
}

function thirdfunc() {
	var_dump( 'last_action' );
}
EOT
		);

		$variables		= $analyzed_file->get_code_elements( 'variables' );
		$functions		= $analyzed_file->get_code_elements( 'functions' );
		$function_calls = $analyzed_file->get_code_elements( 'function_calls' );

		// Assert expected functions
		$this->assertEqualSets( array( '' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'somefunc', 'secondfunc', 'thirdfunc' ), array_keys( $functions[''] ) );

		// Assert expected variables
		$this->assertEqualSets( array( '', 'somefunc' ), array_keys( $variables ) );
		$this->assertEqualSets( array( 'somevars' ), array_keys( $variables[''] ) );
		$this->assertEqualSets( array( 'somefunc::variable', 'somefunc::_GET', 'somefunc::arg' ), array_keys( $variables['somefunc'] ) );

		// Assert expected function calls
		$this->assertEqualSets( array( 'secondfunc', 'somefunc', 'thirdfunc' ), array_keys( $function_calls ) );
		$this->assertEqualSets( array( 'isset' ), array_keys( $function_calls['somefunc'] ) );
		$this->assertEqualSets( array( 'do_action' ), array_keys( $function_calls['secondfunc'] ) );
		$this->assertEqualSets( array( 'var_dump' ), array_keys( $function_calls['thirdfunc'] ) );
	}

	/**
	 * Tests the parsing of a realistic code sample. This sample should eventually
	 * be expanded to test more combinations of properties.
	 */
	public function test_actual_code_sample(){
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
abstract class BaseFileClass {
	abstract function list_files( $dir );
}

class FileClass extends BaseFileClass {
	const NUM_FILES = 1;
	private $files = 'a';
	protected $protected_files;

	function list_files( $dir ) {
		if(is_dir($dir)) {
			if($handle = opendir($dir)) {
				while(($file = readdir($handle)) !== false) {
					if($file != "." &amp;&amp; $file != ".." &amp;&amp; $file != "Thumbs.db"/*pesky windows, images..*/) {
						echo '<a target="_blank" href="'.$dir.$file.'">'.$file.'</a><br>'."\n";
					}
				}

				closedir($handle);
			}
		}
	}
}
EOT
		);

		$classes        = $analyzed_file->get_code_elements( 'classes' );
		$functions      = $analyzed_file->get_code_elements( 'functions' );
		$function_calls = $analyzed_file->get_code_elements( 'function_calls' );
		$variables      = $analyzed_file->get_code_elements( 'variables' );
		$constants      = $analyzed_file->get_code_elements( 'constants' );

		// Assert expected classes
		$this->assertEqualSets( array( '' ), array_keys( $classes ) );
		$this->assertEqualSets( array( 'BaseFileClass', 'FileClass' ), array_keys( $classes[''] ) );

		// Assert class properties
		$this->assert_object_property( $classes['']['BaseFileClass'], 'abstract', 'abstract' );
		$this->assertTrue( empty( $classes['']['BaseFileClass']['parentclass'] ) );

		$this->assert_object_property( $classes['']['FileClass'], 'parentclass', 'BaseFileClass' );
		$this->assertTrue( empty( $classes['']['FileClass']['abstract'] ) );

		// Assert expected functions
		$this->assertEqualSets( array( 'BaseFileClass', 'FileClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'BaseFileClass::list_files' ), array_keys( $functions['BaseFileClass'] ) );
		$this->assertEqualSets( array( 'FileClass::list_files' ), array_keys( $functions['FileClass'] ) );

		// Assert Function properties
		$this->assert_object_property( $functions['BaseFileClass']['BaseFileClass::list_files'], 'abstract', 'abstract' );
		$this->assert_object_property( $functions['BaseFileClass']['BaseFileClass::list_files'], 'args', '$dir' );
		$this->assertTrue( empty( $functions['BaseFileClass']['BaseFileClass::list_files']['visibility'] ) || 'public' === $functions['BaseFileClass']['BaseFileClass::list_files']['visibility'] );

		$this->assertTrue( empty( $functions['FileClass']['FileClass::list_files']['abstract'] ) );
		$this->assert_object_property( $functions['FileClass']['FileClass::list_files'], 'args', '$dir' );
		$this->assertTrue( empty( $functions['FileClass']['FileClass::list_files']['visibility'] ) || 'public' === $functions['FileClass']['FileClass::list_files']['visibility'] );

		// Assert the expected function calls
		$this->assertEqualSets( array( 'FileClass::list_files' ), array_keys( $function_calls ) );
		$this->assertEqualSets( array( 'is_dir', 'opendir', 'readdir', 'closedir' ), array_keys( $function_calls['FileClass::list_files'] ) );

		// Assert function call properties
		$this->assert_object_property( $function_calls['FileClass::list_files']['opendir'], 'in_var', 'handle' );
		$this->assert_object_property( $function_calls['FileClass::list_files']['readdir'], 'in_var', 'file' );

		// Assert the expected variables
		$this->assertEqualSets( array( 'FileClass', 'FileClass::list_files' ), array_keys( $variables ) );
		$this->assertEqualSets( array( 'FileClass::files', 'FileClass::protected_files' ), array_keys( $variables['FileClass'] ) );
		$this->assertEqualSets( array( 'FileClass::list_files::dir', 'FileClass::list_files::handle', 'FileClass::list_files::file' ), array_keys( $variables['FileClass::list_files'] ) );

		// Assert the expected constants
		$this->assertEqualSets( array( 'FileClass' ), array_keys( $constants ) );
		$this->assertEqualSets( array( 'FileClass::NUM_FILES' ), array_keys( $constants['FileClass'] ) );
	}

	/**
	 * Tests that line numberings are correctly maintained.
	 */
	public function test_line_numbers() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<h1>title</h1>
<?php
<<<SOMETEXT

SOMETEXT
		;
/**
 * Tests something.
 */
function some_test() {
		$somevar = '';
		some_call();
}

class test_class {
	private
	function some_func() {}
}
?>


<?php
$last_var = null;

EOT
		);

		$classes        = $analyzed_file->get_code_elements( 'classes' );
		$functions      = $analyzed_file->get_code_elements( 'functions' );
		$function_calls = $analyzed_file->get_code_elements( 'function_calls' );
		$variables      = $analyzed_file->get_code_elements( 'variables' );

		// Assert things in order of line numbers
		$this->assert_object_property( $functions['']['some_test'], 'line', 10 );
		$this->assert_object_property( $variables['some_test']['some_test::somevar'], 'line', 11 );
		$this->assert_object_property( $function_calls['some_test']['some_call'], 'line', 12 );
		$this->assert_object_property( $classes['']['test_class'], 'line', 15 );
		$this->assert_object_property( $functions['test_class']['test_class::some_func'], 'line', 17 );
		$this->assert_object_property( $variables['']['last_var'], 'line', 23 );
	}

	/**
	 * Tests that function doc strings are correctly parsed in.
	 */
	public function test_doc_strings() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
/**
 * Tests something.
 */
function some_test() {
		$somevar = '';
		some_call();
}

class test_class {

	/**
	 *Does something.
	 */
	private function some_func() {}
}
?>


<?php
$last_var = null;

EOT
		);

		$functions = $analyzed_file->get_code_elements( 'functions' );

		$this->assert_object_property( $functions['']['some_test'], 'documentation', 'Tests something.' );
		$this->assert_object_property( $functions['test_class']['test_class::some_func'], 'documentation', 'Does something.' );
	}

	/**
	 * Tests that function doc strings are correctly parsed in.
	 */
	public function test_anonymous_functions() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php

function some_func() {
	apply_filters( 'some_filter', function( $arg ) { return $arg; } );
}

$f = function() {
	apply_filters( 'some_filter', function( $arg1 ) { return $arg1; } );
};

$f();
EOT
		);

		$functions = $analyzed_file->get_code_elements( 'functions' );

		$this->assert_object_property( $functions['some_func']['some_func::{closure}'], 'args', '$arg' );
		$this->assert_object_property( $functions['some_func']['some_func::{closure}'], 'line', 4 );

		$this->assert_object_property( $functions['']['{closure}'], 'args', '' );
		$this->assert_object_property( $functions['']['{closure}'], 'line', 7 );
		$this->assert_object_property( $functions['']['{closure}'], 'in_var', 'f' );

		$this->assert_object_property( $functions['{closure}']['{closure}::{closure}'], 'args', '$arg1' );
		$this->assert_object_property( $functions['{closure}']['{closure}::{closure}'], 'line', 8 );
	}

	public function test_foreach_loop() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
function test_function() {
	foreach( $objects as $obj ) {
	$a = "{$ahojky}";
	}
	}
EOT
		);

		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected functions
		$this->assertEqualSets( array( 'test_function' ), array_keys( $functions[''] ) );

	}

	public function test_foreach_loop_inside_class_without_objects() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {

	function test_function() {
		foreach( $ahoj as $obj ) {
			$a = "{$ahojky}";
		}
	}

	function second_test_function() {}
}
EOT
		);
		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function', 'FirstClass::second_test_function' ), array_keys( $functions['FirstClass'] ) );
	}

	public function test_foreach_loop_inside_class_with_object_in_loop_definition() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {

	function test_function() {
		foreach( $object->ahoj as $obj ) {
			$a = "{$ahojky}";
		}
	}

	function second_test_function() {}
}
EOT
		);
		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function', 'FirstClass::second_test_function' ), array_keys( $functions['FirstClass'] ) );
	}

	public function test_foreach_loop_inside_class_with_object_in_string_variable() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {

	function test_function() {
		foreach( $ahoj as $obj ) {
			$a = "{$obj->ahojky}";
		}
	}

	function second_test_function() {}
}
EOT
		);
		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function', 'FirstClass::second_test_function' ), array_keys( $functions['FirstClass'] ) );
	}

	public function test_foreach_loop_inside_class_without_object1() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {

	function test_function() {
		foreach( $objects->ahoj as $obj ) {
			$a = "{$ahojky}";
		}
	}

	function second_test_function() {}
}
EOT
		);
		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function', 'FirstClass::second_test_function' ), array_keys( $functions['FirstClass'] ) );
	}

	public function test_foreach_loop_inside_class_with_object() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {

	function test_function() {
		foreach( $objects->ahoj as $obj ) {
			$a = "{$obj->ahojky}";
		}
	}

	function second_test_function() {}
}
EOT
		);

		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function', 'FirstClass::second_test_function' ), array_keys( $functions['FirstClass'] ) );
	}

	public function test_objects_in_function_call_args_with_object_in_object() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {

	function test_function() {
		foreach( $objects->value as $obj ) {
			add_meta_box(
				"post_{$direction}_{$obj->value->object}"
			);
		}
	}

	function second_test_function() {}
}
EOT
		);

		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function', 'FirstClass::second_test_function' ), array_keys( $functions['FirstClass'] ) );
	}

	public function test_objects_in_function_call_args() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {

	function test_function() {
		foreach( $objects->value as $obj ) {
			add_meta_box(
				"post_{$direction}_{$obj->value}"
			);
		}
	}

	function second_test_function() {}
}
EOT
		);

		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function', 'FirstClass::second_test_function' ), array_keys( $functions['FirstClass'] ) );
	}

	public function test_two_string_varnames() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {

	function test_function() {
		$a = "{$a}";
		$b = "{$b}";
	}

	function second_test_function() {}
}
EOT
		);

		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function', 'FirstClass::second_test_function' ), array_keys( $functions['FirstClass'] ) );
	}

	public function test_objects_in_function_call_args_dollar_outside() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {

	function test_function() {
		foreach( $objects->value as $obj ) {
			add_meta_box(
				"post_{$direction}_${value}"
			);
		}
	}

	function second_test_function() {}
}
EOT
		);

		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function', 'FirstClass::second_test_function' ), array_keys( $functions['FirstClass'] ) );
	}

	public function test_objects_in_function_call_args_array() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {

	function test_function() {
		foreach( $objects->value as $obj ) {
			add_meta_box(
				"post_{$direction}_{$b[0][1]['element']}"
			);
		}
	}

	function second_test_function() {}
}
EOT
		);

		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function', 'FirstClass::second_test_function' ), array_keys( $functions['FirstClass'] ) );
	}

	public function test_objects_in_function_call_args_array_of_objects() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {

	function test_function() {
		foreach( $objects->value as $obj ) {
			add_meta_box(
				"post_{$direction}_{$obj->values[3]->name}"
			);
		}
	}

	function second_test_function() {}
}
EOT
		);

		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function', 'FirstClass::second_test_function' ), array_keys( $functions['FirstClass'] ) );
	}

	public function test_array_of_objects_in_curly_braces() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {

	function test_function() {
		foreach( $objects->value as $obj ) {
			$b = "${a}";
			$a = "{$obj->values[3]->name}";
		}
	}

	function second_test_function() {}
}
EOT
		);

		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function', 'FirstClass::second_test_function' ), array_keys( $functions['FirstClass'] ) );
	}

	public function test_multidimensional_array_in_curly_braces() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {

	function test_function() {
		foreach( $objects->value as $obj ) {
			$b = "${a}";
			$a = "{$arr[4][3]}";
		}
	}

	function second_test_function() {}
}
EOT
		);

		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function', 'FirstClass::second_test_function' ), array_keys( $functions['FirstClass'] ) );
	}

	public function test_var_inside_var_in_curly_braces() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {

	function test_function() {
		foreach( $objects->value as $obj ) {
			$b = "${a}";
			$a = "{${$name}}"
		}
	}

	function second_test_function() {}
}
EOT
		);

		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function', 'FirstClass::second_test_function' ), array_keys( $functions['FirstClass'] ) );
	}

	public function test_object_method_call_in_curly_braces() {
		$analyzed_file = new AnalyzedPHPFile( 'test.php', <<<'EOT'
<?php
class FirstClass extends ParentClass {

	function test_function() {
		foreach( $objects->value as $obj ) {
			$b = "${a}";
			$a = "{${$object->getName()}}"
		}
	}

	function second_test_function() {}
}
EOT
		);

		$functions  = $analyzed_file->get_code_elements( 'functions' );

		// Assert expected functions
		$this->assertEqualSets( array( 'FirstClass' ), array_keys( $functions ) );
		$this->assertEqualSets( array( 'FirstClass::test_function', 'FirstClass::second_test_function' ), array_keys( $functions['FirstClass'] ) );
	}

}
