<?php

class ResourceAnalyzer extends BaseAnalyzer {
	function __construct() {
		foreach ( ResourceElementFactory::$resource_types as $resource_class => $resource_group ) {
			foreach ( $resource_group as $resource ) {
				$this->elements[ $resource['plural'] ] = new ElementGroup( $resource['plural'], $resource['singular'] );
			}
		}
	}
	
	/**
	 * 
	 * @param array<AnalyzedFile> $files
	 */
	public function analyze( $files ) {
		// First we get the list of file metas
		$file_elements = $this->scanner->elements['files']->get_children();
		
		foreach ( $files as $file ) {
			if ( $file->get_filetype() !== 'php' ) {
				continue;
			}

			$filepath = $file->get_filepath();
			if ( array_key_exists( $filepath, $file_elements ) ) {
				$file_element = $file_elements[ $filepath ];
			} else {
				// This is not a file we can handle
				continue;
			}

			$visitor = new AnalyzerVisitor( 'ResourceElementFactory', $file_element, $this->elements );

			$traverser = new PhpParser\NodeTraverser;
			$traverser->addVisitor( $visitor );
			$traverser->traverse( $file->get_node_tree() );
		}
	}
}
