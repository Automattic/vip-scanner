<?php

class PHPAnalyzer extends BaseAnalyzer {
	protected $renderers = array();
	protected $hierarchy_metas = array(
		'namespaces' => 'NamespaceRenderer',
		'classes'    => 'ClassRenderer',
		'functions'  => 'FunctionRenderer',
	);
	
	function __construct() {
		$this->renderers = array(
			'files'		 => new RendererGroup( __( 'Files', 'theme-review' ), __( 'File', 'theme-review' ) ),
			'namespaces' => new RendererGroup( __( 'Namespaces', 'theme-review' ), __( 'Namespace', 'theme-review' ) ),
			'classes'	 => new RendererGroup( __( 'Classes', 'theme-review' ), __( 'Class', 'theme-review' ) ),
			'functions'  => new RendererGroup( __( 'Functions', 'theme-review' ), __( 'Function', 'theme-review' ) ),
		);
	}
	
	/**
	 * Runs the analyzer on the given $files.
	 * 
	 * @param array<AnalyzedFile> $files The files to process
	 */
	public function analyze( $files ) {
		foreach ( $files as $file ) {
			$file_meta = new FileRenderer( $file );
			$this->add_renderers( $file, $file_meta );
			$this->renderers['files']->add_child( $file_meta );
		}
	}

	/**
	 * 
	 * @param AnalyzedFile $file
	 * @param AnalyzerRenderer $renderer
	 */
	public function add_renderers( $file, &$renderer, $path = '', $hierarchy = null ) {
		if ( is_null( $hierarchy ) ) {
			$hierarchy = $file->get_check_hierarchy();
		}

		foreach ( $hierarchy as $level => $hierarchy_children ) {
			$code_elements = $file->get_code_elements( $level, $path );
			
			if ( empty( $code_elements ) ) {
				$this->add_renderers( $file, $renderer, $path, $hierarchy_children );
				
			} else {
				foreach ( $code_elements as $child_path => $child_element ) {
					
					if ( array_key_exists( $level, $this->hierarchy_metas ) ) {
						$child_meta = new $this->hierarchy_metas[$level]( $child_path );

						foreach ( $child_element as $prop_name => $prop_value ) {
							$child_meta->add_attribute( $prop_name, $prop_value );
						}

						$this->add_renderers( $file, $child_meta, $child_path, $hierarchy_children );

						$renderer->add_child( $child_meta );
						
						// If the path is empty add this to the list of root metas
						if ( empty( $path ) ) {
							$this->renderers[$level]->add_child( $child_meta );
						}
					} else {
						$this->add_renderers( $file, $renderer, $child_path, $hierarchy_children );
					}
				}
			}
		}
	}
}
