<?php

abstract class BaseCheck
{
	protected $check_count = 0;
	protected $errors = array();

	// Returns true for good/okay/acceptable, false for bad/not-okay/unacceptable
	abstract public function check( $files );

	// Returns array of strings explaining any problems found
	public function get_errors() {
		return $this->errors;
	}

	protected function increment_check_count() {
		$this->check_count++;
	}

	protected function add_error( $slug, $description, $level, $file = '', $lines = array() ) {
		$error = array(
			'slug' => $slug,
			'level' => $level,
			'description' => $description
		);

		$error['file'] = '';
		if ( ! empty( $file ) )
			$error['file'] = $file;

		if( ! empty( $lines ) ) {
			$lines = array_map( 'htmlspecialchars', (array) $lines );
			$lines = array_map( 'trim', $lines );
			$error['lines'] = $lines;
		}

		$this->errors[] = $error;
	}

	public function get_results() {
		return array(
			'count' => $this->check_count,
			'errors' => $this->errors
		);
	}

	protected function get_all_files( $files ) {
		$all_files = array();
		foreach( $files as $type => $type_files ) {
			$all_files = array_merge( $all_files, $type_files );
		}
		return $all_files;
	}

	protected function filter_files( $files, $type = '' ) {
		$files = (array) $files;
		if( $type ) {
			if( isset( $files[$type] ) )
				return $files[$type];
			else
				return array();
		}
		return $files;
	}

	protected function merge_files( $files, $type = '' ) {
		$files = (array) $files;
		if( $type ) {
			if( isset( $files[$type] ) )
				return implode( ' ', $files[$type] );
			else
				return '';
		}
		return implode( ' ', $this->get_all_files( $files ) );
	}

	function get_line( $line, $content ) {
		if( ! is_array( $content ) )
			$lines = preg_split( '/((?<!\\\|\r)\n)|((?<!\\\)\r\n)/', $content );
		else
			$lines = $content;

		$line--;

		if( isset( $lines[$line] ) )
			return $lines[$line];

		return null;
	}

	/**
	 * Search through a file for a given pattern
	 *
	 * @param string Regular expression to grep for
	 * @param string Path to the file
	 * @return array Associative array with key as line number and line contents as value
	 */
	protected function grep_file( $pattern, $file ) {
		// Read file lines file into an array
		$lines = file( $file, FILE_IGNORE_NEW_LINES );
		return $this->grep_content( $pattern, $lines );
	}

	/**
	 * Search through contents for a given pattern
	 *
	 * @param string Regular expression to grep for
	 * @param string|array Contents of file as a string or as array split by line
	 * @return array Associative array with key as line number and line contents as value
	 */
	protected function grep_content( $pattern, $content ) {
		if( ! is_array( $content ) )
			$lines = preg_split( '/((?<!\\\|\r)\n)|((?<!\\\)\r\n)/', $content );
		else
			$lines = $content;

		$line_index = 0;
		$grep_lines = array();
		$pattern = trim( $pattern );

		foreach( $lines as $line ) {
			if ( stristr( $line, $pattern ) ) {
				$pattern = str_replace( '"', "'", $pattern );
				$line = str_replace( '"', "'", $line );
				$pattern = ltrim( $pattern );
				$pre = ( FALSE !== ( $pos = strpos( $line, $pattern ) ) ? substr( $line, 0, $pos ) : FALSE );
				$pre = ltrim( htmlspecialchars( $pre ) );

				$line_number = '' . ( $line_index + 1 );
				$grep_lines[$line_number] = $pre . htmlspecialchars( substr( stristr( $line, $pattern ), 0, 75 ) );
			}
			$line_index++;
		}
		return $grep_lines;
	}

	protected function preg_file( $pattern, $file ) {
		// Read file into an array
		$lines = file( $file, FILE_IGNORE_NEW_LINES );
		return $this->preg_content( $pattern, $lines );
	}

	protected function preg_content( $pattern, $content ) {
		if( ! is_array( $content ) )
			$lines = preg_split( '/((?<!\\\|\r)\n)|((?<!\\\)\r\n)/', $content );
		else
			$lines = $content;

		$line_index = 0;
		$preg_lines = array();

		foreach( $lines as $line ) {
			if ( preg_match( $pattern, $line, $matches ) ) {
				$match = $matches[0];
				$line = str_replace( '"', "'", $line );
				$match = ltrim( $match );
				$pre = ( FALSE !== ( $pos = strpos( $line, $match ) ) ? substr( $line, 0, $pos ) : FALSE );
				$pre = ltrim( htmlspecialchars( $pre ) );

				$line_number = '' . ( $line_index + 1 );
				$preg_lines[$line_number] = $pre . htmlspecialchars( substr( stristr( $line, $match ), 0, 75 ) );
			}
			$line_index++;
		}
		return $preg_lines;
	}

	protected function get_filename( $file ) {
		return pathinfo( $file, PATHINFO_BASENAME );
	}

	/**
	 * Scan a file for a given pcre.
	 *
	 * Merged files should not use this function.
	 *
	 * @uses VIP_PregFile::__construct()
	 * @uses VIP_PregFile::get()
	 *
	 * @param string $needle A Perl compatible regular expression.
	 * @param string $haystack A file to search through.
	 * @return array Complete matched lines (string) indexed by the first line number (int).
	 */
	protected function preg_file2( $needle, $haystack ) {
		$scanner = new VIP_PregFile( $needle, $haystack );
		$results = $scanner->get();
		return $results;
	}
}