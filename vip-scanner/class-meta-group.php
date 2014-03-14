<?php

class MetaGroup extends AnalyzerMeta {
	protected $singular = '';
			
	function __construct( $plural, $singular, $attributes = array() ) {
		parent::__construct( $plural, $attributes );
		$this->singular = $singular;
	}
	
	function display( $echo = true ) {
		$output = '';
		
		// Output the header. Don't escape here because we expect the header to contain html.
		$output .= '<h3 class="meta-group-header">' . $this->display_header() . '</h3>';
		
		$output .= '<div class="meta-group-body">';
		foreach ( $this->child_metas as $meta ) {
			$output .= $meta->display( false );
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
			'<strong class="meta-class-name">%s</strong> (%s)',
			esc_html( ucwords( $this->name() ) ),
			empty( $this->child_metas ) ? '0' : esc_html( $this->get_child_summary() )
		);
	}
}