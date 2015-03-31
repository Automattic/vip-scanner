<?php

class CodeAnalyzer extends BaseAnalyzer {
	function __construct() {
		$this->elements = array(
			'files'      => new ElementGroup( __( 'Files', 'theme-check' ), __( 'File', 'theme-check' ) ),
			'namespaces' => new ElementGroup( __( 'Namespaces', 'theme-check' ), __( 'Namespace', 'theme-check' ) ),
			'classes'    => new ElementGroup( __( 'Classes', 'theme-check' ), __( 'Class', 'theme-check' ) ),
			'functions'  => new ElementGroup( __( 'Functions', 'theme-check' ), __( 'Function', 'theme-check' ) ),
			'totals'     => new ElementGroup( __( 'Totals', 'theme-check' ), __( 'Total', 'theme-check' ) ),
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
