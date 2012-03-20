<?php
class UndefinedFunctionCheck extends BaseCheck {
	var $defined_functions = array();

	function check( $files ) {
		$result = true;

		// if WP not loaded, throw error
		if( ! function_exists( 'wp' ) ) {
			$this->add_error( 'wp-load', sprintf( '%s requires WordPress to be loaded.', get_class( $this ) ), 'fail' );
			return false;
		}

		$this->get_defined_functions( $files );

		// Loop through all files and check that all function calls are valid
		foreach( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {

			$tokens = token_get_all( $file_content );

			for ( $i = 0; $i < count( $tokens ); $i++ ) {
				$token = $tokens[$i];

				if( is_array( $token ) ) {
					list( $token_id, $token_text, $line ) = $token;

					// Figure out if it's a function call
					if( $this->is_function_call( $token, $tokens, $i ) ) {

						$this->increment_check_count();

						if( ! $this->is_function_callable( $token_text ) ) {

							$result = false;

							$this->add_error(
								'undefined-function',
								sprintf( 'Undefined function found in file: %s', $token_text ),
								'blocker',
								$file_path,
								array( "$line" => $this->get_line( $line, $file_content ) )
							);
						}

						// TODO: Handle class methods and other function calls
							// if class function -> in_array( $tokens[$i - 1], array( T_OBJECT_OPERATOR, T_DOUBLE_COLON ) )

							// is it add_filter or add_action
								//if( in_array( $token_name, array( 'add_filter', 'add_action' ) ) ) {
							// is it call_user_func or call_user_func_array
								//if( in_array( $token_name, array( 'call_user_func', 'call_user_func_array' ) ) ) {
							// is it call_user_method or call_user_method_array
								//if( in_array( $token_name, array( 'call_user_method', 'call_user_method_array' ) ) ) {
					}
				}
			}
		}

		return $result;
	}

	// Loop through and pull all functions out into array.
	// Ideally we should just check functions.php and any included files.
	// But for the sake of simplicity, we're assuming that the developer has been good and followed convention.
	// TODO: Handle class methods and scope issues
	function get_defined_functions( $files ) {
		$merged_php = $this->merge_files( $files, 'php' );
		$tokens = token_get_all( $merged_php );

		for ( $i = 0; $i < count( $tokens ); $i++ ) {
			$token = $tokens[$i];

			if( is_array( $token ) ) {
				list( $token_id, $token_text, $line ) = $token;

				if( $this->is_function_definition( $token, $tokens, $i ) ) {
					$this->defined_functions[] = $token_text;
				}
			}
		}
	}

	// Check if directly or defined in the current context
	function is_function_callable( $function ) {
		return is_callable( $function ) || in_array( $function, $this->defined_functions );
	}

	// Figure out if it's a function call - T_STRING with T_FUNCTION 2 behind
	function is_function_definition( $token, $tokens, $index ) {
		list( $token_id, $token_text, $line ) = $token;

		return $token_id == T_STRING
			&& (
					( ! is_array( $tokens[$index + 1] ) && $tokens[$index + 1] == '(' )
					||
					( $tokens[$index + 1][0] == T_WHITESPACE && $tokens[$index + 2] == '(' )
				)
				&& $index > 2 && $tokens[$index - 2][0] == T_FUNCTION;
	}

	// Check if token is a function being invoked
	function is_function_call( $token, $tokens, $index ) {
		list( $token_id, $token_text, $line ) = $token;

		return $token_id == T_STRING &&
			( $index < 2 ||
				// Exclude function definition and class instantiation. Exclude class methods. TODO: Remove latter when incorporating classes.
				( ! in_array( $tokens[$index - 2][0], array( T_FUNCTION, T_NEW ) ) && ! in_array( $tokens[$index - 1][0], array( T_OBJECT_OPERATOR, T_DOUBLE_COLON ) ) ) )
				&& (
					( ! is_array( $tokens[$index + 1] ) && $tokens[$index + 1] == '(' )
					||
					( $tokens[$index + 1][0] == T_WHITESPACE && $tokens[$index + 2] == '(' )
				)
			;
	}
}

// Types of function calls

// function()
// function ()
// function( $arg )
// class->function()
// class->function()
// class->function( $arg )
// class::function()
// class::function( $arg )

// add_action( '', function )
// add_filter( '', function )

// call_user_func( function, $args )
// call_user_func_array( function, $args )
// call_user_method( function, class, $args )
// call_user_method_array( function, class, $args )