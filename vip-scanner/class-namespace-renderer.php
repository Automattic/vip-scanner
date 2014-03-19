<?php

class NamespaceRenderer extends AnalyzerRenderer {
	protected $singular = 'namespace';
	protected $plural = 'namespaces';
	
	function display_header() {
		return 'namespace ' . $this->stylize_text( esc_html( $this->name() ), array( 'bold' => true, 'classes' => array( 'renderer-namespace-name' ) ) );
	}
}
