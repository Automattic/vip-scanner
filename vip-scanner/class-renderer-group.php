<?php

class RendererGroup extends AnalyzerRenderer {
	protected $singular = '';
			
	function __construct( $plural, $singular, $attributes = array() ) {
		parent::__construct( $plural, $attributes );
		$this->singular = $singular;
	}
	
	function display( $echo = true ) {
		$output = '';
		
		// Output the header. Don't escape here because we expect the header to contain html.
		$output .= '<h3 class="renderer-group-header">' . $this->display_header() . '</h3>';
		
		$output .= '<div class="renderer-group-body">';
		foreach ( $this->children as $child ) {
			$output .= $child->display( false );
		}
		$output .= '</div>';
		
		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	function display_header() {
		return sprintf( 
			'<strong class="renderer-class-name">%s</strong> (%s)',
			esc_html( ucwords( $this->name() ) ),
			empty( $this->children ) ? '0' : esc_html( $this->get_child_summary() )
		);
	}
}