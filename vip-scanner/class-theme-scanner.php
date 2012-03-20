<?php

class ThemeScanner extends DirectoryScanner
{
	function __construct( $theme, $checks ) {
		if( ! function_exists( 'get_theme_root' ) )
			return $this->add_error( 'wp-load', sprintf( '%s requires WordPress to be loaded.', get_class( $this ) ), 'blocker' );

		// Get Theme Path
		$path = sprintf( '%s/%s', get_theme_root(), $theme );

		// Call Parent Constructor
		parent::__construct( $path, $checks );
	}
}
