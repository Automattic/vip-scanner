<?php

class CustomResourceAnalyzer extends BaseAnalyzer {
	protected $resource_types = array(
		array(
			'func_name' => 'apply_filters',
			'plural'	=> 'filters',
			'singular'  => 'filter',
		),
		
		array(
			'func_name' => 'do_action',
			'plural'	=> 'actions',
			'singular'  => 'action',
		),
		
		array(
			'func_name'	=> '->add_cap',
			'plural'	=> 'capabilities',
			'singular'  => 'capability',
			'regexes'   => array(  ),
		),
		
		array(
			'func_name' => 'add_role',
			'plural'	=> 'roles',
			'singular'  => 'role',
		),
		
		array(
			'func_name' => 'add_shortcode',
			'plural'    => 'shortcodes',
			'singular'  => 'shortcode',
		),
		
		array(
			'func_name' => 'register_post_type',
			'plural'    => 'custom post types',
			'singular'  => 'custom post type',
		),
		
		array(
			'func_name' => 'register_taxonomy',
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
		$file_functions = $file->get_code_elements( 'functions' );
		
		foreach ( $this->resource_types as $resource ) {
			$regexes = array();
				
			if ( is_array( $resource['func_name'] ) ) {
				foreach ( $resource['func_name'] as $func_name ) {
					$regexes[] = "/{$func_name}\s*\(\s*(?<name>([a-zA-Z0-9_'\".$-]|\s*)+)/ix";
				}
			} else {
				$regexes[] = "/{$resource['func_name']}\s*\(\s*(?<name>([a-zA-Z0-9_'\".$-]|\s*)+)/ix";
			}
				
			if ( isset( $resource['regexes'] ) ) {
				$regexes = array_merge( $regexes, $resource['regexes'] );
			}
			
			$remove_chars = array(
				'\'',
				'"',
				'.',
				' ',
				"\t",
			);
			
			foreach ( $regexes as $regex ) {
				foreach ( $file_functions as $function_path => $functions ) {
					// Scan the functions in the file
					foreach ( $functions as $function ) {
						preg_match_all( $regex, $function['contents'], $matches, PREG_OFFSET_CAPTURE );
						foreach ( $matches['name'] as $match ) {
							$match = str_replace( $remove_chars, '', $match );
							$child_renderer = $this->create_child_renderer_from_match( $match, $function['contents'], $resource, $file, $function['line'] );
							$file_renderer->add_child( $child_renderer );
							$this->renderers[$resource['plural']]->add_child( $child_renderer );
						}
					}

					// Scan the file contents after processing to catch global resource calls
					$phpelements = $file->get_code_elements( 'php' );
					foreach( $phpelements[''] as $phpcontent ) {
						$matches = array();
						preg_match_all( $regex, $phpcontent['contents'], $matches, PREG_OFFSET_CAPTURE );
						foreach ( $matches['name'] as $match ) {
							$match = str_replace( $remove_chars, '', $match );
							$child_renderer = $this->create_child_renderer_from_match( $match, $phpcontent['contents'], $resource, $file, $phpcontent['line'] );
							$file_renderer->add_child( $child_renderer );
							$this->renderers[$resource['plural']]->add_child( $child_renderer );
						}
					}
				}
			}
		}
	}
	
	public function create_child_renderer_from_match( $match, $contents, $resource, $file, $line_offset = 0 ) {
		$child_renderer = new ResourceRenderer( $match[0] );
		$child_renderer->set_resource_type( $resource['singular'], $resource['plural'] );
		$child_renderer->add_attribute( 'line', $file->compute_line_number( $contents, $match[1], $line_offset ) );
		$child_renderer->add_attribute( 'file', $file->get_filename() );
		return $child_renderer;
	}
}
