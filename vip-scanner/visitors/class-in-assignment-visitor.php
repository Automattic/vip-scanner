<?php

use PhpParser\Node;

/**
 * Parser node visitor to denote which variables function calls are assigned to
 *
 * This visitor adds an 'in_var' attribute to all nodes of function call type
 * if those functions are called on the right hand side of a variable
 * assignment. The 'in_var' attribute then simply holds the name of that
 * variable.
 */
class InAssignmentVisitor extends PhpParser\NodeVisitorAbstract {

	/**
	 * @var mixed the name of the variable the current expression is assigned to
	 */
	protected $var;

	public function enterNode( Node $node ) {
		if ( $node instanceof Node\Expr\Assign ) {
			$this->var = $node->var->name;
		}
	}

	public function leaveNode( Node $node ) {
		if ( $node instanceof Node\Expr\Assign &&
			$this->var == $node->var->name ) {
			$this->var = null;
		} elseif ( ! is_null( $this->var ) && (
			$node instanceof Node\Expr\FuncCall ||
			$node instanceof Node\Expr\MethodCall ||
			$node instanceof Node\Expr\StaticCall ||
			$node instanceof Node\Expr\Closure
		) ) {
		$node->setAttribute( 'in_var', $this->var );
		return $node;
		}
	}
}