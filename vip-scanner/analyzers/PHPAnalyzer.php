<?php

class PHPAnalyzer extends BaseAnalyzer {
	protected $hierarchy_metas = array(
		'namespaces' => 'NamespaceCodeElement',
		'classes'    => 'ClassCodeElement',
		'methods'    => 'MethodCodeElement',
		'functions'  => 'FunctionCodeElement',
	);

	protected $check_hierarchy = array(
		'php' => array(
			'namespaces' => array(
				'classes' => array(
					'methods' => array(),
					'members' => array(),
				),
			),

			'classes' => array(
				'methods' => array(),
				'members' => array(),
			),

			'functions' => array(),
			'members'   => array(),
		),
	);

	function __construct() {
		$this->elements = array(
			'files'		 => new ElementGroup( __( 'Files', 'theme-check' ), __( 'File', 'theme-check' ) ),
			'namespaces' => new ElementGroup( __( 'Namespaces', 'theme-check' ), __( 'Namespace', 'theme-check' ) ),
			'classes'	 => new ElementGroup( __( 'Classes', 'theme-check' ), __( 'Class', 'theme-check' ) ),
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
			$this->add_elements( $file, $element );
			$this->elements['files']->add_child( $element );
			$total_lines += (int) $element->get_stat( 'line_count' );
		}
		
		$this->elements['totals']->add_stat( 'total_lines', $total_lines );
	}

	/**
	 * 
	 * @param AnalyzedFile $file
	 * @param BaseElement $element
	 */
	public function add_elements( $file, &$element, $path = '', $hierarchy = null ) {
		if ( is_null( $hierarchy ) ) {
			$hierarchy = $this->check_hierarchy;
		}

		foreach ( $hierarchy as $level => $hierarchy_children ) {
			$code_elements = $file->get_code_elements( $level, $path );
			if ( empty( $code_elements ) ) {
				$this->add_elements( $file, $element, $path, $hierarchy_children );
				
			} else {
				foreach ( $code_elements as $child_name => $child_element ) {
					if ( array_key_exists( $level, $this->hierarchy_metas ) ) {
						$child_element = new $this->hierarchy_metas[ $level ]( $child_element );
						$this->add_elements( $file, $child_element, $child_name, $hierarchy_children );
						$element->add_child( $child_element );

						// If the path is empty add this to the list of root metas
						if ( empty( $path ) ) {
							$this->elements[ $level ]->add_child( $child_element );
						}
					} else {
						$this->add_elements( $file, $element, $child_name, $hierarchy_children );
					}
				}
			}
		}
	}
}
