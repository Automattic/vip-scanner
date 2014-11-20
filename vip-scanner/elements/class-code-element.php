<?php

abstract class CodeElement extends BaseElement {

	/**
	 * @var PhpParser\Node
	 */
	protected $node;

	/**
	 * Construct this CodeElement around a Node obtained from PHP-Parser
	 * @param PhpParser\Node $node
	 */
	function __construct( PhpParser\Node $node ) {
		$this->node = $node;
		$this->name = $node->name;
		$this->set_attributes();
	}

	/**
	 * Set the 'line(s)' attribute to this element's line number(s)
	 *
	 * Gives a hyphen-separated range if the element stretches over multiple
	 * lines, or a single line number otherwise. Accordingly, the attribute
	 * is named either 'lines' or 'line'.
	 *
	 * @return string the line number(s)
	 */
	protected function set_lines_attribute() {
		$start_line = $this->node->getAttribute( 'startLine' );
		$end_line   = $this->node->getAttribute( 'endLine' );
		if ( $start_line === $end_line ) {
			$this->attributes['line'] = $start_line;
		} else {
			$this->attributes['lines'] = sprintf( '%1$s-%2$s', $start_line, $end_line );
		}
	}

	/**
	 * Set the 'documenation' attribute to this element's PHPDoc string
	 * @return string
	 */
	protected function set_doc_attribute() {
		if ( $this->node->hasAttribute( 'comments' ) ) {
			foreach ( $this->node->getAttribute( 'comments' ) as $comment ) {
				if ( $comment instanceof PhpParser\Comment\Doc ) {
					$this->attributes['documentation'] = $comment->getText();
					return;
				}
			}
		}
	}

	/**
	 * Set some attributes to their appropriate values
	 *
	 * This includes the 'path', 'line(s)', and 'documentation' attributes.
	 * @return string
	 */
	public function set_attributes() {
		$this->attributes['path'] = $this->node->getAttribute( 'scope' );
		$this->set_lines_attribute();
		$this->set_doc_attribute();
	}
}