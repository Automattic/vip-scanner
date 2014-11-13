<?php

class FunctionRenderer extends AnalyzerRenderer {
	protected $singular = 'function';
	protected $plural = 'functions';
	
	function display_header() {
		$header_items = array();

		// Add the keywords
		foreach ( array( 'visibility', 'static', 'abstract' ) as $keyword ) {
			if ( array_key_exists( $keyword, $this->attributes ) && !empty( $this->attributes[$keyword] ) ) {
				$header_items[] = $this->stylize_text(
					esc_html( $this->attributes[$keyword] ),
					array(
						'code'    => true,
						'classes' => array( "renderer-class-$keyword" ),
					)
				);
			}
		}

		$args = $this->get_attribute( 'args' );
		if ( is_array( $args ) ) {
			$header_items[] = sprintf(
				'function %s%s',
				$this->stylize_text( esc_html( $this->name() ), array( 'bold' => true, 'classes' => array( 'renderer-function-name' ) ) ),
				$args === ';' ? ';' : '(' . esc_html( implode( ', ', $args ) ) . ')'
			);
		} else {
			$header_items[] = sprintf(
				'function %s%s',
				$this->stylize_text( esc_html( $this->name() ), array( 'bold' => true, 'classes' => array( 'renderer-function-name' ) ) ),
				$args === ';' ? ';' : '(' . esc_html( $args ) . ')'
			);
		}

		return implode( ' ', $header_items );
	}
}