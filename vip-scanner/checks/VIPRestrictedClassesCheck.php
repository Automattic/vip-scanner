<?php

class VIPRestrictedClassesCheck extends CodeCheck
{
	protected static $forbidden_class_names = array(
		'WP_User_Query' => array( 'level' => 'Note', 'note' => 'Use of WP_User_Query' ),
	);

	function __construct() {
		parent::__construct( array(
			'PhpParser\Node\Expr\New_' => function( $node ) {
				if ( ! $node->class instanceof PhpParser\Node\Name ) {
					return;
				}
				$class_name = $node->class->toString();
				if ( in_array( $class_name, array_keys( self::$forbidden_class_names ) ) ) {
					$error = self::$forbidden_class_names[ $class_name ];
					$this->add_error( $class_name, $error['note'], $error['level'] );
				}
			},
		) );
	}
}
