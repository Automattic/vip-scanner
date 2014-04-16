<?php

class ThemeScanner extends DirectoryScanner
{
	function __construct( $theme, $review ) {
		if( ! function_exists( 'get_theme_root' ) )
			return $this->add_error( 'wp-load', sprintf( '%s requires WordPress to be loaded.', get_class( $this ) ), 'blocker' );

        //decide whether to interpret theme as a path

        if ( ( strpos( $theme, '/') !== false ) && !in_array( substr( $theme, 0, 4), array( 'pub/', 'vip/' ) ) ) {
            $path = $theme;
        }else{
            $path = sprintf( '%s/%s', get_theme_root(), $theme );
        }
        
		// Call Parent Constructor
		parent::__construct( $path, $review );
	}
}