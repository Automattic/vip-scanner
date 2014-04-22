<?php

class PHPAnalyzer extends BaseAnalyzer {
	protected $renderers = array();
	protected $hierarchy_metas = array(
		'namespaces' => 'NamespaceRenderer',
		'classes'    => 'ClassRenderer',
		'functions'  => 'FunctionRenderer',
	);

	protected $check_hierarchy = array(
		'php' => array(
			'namespaces' => array(
				'classes' => array(
					'functions' => array(),
					'members'   => array(),
				),
			),

			'classes' => array(
				'functions' => array(),
				'members'	=> array(),
			),

			'functions' => array(),
			'members'   => array(),
		),
	);

	function __construct() {
		$this->renderers = array(
			'files'		 => new RendererGroup( __( 'Files', 'theme-check' ), __( 'File', 'theme-check' ) ),
			'namespaces' => new RendererGroup( __( 'Namespaces', 'theme-check' ), __( 'Namespace', 'theme-check' ) ),
			'classes'	 => new RendererGroup( __( 'Classes', 'theme-check' ), __( 'Class', 'theme-check' ) ),
			'functions'  => new RendererGroup( __( 'Functions', 'theme-check' ), __( 'Function', 'theme-check' ) ),
			'totals'     => new RendererGroup( __( 'Totals', 'theme-check' ), __( 'Total', 'theme-check' ) ),
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

			$file_meta = new FileRenderer( $file );
			$this->add_renderers( $file, $file_meta );
			$this->renderers['files']->add_child( $file_meta );
			$total_lines += (int) $file_meta->get_stat( 'line_count' );
		}
		
		$this->renderers['totals']->add_stat( 'total_lines', $total_lines );
	}

	/**
	 * 
	 * @param AnalyzedFile $file
	 * @param AnalyzerRenderer $renderer
	 */
	public function add_renderers( $file, &$renderer, $path = '', $hierarchy = null ) {
		if ( is_null( $hierarchy ) ) {
			$hierarchy = $this->check_hierarchy;
		}

		foreach ( $hierarchy as $level => $hierarchy_children ) {
			$code_elements = $file->get_code_elements( $level, $path );
			if ( empty( $code_elements ) ) {
				$this->add_renderers( $file, $renderer, $path, $hierarchy_children );
				
			} else {
				foreach ( $code_elements as $child_path => $child_element ) {
					if ( array_key_exists( $level, $this->hierarchy_metas ) ) {
						$child_meta = new $this->hierarchy_metas[$level]( $child_path );
						$child_meta->add_attribute( 'file', $file->get_filename() );

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
