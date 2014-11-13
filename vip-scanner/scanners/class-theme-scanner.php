<?php

class ThemeScanner extends DirectoryScanner
{
	function __construct( $theme, $review ) {
		if( ! function_exists( 'get_theme_root' ) )
			return $this->add_error( 'wp-load', sprintf( '%s requires WordPress to be loaded.', get_class( $this ) ), 'blocker' );

 		// decide whether to interpret theme as a path by checking if the path exists
		$potential_file_path = realpath( $theme );
		if ( $potential_file_path ) {
			$path = $potential_file_path;
		} else {
			$path = sprintf( '%s/%s', get_theme_root(), $theme );
		}

		// Call Parent Constructor
		parent::__construct( $path, $review );
	}
}