<?php

class NamespaceCodeElement extends CodeElement {

	protected $singular = 'namespace';
	protected $plural = 'namespaces';

	/**
	 * Get the building blocks of the header to display
	 * @return array of arrays with 'content' and 'style' keys
	 */
	function get_header() {
		return array(
			'namespace',
			array(
				'content' => esc_html( $this->name() ),
				'style'   => array(
					'bold' => true,
					'classes' => array( 'renderer-namespace-name' ),
				)
			)
		);
	}
}
