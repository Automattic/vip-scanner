<?php

class ClassMeta extends AnalyzerMeta {
	protected $singular = 'class';
	protected $plural = 'classes';
	
	function display_header() {
		$header_items = array();

		// Add the abstract keyword
		if ( array_key_exists( 'abstract', $this->attributes ) && !empty( $this->attributes['abstract'] ) ) {
			$header_items[] = '<code class="meta-class-abstract">' . esc_html( $this->attributes['abstract'] ) . '</code>';
		}

		$header_items[] = sprintf( 
			'class <strong class="meta-class-name">%s</strong>',
			esc_html( $this->name() )
		);

		// Add any inheritance
		if ( array_key_exists( 'parentclass', $this->attributes ) && !empty( $this->attributes['parentclass'] ) ) {
			$header_items[] = 'extends <code class="meta-class-parentclass">' . esc_html( $this->attributes['parentclass'] ) . '</code>';
		}
		
		if ( !empty( $this->child_metas ) ) {
			$header_items[] = sprintf( '<small>(%s)</small>', esc_html( $this->get_child_summary() ) );
		}
		
		return implode( ' ', $header_items );
	}
}