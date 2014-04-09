<?php

class CustomResourceAnalyzer extends BaseAnalyzer {
	protected $remove_chars = array(
		'\'',
		'"',
		'.',
		' ',
		"\t",
	);

	protected $resource_types = array(
		array(
			'func_name' => array( 'apply_filters' ),
			'plural'	=> 'filters',
			'singular'  => 'filter',
		),
		
		array(
			'func_name' => array( 'do_action' ),
			'plural'	=> 'actions',
			'singular'  => 'action',
		),
		
		array(
			'func_name'	=> array( '->add_cap' ),
			'plural'	=> 'capabilities',
			'singular'  => 'capability',
			'regexes'   => array(  ),
		),
		
		array(
			'func_name' => array( 'add_role' ),
			'plural'	=> 'roles',
			'singular'  => 'role',
		),
		
		array(
			'func_name' => array( 'add_shortcode' ),
			'plural'    => 'shortcodes',
			'singular'  => 'shortcode',
		),
		
		array(
			'func_name' => array( 'register_post_type' ),
			'plural'    => 'custom post types',
			'singular'  => 'custom post type',
		),
		
		array(
			'func_name' => array( 'register_taxonomy' ),
			'plural'    => 'taxonomies',
			'singular'  => 'taxonomy',
		),
		
		array(
			'func_name' => array( 'wp_enqueue_script', 'wp_register_script' ),
			'plural'    => 'scripts',
			'singular'  => 'script',
		),
		
		array(
			'func_name' => array( 'wp_enqueue_style', 'wp_register_style' ),
			'plural'    => 'styles',
			'singular'  => 'style',
		),
	);
	
	function __construct() {
		foreach ( $this->resource_types as $resource ) {
			$this->renderers[$resource['plural']] = new RendererGroup( $resource['plural'], $resource['singular'] );
		}
	}
	
	/**
	 * 
	 * @param array<AnalyzedFile> $files
	 */
	public function analyze( $files ) {
		// First we get the list of file metas
		$file_metas = $this->scanner->renderers['files']->get_children();
		
		foreach ( $files as $file ) {
			if ( $file->get_filetype() !== 'php' ) {
				continue;
			}
			
			$filepath = $file->get_filepath();
			if ( !array_key_exists( $filepath, $file_metas ) ) {
				// This is not a file we can handle
				var_dump( "Not scanning file: {$file->get_filepath()}: " . $file_metas[$filepath]->get_file()->get_filepath());
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
	 * @param FileRenderer $file_renderer The meta object for this file.
	 */
	public function scan_file( $file, $file_renderer ) {
		$function_calls = $file->get_code_elements( 'function_calls' );

		foreach ( $this->resource_types as $resource ) {
			foreach ( $resource['func_name'] as $function_name ) {
				foreach ( $function_calls as $call_path => $functions ) {
					// check and see if this function was called
					if ( array_key_exists( $function_name, $functions ) ) {
						if ( isset( $functions[$function_name]['args'] ) ) {
							$calls = array( $functions[$function_name] );
						} else {
							$calls = $functions[$function_name];
						}

						foreach( $calls as $call ) {
							$child_renderer = new ResourceRenderer( str_replace( $this->remove_chars, '', $call['args'][0] ) );
							$child_renderer->set_resource_type( $resource['singular'], $resource['plural'] );
							$child_renderer->add_attribute( 'file', $file->get_filename() );
							$child_renderer->add_attribute( 'args', $call['args'] );

							$file_renderer->add_child( $child_renderer );
							$this->renderers[$resource['plural']]->add_child( $child_renderer );
						}
					}
				}
			}
		}
	}
}
