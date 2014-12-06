<?php

die(); //Don't actually run the following code.

function defined_function( $arg = 'default' ) {
	return $arg;
}

class Defined_Class {
	function defined_method( $arg = 'default' ) {
		return true;
	}

	static function defined_static_method( $arg = 'default' ) {
		return true;
	}
}

$object = new Defined_Class;

// Stand-alone functions
defined_function();
defined_function ();
defined_function( 'dummy' );

undefined_function1();
undefined_function2 ();
undefined_function3( 'fail' );

// *Snip*
// Everything below this line is *not yet being tested*!

// Class methods
$object->defined_method();
$object->defined_method ();
$object->defined_method( 'dummy' );

$object->undefined_method1();
$object->undefined_method2 ();
$object->undefined_method3( 'fail' );

// Static class methods
Defined_Class::defined_static_method();
Defined_Class::defined_static_method ();
Defined_Class::defined_static_method( 'dummy' );

Defined_Class::undefined_static_method1();
Defined_Class::undefined_static_method2 ();
Defined_Class::undefined_static_method3( 'dummy' );

// Functions invoked as actions
add_action( 'some_hook', 'defined_function' );

add_action( 'some_hook', 'undefined_function' );

// Functions invoked as filters
add_filter( 'some_filter_hook', 'defined_function' );

add_filter( 'some_filter_hook', 'undefined_function' );

// call_user_func
call_user_func( 'defined_function', 'dummy' );
call_user_func( array( $object, 'defined_method' ), 'dummy' );
call_user_func( array( 'Defined_Class', 'defined_static_method' ), 'dummy' );

call_user_func( 'undefined_function', 'dummy' );
call_user_func( array( $object, 'undefined_method' ), 'dummy' );
call_user_func( array( 'Defined_Class', 'undefined_static_method' ), 'dummy' );

// call_user_func_array
call_user_func( 'defined_function', array( 'foo', 'bar' ) );
call_user_func( array( $object, 'defined_method' ), array( 'foo', 'bar' ) );
call_user_func( array( 'Defined_Class', 'defined_static_method' ), array( 'foo', 'bar' ) );

call_user_func( 'undefined_function', array( 'foo', 'bar' ) );
call_user_func( array( $object, 'undefined_method' ), array( 'foo', 'bar' ) );
call_user_func( array( 'Defined_Class', 'undefined_static_method' ), array( 'foo', 'bar' ) );

// call_user_method (deprecated since PHP 4.1.0)
call_user_method( 'defined_method', $object, 'dummy' );
call_user_method( 'undefined_method', $object, 'dummy' );

// call_user_method_array (deprecated since PHP 4.1.0)
call_user_method_array( 'defined_method', $object, array( 'foo', 'bar' ) );
call_user_method_array( 'undefined_method', $object, array( 'foo', 'bar' ) );
