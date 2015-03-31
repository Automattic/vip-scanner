<?php

class ThemeAnalyzer extends BaseAnalyzer {
	protected $parent_theme_regex = '';
	
	public function analyze( $files ) {
		$totals_element = $this->scanner->elements['totals'];
		$files_element = $this->scanner->elements['files'];

		// Search for a stylesheet in the list of files
		foreach ( $files as $file ) {
			if ( $file->get_filetype() === 'css'  ) {
				$file_element = new FileElement( $file );
				$files_element->add_child( $file_element );
				$theme = $file->get_theme();

				if ( !is_null( $theme ) ) {
					$parent = $theme->parent();
					if ( $parent ) {
						$totals_element->add_attribute( 'parent_theme', $parent->Name );
					}
				}
			}
		}
	}

}
