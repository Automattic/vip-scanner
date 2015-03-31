<?php

class MethodCodeElement extends FunctionCodeElement {
	protected $singular = 'class method';
	protected $plural = 'class methods';

	function get_header() {
		// Add the keywords
		$header_items = array();
		foreach ( array( 'public', 'protected', 'private', 'static', 'abstract' ) as $keyword ) {
			$is_keyword = 'is' . ucfirst( $keyword );
			if ( $this->node->$is_keyword() ) {
				$header_items[] = array(
					'content' => esc_html( $keyword ),
					'style'   => array(
						'code'    => true,
						'classes' => array( "renderer-class-$keyword" ),
					)
				);
			}
		}
		return array_merge( $header_items,  parent::get_header() );
	}
}