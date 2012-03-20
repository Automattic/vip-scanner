<?php

class DirectoryScanner extends BaseScanner
{
	function __construct( $path, $checks ) {
		// Scan directory and read in contents
		$files = $this->get_file_contents( $path );
		parent::__construct( $files, $checks );
	}

	function get_files_in_directory( $directory = '.' ) {
		$files = array();

		if( is_dir( $directory ) ) {

			// Skip source control
			if ( in_array( basename( $directory ), array( '.svn', '.git' ) ) )
				return array();

			if( ! is_readable( $directory ) ) {
				$this->add_error( 'directory-permission', sprintf( 'Permission denied for directory: %s', $directory ), 'blocker', $directory );
				return false;
			}

			if( $handle = opendir( $directory ) ) {
				while( false !== ( $file = readdir( $handle ) ) ) {
					// Loop through the files, skipping . and .., and recursing, if necessary
					if ( ! in_array( $file, array( '.', '..' ) ) ) {
						$filepath = $directory . '/' . $file;
						if ( is_dir( $filepath ) )
							$files = array_merge( $files, $this->get_files_in_directory( $filepath ) );
						else
							array_push( $files, $filepath );
					}
				}
				closedir( $handle );
			}
		} else {
			// function was called with an invalid non-directory argument
			$this->add_error( 'invalid-directory', sprintf( 'Directory doesn\'t exist: %s', $directory ), 'blocker', $directory );
			return false;
		}
		return $files;
	}

	function get_file_contents( $path ) {
		$file_contents = array();
		$files = $this->get_files_in_directory( $path );
		if ( $files ) {
			foreach( $files as $file ) {
				if( ! is_readable( $file ) ) {
					$this->add_error( 'file-permission', sprintf( 'Permission denied for file: %s', $file ), 'blocker', $file );
					continue;
			}

				/* // The following helps prevent false positives, e.g. comments, but messes up line numbers
				if ( 'php' == $this->get_file_type( $file ) ) {
					// strip comments and unnecessary whitespace
					$file_contents[$file] = php_strip_whitespace( $file );
				} else {
					$file_contents[$file] = file_get_contents( $file );
				}
				*/
				// The following causes false positives, e.g. code in comments gets picked up
				$file_contents[$file] = file_get_contents( $file );
			}
		}
		return $file_contents;
	}
}