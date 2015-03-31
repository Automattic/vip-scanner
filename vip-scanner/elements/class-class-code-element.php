<?php

class ClassCodeElement extends CodeElement {
	protected $singular = 'class';
	protected $plural = 'classes';

	/**
	 * Get the building blocks of the header to display
	 * @return array of arrays with 'content' and 'style' keys
	 */
	function get_header() {
		$header_items = array();

		// Add the abstract keyword
		if ( $this->node->isAbstract() ) {
			$header_items[] = array(
				'content' => esc_html( 'abstract' ),
				'style'   => array(
					'code' => true,
					'classes' => array( 'renderer-class-abstract' ),
				),
			);
		}

		$header_items[] = 'class';
		$header_items[] = array(
			'content' => $this->prefixed_name(),
			'style'   => array(
				'bold' => true,
				'classes' => array( 'renderer-class-name' ),
			),
		);

		// Add any inheritance
		if ( ! empty( $this->node->extends ) ) {
			$header_items[] = 'extends';
			$header_items[] = array(
				'content' => esc_html( $this->node->extends->toString() ),
				'style'   => array(
					'code' => true,
					'classes' => array( 'renderer-class-parentclass' ),
				)
			);
		}

		return $header_items;
	}
}