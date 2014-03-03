<?php

class DirectoriesCheck extends BaseCheck {
	function check( $files ) {

		$result = true;
		$found = false;
		
		$bad_paths = array( '.git', '.svn', '.sass-cache', '.DS_Store' );

		foreach ( $files as $path => $file ) {
			$this->increment_check_count();
			foreach ( $bad_paths as $bad_path ) {
				if ( strpos( $path, $bad_path ) !== false ) {
					$found = true;
				}
			}
		}

		if ( $found ) {
			$this->add_error(
				'unnecessary-directories',
				'Please remove any extraneous directories like `.git`, `.svn`, `.sass-cache` or `.DS_Store`',
				'required'
			);
			$result = false;
		}
		return $result;
	}
}
