<?php

class NamespaceMeta extends AnalyzerMeta {
	protected $singular = 'namespace';
	protected $plural = 'namespaces';
	
	function display_header() {
		return 'namespace <strong class="meta-namespace-name">' . esc_html( $this->name() ) . '</strong>';
	}
}
