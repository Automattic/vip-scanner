<?php

class CustomResourceAnalyzer extends BaseAnalyzer {
	protected $resource_types = array(
		array(
			'func_name' => array( 'apply_filters' ),
			'call_type' => 'function_calls',
			'plural'	=> 'filters',
			'singular'  => 'filter',
		),
		
		array(
			'func_name' => array( 'do_action' ),
			'call_type' => 'function_calls',
			'plural'	=> 'actions',
			'singular'  => 'action',
		),
		
		array(
			'func_name'	=> array( 'add_cap' ),
			'call_type' => 'method_calls',
			'plural'	=> 'capabilities',
			'singular'  => 'capability',
			'regexes'   => array(  ),
		),
		
		array(
			'func_name' => array( 'add_role' ),
			'call_type' => 'function_calls',
			'plural'	=> 'roles',
			'singular'  => 'role',
		),
		
		array(
			'func_name' => array( 'add_shortcode' ),
			'call_type' => 'function_calls',
			'plural'    => 'shortcodes',
			'singular'  => 'shortcode',
		),
		
		array(
			'func_name' => array( 'register_post_type' ),
			'call_type' => 'function_calls',
			'plural'    => 'custom post types',
			'singular'  => 'custom post type',
		),
		
		array(
			'func_name' => array( 'register_taxonomy' ),
			'call_type' => 'function_calls',
			'plural'    => 'taxonomies',
			'singular'  => 'taxonomy',
		),
		
		array(
			'func_name' => array( 'wp_enqueue_script', 'wp_register_script' ),
			'call_type' => 'function_calls',
			'plural'    => 'scripts',
			'singular'  => 'script',
		),
		
		array(
			'func_name' => array( 'wp_enqueue_style', 'wp_register_style' ),
			'call_type' => 'function_calls',
			'plural'    => 'styles',
			'singular'  => 'style',
		),
	);
	
	function __construct() {
		foreach ( $this->resource_types as $resource ) {
			$this->elements[ $resource['plural'] ] = new ElementGroup( $resource['plural'], $resource['singular'] );
		}
	}
	
	/**
	 * 
	 * @param array<AnalyzedFile> $files
	 */
	public function analyze( $files ) {
		// First we get the list of file metas
		$file_metas = $this->scanner->elements['files']->get_children();
		
		foreach ( $files as $file ) {
			if ( $file->get_filetype() !== 'php' ) {
				continue;
			}
			
			$filepath = $file->get_filepath();
			if ( !array_key_exists( $filepath, $file_metas ) ) {
				// This is not a file we can handle
				continue;
			}
			
			// Scan this file for custom resources
			$this->scan_file( $file, $file_metas[$filepath] );
		}
	}
	
	/**
	 * Scans the given $file, looking for a list of functions that we want to identify
	 * to locate custom capabilities, taxonomies, etc...
	 * 
	 * Creates meta objects for everything that we find.
	 * 
	 * @param AnalyzedFile $file The file to scan
	 * @param FileElement $file_element The meta object for this file.
	 */
	public function scan_file( $file, $file_element ) {
		foreach ( $this->resource_types as $resource ) {
			foreach ( $resource['func_name'] as $function_name ) {
				$calls = $file->get_code_elements( $resource['call_type'] );
				foreach ( $calls as $call_path => $functions ) {
					// check and see if this function was called
					if ( array_key_exists( $function_name, $functions ) ) {
						if ( ! is_array( $functions[ $function_name ] ) ) {
							$calls = array( $functions[ $function_name ] );
						} else {
							$calls = $functions[ $function_name ];
						}

						foreach( $calls as $call ) {
							$analyzer = new ResourceCodeElement( $call );
							$analyzer->set_resource_type( $resource['singular'], $resource['plural'] );
							$file_element->add_child( $analyzer );
							$this->elements[ $resource['plural'] ]->add_child( $analyzer );
						}
					}
				}
			}
		}
	}
}
