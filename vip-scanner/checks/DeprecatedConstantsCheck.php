<?php
/**
 * Checks for deprecated constants:
 * STYLESHEETPATH
 * TEMPLATEPATH
 */

class DeprecatedConstantsCheck extends CodeCheck {

	protected static $deprecated_constants = array(
		'STYLESHEETPATH' => 'get_stylesheet_directory()',
		'TEMPLATEPATH'   => 'get_template_directory()',
	);

	function __construct() {
		parent::__construct( array(
			'PhpParser\Node\Expr\ConstFetch' => function( $node ) {
				$name = $node->name->toString();
				if ( array_key_exists( $name, self::$deprecated_constants ) ) {
					$this->add_error(
						'deprecated',
						sprintf(
							'The constant %1$s is deprecated. Use %2$s instead.',
							'<code>' . esc_html( $name ) . '</code>',
							'<code>' . esc_html( self::$deprecated_constants[ $name ] ) . '</code>'
						),
						BaseScanner::LEVEL_BLOCKER
					);
				}
			}
		) );
	}
}
