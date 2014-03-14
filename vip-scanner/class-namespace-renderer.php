<?php

class NamespaceRenderer extends AnalyzerRenderer {
	protected $singular = 'namespace';
	protected $plural = 'namespaces';
	
	function display_header() {
		return 'namespace <strong class="renderer-namespace-name">' . esc_html( $this->name() ) . '</strong>';
	}
}
