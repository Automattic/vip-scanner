<?php

class FunctionMeta extends AnalyzerMeta {
	protected $singular = 'function';
	protected $plural = 'functions';
	
	function display_header() {
		$header_items = array();
		
		// Add the keywords
		foreach ( array( 'visibility', 'static', 'abstract' ) as $keyword ) {
			if ( array_key_exists( $keyword, $this->attributes ) && !empty( $this->attributes[$keyword] ) ) {
				$header_items[] = "<code class='meta-class-$keyword'>" . esc_html( $this->attributes[$keyword] ) . '</code>';
			}
		}

		$args = $this->get_attribute( 'args' );
		$header_items[] = sprintf( 
			'function <strong class="meta-function-name">%s</strong>%s',
			esc_html( $this->name() ),
			$args === ';' ? ';' : '(' . esc_html( $args ) . ')'
		);
		
		return implode( ' ', $header_items );
	}
}