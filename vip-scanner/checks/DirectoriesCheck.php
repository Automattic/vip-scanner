<?php

class DirectoriesCheck extends BaseCheck {
	function check( $files ) {

		$result = true;
		$found = false;

		foreach ( $files as $path => $file ) {
			$this->increment_check_count();
			if ( strpos( $path, '.git' ) !== false || strpos( $path, '.svn' ) !== false )
				$found = true;
		}

		if ( $found ) {
			$this->add_error(
				'unnecessary-directories',
				'Please remove any extraneous directories like `.git` or `.svn`',
				'required'
			);
			$result = false;
		}
		return $result;
	}
}