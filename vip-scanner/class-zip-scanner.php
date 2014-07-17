<?php

class ZipScanner extends BaseScanner
{
	protected $path;

	function __construct( $path, $review ) {
		$this->set_path( $path );

		// Scan zip and read in contents
		$files = $this->get_zip_contents( $this->get_path() );
		parent::__construct( $files, $review );
	}

	public function get_zip_contents( $path ) {
		$files = array();
		$zip = new ZipArchive();

		$zip_ret = $zip->open( $path );

		if ( false === $zip_ret) {
			$this->add_error( 'invalid-zip', sprintf( 'Can\'t open: %s', $path ), 'blocker', $path );
			return;
		}

		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$file_name = $zip->getNameIndex( $i );
			$file_info = pathinfo( $file_name );

 	 	 	// Skip directories - seemingly the only way, since php's zip
 	 	 	// wrapper doesn't support stat() calls.
			if ( substr( $file_name, -1 ) == '/' ) {
				continue;
			}

			$files[$file_name] = file_get_contents("zip://{$path}#{$file_name}", 'r' );
		}

		return $files;
	}

	public function set_path( $path = '' ) {
		$this->path = $path;

		return $this;
	}

	public function get_path() {
		return $this->path;
	}
}
