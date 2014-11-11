<?php

use PhpParser\Node;

class ScopeVisitor extends PhpParser\NodeVisitorAbstract {
	const SCOPE_SEPARATOR = '::';
	const CLOSURE_NAME = '{closure}';

	/**
	 * @var array The scope stack for the current node
	 */
	protected $scope = array();

	public function enterNode( Node $node ) {
		$node->setAttribute(
			'scope',
			implode( self::SCOPE_SEPARATOR, $this->scope )
		);

		if (
			$node instanceof Node\Stmt\NameSpace_ ||
			$node instanceof Node\Stmt\Class_ ||
			$node instanceof Node\Stmt\ClassMethod ||
			$node instanceof Node\Stmt\Function_
		) {
			$this->scope[] = $node->name;
		} elseif ( $node instanceof Node\Expr\Closure ) {
			$this->scope[] = self::CLOSURE_NAME;
		}
		return $node;
	}

	public function leaveNode( Node $node ) {
		if (
			$node instanceof Node\Stmt\NameSpace_ ||
			$node instanceof Node\Stmt\Class_ ||
			$node instanceof Node\Stmt\ClassMethod ||
			$node instanceof Node\Stmt\Function_ ||
			$node instanceof Node\Expr\Closure
		) {
			array_pop( $this->scope );
		}
	}
}