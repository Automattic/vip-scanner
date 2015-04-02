<?php
/**
 * Checks for constants that themes can't use:
 * PLUGINDIR
 * WP_PLUGIN_DIR
 * MUPLUGINDIR
 * WPMU_PLUGIN_DIR
 * IS_WPCOM
 */

class ForbiddenConstantsCheck extends CodeCheck {

	protected static $forbidden_constants = array(
		'PLUGINDIR',
		'WP_PLUGIN_DIR',
		'MUPLUGINDIR',
		'WPMU_PLUGIN_DIR',
		'IS_WPCOM',
	);

	function __construct() {
		parent::__construct( array(
			'PhpParser\Node\Expr\ConstFetch' => function( $node ) {
				$name = $node->name->toString();
				if ( in_array( $name, self::$forbidden_constants ) ) {
					$this->add_error(
						'forbidden',
						sprintf( 'Themes cannot use the constant %s.', '<code>' . $name . '</code>' ),
						BaseScanner::LEVEL_BLOCKER
					);
				}
			}
		) );
	}
}
