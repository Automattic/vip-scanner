<?php
/**
 * Checks for usage of functions that themes should not use:
 *
 * register_post_type()
 * register_taxonomy()
 * add_shortcode()
 * add_meta_box()
 * add_help_tab() (WP_Screen method)
 * query_posts()
 */

class ForbiddenFunctionsCheck extends CodeCheck {

	protected static $forbidden_functions = array(
		'register_post_type',
		'register_taxonomy',
		'add_shortcode',
		'add_meta_box',
		'add_help_tab',
		'query_posts',
		'get_children',
	);

	function __construct() {
		parent::__construct( array(
			'PhpParser\Node\Expr\FuncCall' => function( $node ) {
				$name = $node->name->toString();
				if ( in_array( $name, self::$forbidden_functions ) ) {
					$this->add_error(
						'forbidden-function',
						sprintf( 'The function %s was found in the theme. Themes cannot use this function, please remove it.', '<code>' . $name . '</code>' ),
						BaseScanner::LEVEL_BLOCKER
					);
				}
			}
		) );
	}
}
