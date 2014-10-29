<?php

/**
 * Ensure that vip-init.php is required in the theme
 */
class VIPInitCheck extends BaseCheck {
	public function check( $files ) {
		// If the scan is not a theme scan, skip (for example, diff scan)
		$scanner = $this->get_scanner();

		if ( ! $scanner instanceof ThemeScanner )
			return true;
		
		$path 	= $this->get_path(); // So we can ensure we only look at the main functions.php
		$files 	= $this->filter_files( $files, 'php' );

		$vip_init_found = false;

		foreach( $files as $path => $content ) {
			if ( ! $this->file_is_main_functions( $path ) )
				continue;

			$vip_init_found = $this->vip_init_is_included( $path );

			break;
		}

		if ( ! $vip_init_found )
			$this->add_error( 'vip-init', 'VIP Init', BaseScanner::LEVEL_BLOCKER, 'functions.php', array( 'vip-init.php was not required' ) );

		return true;
	}

	public function file_is_main_functions( $file ) {
		$filename = $this->get_filename( $file );

		if ( 'functions.php' !== $filename )
			return false;

		// Strip off the main path, so we can tell if $file is in the root - otherwise we'd get false positives for other files
		// named functions.php in other directories
		$path = str_replace( $this->get_path(), '', $file );

		if ( '/' !== dirname( $path ) )
			return false;

		return true;
	}

	public function vip_init_is_included( $file ) {
		$matches = $this->preg_file( '/\brequire(?:_once)?\s*?\(\s*?WP_CONTENT_DIR\s*?\.\s*?(\'|\")\/themes\/vip\/plugins\/vip-init\.php\1/', $file );

		return ( count( $matches ) ) ? true : false;
	}
}
