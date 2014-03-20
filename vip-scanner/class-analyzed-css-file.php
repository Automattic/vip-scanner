<?php

class AnalyzedCSSFile extends AnalyzedFile {
	protected $filepath = '';
	protected $filecontents = '';
	protected $processed_file_contents = '';
	protected $is_main_stylesheet = false;
	protected $theme = null;
	
	protected $hierarchy_elements = array(
		'rules'			   => array(),
		'media_directives' => array(),
	);
	
	protected $comments_regex = <<<EOT
		(\/\*(?:(?!\*\/)[\s\S])*\*\/)			# match a multiline comment
			|
		(\/\/(?:(?!\n|\r)[\s\S])*)$				# match a single line comment
EOT
		;
	
	protected $hierarchy_regexes = array(
		'media_directives' => array( 'regex' => <<<EOT
			(\s|\A)(?<name>@[^{]+)			# Match the definition of the directive
 			(?<contents>					# Match the contents of the directive
					 (				
 						{			
 							(?:
								 [^{}]++
								  |
								(?4)
							)*
						}			
					)				
			)
EOT
			, ),

		'rules'  => array( 'regex' => <<<EOT
			^\s*(?<name>[^{@}]+)
			{(?<contents>[^}]+)}
EOT
			, ),
	);
	
	protected $check_hierarchy = array(
		'media_directives' => array(
			'rules' => array(),
		),

		'rules' => array(),
	);
	
	/**
	 * Returns whether this file is the main stylesheet for a theme.
	 * @return boolean
	 */
	public function is_main_stylesheet() {
		return $this->is_main_stylesheet;
	}
	
	/**
	 * Gets the theme that this stylesheet defines if is_main_stylesheet() is true,
	 * and null otherwise.
	 * @return WP_Theme
	 */
	public function get_theme() {
		return $this->theme;
	}
	
	/**
	 * Analyzes this file.
	 */
	protected function analyze_file() {
		// Load the contents of the file
		if ( is_null( $this->filecontents ) || ! $this->filecontents ) {
			$this->filecontents = file_get_contents( $this->filepath );
		}
		
		if ( false === $this->filecontents ) {
			return;
		}

		// Strip strings and comments from the file. Preserve line numbers
		$stripped = $this->strip_strings_and_comments( $this->filecontents );

		// Do the php check hierarchy
		$this->processed_file_contents = $this->do_check_hierarchy( '', $this->check_hierarchy, $stripped, 0 );

		// Only continue if this file is a style.css file.
		if ( $this->get_filename() !== 'style.css' ) {
			return;
		}

		// Check if this file is the main stylesheet for a theme
		$path = pathinfo( $this->get_filepath(), PATHINFO_DIRNAME );
		$theme_root = get_theme_root( $this->get_filepath() );
		if ( 0 === strpos( $path, $theme_root ) ) {
			$path = substr( $path, strlen( $theme_root ) );
		}

		$theme = wp_get_theme( $path );
		if ( !empty( $theme ) && is_a( $theme, 'WP_Theme' ) ) {
			$this->is_main_stylesheet = true;
			$this->theme = $theme;
		}
	}
	
	protected function get_strings_and_comments_regexes() {
		return array(
			$this->comments_regex,
		);
	}
}