<?php

/**
 * Populate an empty root FileElement with the hierarchy of its child elements,
 * adding those child elements to 'global', i.e. cross-file ElementGroups.
 */
class AnalyzerVisitor extends PhpParser\NodeVisitorAbstract {
	/**
	 * A dynamic stack representing the scope of the element currently
	 * visited, with the corresponding file's FileElement at its root
	 *
	 * @var BaseElement[]
	 */
	protected $stack = array();
	/**
	 * Array holding cross-file ElementGroups
	 * @var array<string, ElementGroup>
	 */
	protected $global_elements = array();

	/**
	 * @param string $factory the name of an element factory class
	 * @param FileElement $root the root FileElement to be populated
	 * @param array $global_elements ElementGroups
	 */
	function __construct( $factory, $root, $global_elements ) {
		$this->factory = $factory;
		$this->stack[] = $root;
		$this->global_elements = $global_elements;
	}

	/**
	 * Get the root FileElement, with child elements added.
	 * @return FileElement
	 */
	function get_root() {
		return $this->stack[0];
	}

	public function enterNode( PhpParser\Node $node ) {
		$factory = $this->factory;
		$element = $factory::create_element( $node );
		if ( $element !== false ) {
			$this->stack[] = $element;
		}
	}

	public function leaveNode( PhpParser\Node $node ) {
		$element = end( $this->stack );
		// Is the element on top of the stack for the node we're leaving?
		if ( $element instanceof CodeElement && $element->node == $node ) {
			// Remove it from the stack
			array_pop( $this->stack );

			// Add it to the new top element on the stack, which thus becomes
			// its parent.
			end( $this->stack )->add_child( $element );

			// Add the element to $this->global_elements if its type is being
			// tracked there.
			if ( array_key_exists( $element->plural(), $this->global_elements ) ) {
				$this->global_elements[ $element->plural() ]->add_child( $element );
			}
		}
	}
}
