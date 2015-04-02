<?php
/**
 * Checks for the usage of forbidden PHP functions.
 */

class ForbiddenPHPFunctionsCheck extends CodeCheck {

	protected static $forbidden_php_functions = array(
			'popen',
			'proc_open',
			'exec',
			'shell_exec',
			'system',
			'passthru',
			'base64_decode',
			'base64_encode',
			'uudecode',
			'str_rot13',
			'ini_set',
			'create_function',
			'extract',
	);

	function __construct() {
		parent::__construct( array(
			'PhpParser\Node\Expr\Eval_' => function( $node ) {
				$this->add_error(
					'forbidden-php',
					sprintf( 'The PHP function %s was found. Themes cannot use this function.', '<code>eval()</code>' ),
					'blocker'
				);
			},
			'PhpParser\Node\Expr\FuncCall' => function( $node ) {
				$name = $node->name->toString();
				if ( in_array( $name, self::$forbidden_php_functions ) ) {
					$this->add_error(
						'forbidden-php',
						sprintf( 'The PHP function %s was found. Themes cannot use this function.', '<code>' . $name . '()</code>' ),
						'blocker'
					);
				}
			},
		) );
	}
}
