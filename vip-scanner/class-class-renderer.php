<?php

class ClassRenderer extends AnalyzerRenderer {
	protected $singular = 'class';
	protected $plural = 'classes';
	
 	function display_header() {
		$header_items = array();

		// Add the abstract keyword
		if ( array_key_exists( 'abstract', $this->attributes ) && !empty( $this->attributes['abstract'] ) ) {
			$header_items[] = $this->stylize_text( esc_html( $this->attributes['abstract'] ), array(
				'code' => true,
				'classes' => array( 'renderer-class-abstract' )
			) );
		}

		$header_items[] = 'class ' . $this->stylize_text( esc_html( $this->name() ), array(
			'bold' => true,
			'classes' => array( 'renderer-class-name' ),
		) );

		// Add any inheritance
		if ( array_key_exists( 'parentclass', $this->attributes ) && !empty( $this->attributes['parentclass'] ) ) {
			$header_items[] = 'extends ' . $this->stylize_text( esc_html( $this->attributes['parentclass'] ), array(
				'code' => true,
				'classes' => array( 'renderer-class-parentclass' ),
			) );
		}

		if ( !empty( $this->children ) ) {
			$header_items[] = $this->stylize_text( esc_html( '(' . $this->get_child_summary() . ')' ), array(
				'small' => true,
			) );
		}
		
		return implode( ' ', $header_items );
	}
}