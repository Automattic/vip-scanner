<?php

class ResourceRenderer extends AnalyzerRenderer {
	
	function set_resource_type( $singular, $plural ) {
		$this->singular = $singular;
		$this->plural = $plural;
	}
	
	function display_header() {
		$header_items = array();

		$name = esc_html( $this->name() );

		$header_items[] = sprintf( 
			'%1$s %2$s',
			$this->stylize_text( esc_html( $this->singular ), array( 
				'bold' => true,
				'classes' => array( 'renderer-resource-name', "renderer-resource-$name-name" ),
			) ),
			$name
		);

		return implode( ' ', $header_items );
	}
}
