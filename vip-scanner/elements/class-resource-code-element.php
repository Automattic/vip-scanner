<?php

class ResourceCodeElement extends CodeElement {
	protected $remove_chars = array(
			'\'',
			'"',
			'.',
			' ',
			"\t",
	);

	/**
	 * Construct this ResourceCodeElement around a Node obtained from PHP-Parser
	 * @param PhpParser\Node $node
	 */
	function __construct( PhpParser\Node $node ) {
		parent::__construct( $node );
		$this->attributes['args'] = $this->get_args();
		$this->name = str_replace( $this->remove_chars, '', $this->attributes['args'][0] );
	}

	/**
	 * Set the type of resource this element stands for
	 *
	 * @param string $singular the singular form of the resource type's name
	 * @param string $plural the plural form of the resource type's name
	 */
	function set_resource_type( $singular, $plural ) {
		$this->singular = $singular;
		$this->plural = $plural;
	}

	/**
	 * Get the building blocks of the header to display
	 * @return array of arrays with 'content' and 'style' keys
	 */
	function get_header() {
		$name = esc_html( $this->name() );

		return array(
			array(
				'content' => esc_html( $this->singular ),
				'style'   => array(
					'bold' => true,
					'classes' => array( 'renderer-resource-name', "renderer-resource-$name-name" ),
				)
			),
			array( 'content' => $this->prefixed_name() ),
		);
	}

	/**
	 * Return the arguments to this function call
	 * @return string the arguments as passed to this function call
	 */
	function get_args() {
		$pretty_printer = new PhpParser\PrettyPrinter\Standard;
		$args = array();
		foreach ( $this->node->args as $arg ) {
			$args[] = $pretty_printer->pArg( $arg );
		}
		return $args;
	}
}
