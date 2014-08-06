<?php

class YUICompressorCheck extends BaseCheck {
	const COMMAND = 'java';

	/**
	 * This constant can enable/disable the CSS YUI Compressor.
	 * Change to true if you want to use it with both JS and CSS
	 */
	const USE_CSS = false;

	protected $yui_file_name = "yuicompressor-2.4.8.jar";

	public function check( $files ) {
		// If the scan is not a theme scan, skip (for example, diff scan)
		$scanner = $this->get_scanner();

		if ( ! $scanner instanceof ThemeScanner )
			return $scanner;
		
		// First check if Java is installed on the system 
		if ( ! self::isJavaInstalled() ) {
			$this->add_error( 'yuicompressor', 'YUI Compressor', BaseScanner::LEVEL_WARNING, null, array( 'Java is not present on this system - as such, the YUI Compressor wasn\'t performed on this theme.') );
			return true;
		}

		// Then, check if the yuicompressor java binary is present
		if( ! file_exists( VIP_SCANNER_BIN_DIR . '/' . $this->yui_file_name ) ) {
			$this->add_error( 'yuicompressor', 'YUI Compressor', BaseScanner::LEVEL_WARNING, null, array( 'The filename ' . $this->yui_file_name . ' wasn\'t found on ' . VIP_SCANNER_BIN_DIR . ' directory.' ) );
			return true;
		}
		
		// If the USE_CSS const is true, get all the JS and CSS files. Otherwise, get only the js files.
		if( self::USE_CSS ) {
			$files = array_merge_recursive( $this->filter_files( $files, 'js' ), $this->filter_files( $files, 'css' )  );
		} else {
			$files = $this->filter_files( $files, 'js' );
		}
		
		$errors = array();
		foreach ( $files as $file_path => $file_content ) {

			$command = escapeshellcmd( self::COMMAND . ' -jar ' . VIP_SCANNER_BIN_DIR . '/' . $this->yui_file_name . ' ' . $file_path );
			// Force the STDERR to output on STDOUT by adding the 2>&1
			$result = shell_exec( $command . " 2>&1");
			
			if ( ! $result )
				return true;

			$results = explode( "\n", $result );
			foreach( $results as $key => $result ) {
				// Found an error, process it
				if ( false !== strpos( $result, '[ERROR]' ) ) {
					// The error description is always on the next line
					$error_desc = trim($results[ $key + 1 ]);

					$errors[ $file_path ][] = $error_desc;
				}
			}

			// Check if have errors and add them to the BaseCheck
			if( ! empty( $errors[ $file_path ] ) ) {
				$this->add_error( 'yuicompressor' , 
					'YUI Compressor results', 
					BaseScanner::LEVEL_BLOCKER, 
					$file_path, 
					array_merge( array("There was YUI Compressor errors! Check below:"), $errors[ $file_path ] ) 
				);
			}

		}

		// No errors? Everything is fine then!
		if( empty( $errors ) ) {
			return true;
		}
		
		return false;
	}

	public static function isJavaInstalled() {
		$command = escapeshellarg( self::COMMAND );

		$result = shell_exec( "which $command" );

		return ( empty( $result ) ? false : true );
	}
}
