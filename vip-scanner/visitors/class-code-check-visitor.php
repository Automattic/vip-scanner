<?php

class CodeCheckVisitor extends PhpParser\NodeVisitorAbstract {

	protected $checks = array();

	public function __construct( $checks = array() ) {
		$this->checks = $checks;
	}

	public function leaveNode( PhpParser\Node $node ) {
		$this->current_node = $node;
		$this->check( $node );
	}

	public function get_current_line() {
		return $this->current_node->getAttribute( 'startLine' );
	}

	public function check( PhpParser\Node $node ) {
		foreach ( $this->checks as $type => $function ) {
			if ( get_class( $node ) === $type ) {
				call_user_func( $function, $node );
			}
		}
	}
}
