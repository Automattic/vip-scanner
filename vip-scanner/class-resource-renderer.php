<?php

class ResourceRenderer extends AnalyzerRenderer {
	
	function set_resource_type( $singular, $plural ) {
		$this->singular = $singular;
		$this->plural = $plural;
	}
	
	function display_header() {
		$header_items = array();
		
		$header_items[] = sprintf( 
			'%1$s <strong class="renderer-resource-name renderer-resource-%1$s-name">%2$s</strong>',
			esc_html( $this->singular ),
			esc_html( $this->name() )
		);
		
		return implode( ' ', $header_items );
	}
}
