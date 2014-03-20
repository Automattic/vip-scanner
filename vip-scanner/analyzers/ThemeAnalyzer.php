<?php

class ThemeAnalyzer extends BaseAnalyzer {
	protected $parent_theme_regex = '';
	
	public function analyze( $files ) {
		$totals_renderer = $this->scanner->renderers['totals'];
		$files_renderer = $this->scanner->renderers['files'];

		// Search for a stylesheet in the list of files
		foreach ( $files as $file ) {
			if ( $file->get_filetype() === 'css'  ) {
				$file_renderer = new FileRenderer( $file );
				$files_renderer->add_child( $file_renderer );
				$theme = $file->get_theme();

				if ( !is_null( $theme ) ) {
					$parent = $theme->parent();
					if ( $parent ) {
						$totals_renderer->add_attribute( 'parent_theme', $parent->Name );
					}
				}
			}
		}
	}

}
