<?php

class CodeElementFactory {
	protected static $type_to_element = array(
		'PhpParser\Node\Stmt\Namespace_'  => 'NamespaceCodeElement',
		'PhpParser\Node\Stmt\Class_'      => 'ClassCodeElement',
		'PhpParser\Node\Stmt\ClassMethod' => 'MethodCodeElement',
		'PhpParser\Node\Stmt\Function_'   => 'FunctionCodeElement',
	);

	public static function create_element( PhpParser\Node $node ) {
		$node_class = get_class( $node );
		if ( array_key_exists( $node_class, self::$type_to_element ) ) {
			return new self::$type_to_element[ $node_class ]( $node );
		}
		return false;
	}
}
