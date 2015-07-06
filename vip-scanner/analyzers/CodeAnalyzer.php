<?php

class CodeAnalyzer extends BaseAnalyzer {
	function __construct() {
		$this->elements = array(
			'files'      => new ElementGroup( __( 'Files', 'vip-scanner' ), __( 'File', 'vip-scanner' ) ),
			'namespaces' => new ElementGroup( __( 'Namespaces', 'vip-scanner' ), __( 'Namespace', 'vip-scanner' ) ),
			'classes'    => new ElementGroup( __( 'Classes', 'vip-scanner' ), __( 'Class', 'vip-scanner' ) ),
			'functions'  => new ElementGroup( __( 'Functions', 'vip-scanner' ), __( 'Function', 'vip-scanner' ) ),
			'totals'     => new ElementGroup( __( 'Totals', 'vip-scanner' ), __( 'Total', 'vip-scanner' ) ),
		);
	}

	/**
	 * Runs the analyzer on the given $files.
	 * 
	 * @param array<AnalyzedFile> $files The files to process
	 */
	public function analyze( $files ) {
		$total_lines = 0;

		foreach ( $files as $file ) {
			if ( $file->get_filetype() !== 'php' ) {
				continue;
			}

			$element = new FileElement( $file );
			$visitor = new AnalyzerVisitor( 'CodeElementFactory', $element, $this->elements );

			$traverser = new PhpParser\NodeTraverser;
			$traverser->addVisitor( $visitor );
			$traverser->traverse( $file->get_node_tree() );

			$this->elements['files']->add_child( $visitor->get_root() );
			$total_lines += (int) $element->get_stat( 'line_count' );
		}
		
		$this->elements['totals']->add_stat( 'total_lines', $total_lines );
	}
}
