<?php

class PHPAnalyzer extends BaseAnalyzer {
	protected $metas = array();
	protected $hierarchy_metas = array(
		'namespaces' => 'NamespaceMeta',
		'classes'    => 'ClassMeta',
		'functions'  => 'FunctionMeta',
	);
	
	function __construct() {
		$this->metas = array(
			'files'		 => new MetaGroup( __( 'Files', 'theme-review' ), __( 'File', 'theme-review' ) ),
			'namespaces' => new MetaGroup( __( 'Namespaces', 'theme-review' ), __( 'Namespace', 'theme-review' ) ),
			'classes'	 => new MetaGroup( __( 'Classes', 'theme-review' ), __( 'Class', 'theme-review' ) ),
			'functions'  => new MetaGroup( __( 'Functions', 'theme-review' ), __( 'Function', 'theme-review' ) ),
		);
	}
	
	/**
	 * Runs the analyzer on the given $files.
	 * 
	 * @param array<AnalyzedFile> $files The files to process
	 */
	public function analyze( $files ) {
		foreach ( $files as $file ) {
			$file_meta = new FileMeta( $file );
			$this->add_metas( $file, $file_meta );
			$this->metas['files']->add_child_meta( $file_meta );
		}
	}

	/**
	 * 
	 * @param AnalyzedFile $file
	 * @param AnalyzerMeta $meta
	 */
	public function add_metas( $file, &$meta, $path = '', $hierarchy = null ) {
		if ( is_null( $hierarchy ) ) {
			$hierarchy = $file->get_check_hierarchy();
		}

		foreach ( $hierarchy as $level => $hierarchy_children ) {
			$code_elements = $file->get_code_elements( $level, $path );
			
			if ( empty( $code_elements ) ) {
				$this->add_metas( $file, $meta, $path, $hierarchy_children );
				
			} else {
				foreach ( $code_elements as $child_path => $child_element ) {
					
					if ( array_key_exists( $level, $this->hierarchy_metas ) ) {
						$child_meta = new $this->hierarchy_metas[$level]( $child_path );

						foreach ( $child_element as $prop_name => $prop_value ) {
							$child_meta->add_attribute( $prop_name, $prop_value );
						}

						$this->add_metas( $file, $child_meta, $child_path, $hierarchy_children );

						$meta->add_child_meta( $child_meta );
						
						// If the path is empty add this to the list of root metas
						if ( empty( $path ) ) {
							$this->metas[$level]->add_child_meta( $child_meta );
						}
					} else {
						$this->add_metas( $file, $meta, $child_path, $hierarchy_children );
					}
				}
			}
		}
	}
}
