<?php

class FunctionCodeElement extends CodeElement {
	protected $singular = 'function';
	protected $plural = 'functions';

	/**
	 * Return this function's parameters, including types, default values, etc
	 * @return string the parameters, separated by commas and spaces
	 */
	function get_params() {
		$prettyPrinter = new PhpParser\PrettyPrinter\Standard;
		$pretty = array();
		foreach ( $this->node->params as $param ) {
			$pretty[] = $prettyPrinter->pParam( $param );
		}
		return implode( ', ', $pretty );
	}

	/**
	 * Get the building blocks of the header to display
	 * @return array of arrays with 'content' and 'style' keys
	 */
	function get_header() {
		return array(
			'function',
			array(
				'content' => $this->prefixed_name(),
				'style'   => array(
					'bold'    => true,
					'classes' => array( 'renderer-function-name' )
				),
			),
			'(' . esc_html( ( $this->get_params() ) ) . ')',
		);
	}
}