<?php

abstract class AnalyzedFile {
	protected $filepath = '';
	protected $filecontents = '';
	protected $processed_file_contents = '';
	protected $hierarchy_elements = array();
	protected $check_hierarchy = array();
	
	public function __construct( $filepath, $filecontents = null ) {
		$this->filepath = $filepath;
		$this->filecontents = $filecontents;
		$this->analyze_file();
	}

	/**
	 * Gets the path of the analyzed file.
	 * @return string
	 */
	public function get_filepath() {
		return $this->filepath;
	}
	
	/**
	 * Gets the name of the analyzed file.
	 * @return string
	 */
	public function get_filename() {
		return basename( $this->filepath );
	}
	
	/**
	 * Gets the stored contents of the analyzed file.
	 * @return string
	 */
	public function get_file_contents() {
		return $this->filecontents;
	}
	
	/**
	 * Gets the contents of the file after processing, with every recognized 
	 * element stripped out.
	 * @return string
	 */
	public function get_processed_file_contents() {
		return $this->processed_file_contents;
	}
	
	/**
	 * Gets elements of PHP code from a file by their path. This includes things such as namespaces,
	 * classes, and functions.
	 * 
	 * The path is the names of the containers of an element. For example, if a 
	 * function "myfunc" exists within a class "myclass" its path would be "myclass".
	 * 
	 * Specify the $type to get all elements of that type. If $path is null you 
	 * will receive all functions in the file, including namespaced functions and
	 * class members.
	 * 
	 * If $path is an empty string it will retrieve only elements of $type within
	 * the global namespace.
	 * 
	 * @param string $type The element type. "namespaces", "classes", or "functions"
	 * @param string $path [optional] The path to the elements. Empty string for global.
	 * @return array
	 */
	public function get_code_elements( $type, $path = null ) {
		if ( ! array_key_exists( $type, $this->hierarchy_elements ) ) {
			return array();
		}
		
		if ( is_null( $path ) ) {
			return $this->hierarchy_elements[$type];
		} else {
			if ( array_key_exists( $path, $this->hierarchy_elements[$type] ) ) {
				return $this->hierarchy_elements[$type][$path];
			} else {
				return array();
			}
		}
	}
	
	/**
	 * Retrieves the check hierarchy
	 * @return array
	 */
	public function get_check_hierarchy() {
		return $this->check_hierarchy;
	}

	/**
	 * Analyzes this file.
	 */
	protected abstract function analyze_file();
	
	/**
	 * Runs through a check hierarchy recursively. Assumes that the incoming
	 * contents have already been stripped of strings and comments.
	 * 
	 * Typically uses the hierarchy AnalyzedFile::$check_hierarchy
	 * 
	 * The general algorithm is this:
	 * 1. Iterate over the first level of the passed in hierarchy.
	 *	1. Apply the regex for this level. Every regex (if there's a match)
	 *		should have a named capture group "contents". This would be the contents
	 *		of a code block for example.
	 *	2. Record all of the names of the matches. For example if we match a "class"
	 *		we record all of the class names.
	 *	3. Recurse to the next level of the hierarchy, passing on the contents from 1.1. 
	 *		If the last level was "class" then this may be functions.
	 * 2. Remove everything that we've analyzed from the input string. This stops
	 *		items within code block (such as functions in classes) from being matched
	 *		twice. This step preserves line numbers.
	 * 
	 * @param string $top_level The name of the container of this level of hierarchy.
	 * @param array $hierarchy The hierarchy of checks to run.
	 * @param string $contents The contents to run the checks on
	 * @param number $line_offset The offset to be added to line numbers recorded by matches
	 */
	protected function do_check_hierarchy( $top_level, $hierarchy, $contents, $line_offset ) {
		foreach ( $hierarchy as $level => $children ) {
			// Get the regex for this element
			$regex = $this->hierarchy_regexes[$level]['regex'];

			if ( empty( $regex ) ) continue;

			// Get all of the matches
			preg_match_all( "/$regex/ix", $contents, $matches, PREG_OFFSET_CAPTURE );

			// Go over each match
			foreach ( $matches['contents'] as $index => $match ) {
				$this_top_level = '';
				$this_line_number = $this->compute_line_number( $contents, $match[1], $line_offset );

				// Save this match (only saved named capture groups)
				if ( isset( $this->hierarchy_elements[$level] ) ) {

					if ( empty( $top_level ) ) {
						if ( ! empty( $matches['name'] ) ) {
							$this_top_level = $matches['name'][$index][0];
						}
					} else {
						$this_top_level = "$top_level::{$matches['name'][$index][0]}";
					}

					$this_match = array( 'line' => $this_line_number );

					// Save this matches properties
					foreach ( $matches as $match_type => $individual_matches ) {
						if ( ! is_numeric( $match_type ) ) {
							$this_match[$match_type] = $individual_matches[$index][0];
						}
					}
					
					if ( ! array_key_exists( $top_level, $this->hierarchy_elements[$level] ) ) {
						$this->hierarchy_elements[$level][$top_level] = array();
					}

					$this->hierarchy_elements[$level][$top_level][$this_top_level] = $this_match;
				}

				// Parse any children in the hierarchy. Subtract 1 from line number because
				// The first line of the next hierarchy is on the current line
				$this->do_check_hierarchy( $this_top_level, $children, $match[0], $this_line_number - 1 );
			}

			// Replace substring (to prevent double matches) and calculate line offsets
			foreach ( $matches[0] as $index => $match ) {
				$this->replace_match_with_whitespace( $contents, $matches[0][$index][0] );
			}
		}
		
		return $contents;
	}
	
	/**
	 * Counts the line number of the char at index $char in the $string.
	 * 
	 * @param string $string
	 * @param number $char
	 * @param number $line_offset
	 */
	public function compute_line_number( $string, $char, $line_offset = 0 ) {
		preg_match_all( "/\r\n|\r|\n/", substr( $string, 0, $char ), $matches );
		
		return count( $matches[0] ) + 1 + $line_offset;
	}

	/**
	 * Replaces $match in $contents with whitespace, preserving line numbers.
	 * Modifies the $contents variable.
	 * 
	 * @param string $contents
	 * @param string $match
	 */
	public function replace_match_with_whitespace( &$contents, $match ) {
		// Count the number of lines we're about to remove
		preg_match_all( "/\r\n|\r|\n/", $match, $line_end_matches );
		$mod_line_count = count( $line_end_matches[0] );

		// Remove the contents of these lines while maintaining the overall line count
		$pos = strpos( $contents, $match );
		$contents = substr_replace( $contents, str_repeat( "\n", $mod_line_count ) , $pos, strlen( $match ) );
	}
	
	/**
	 * Strips all strings and comments from $contents. Attempts to preserve line numbers.
	 * 
	 * @param string $contents The initial contents
	 * @return string $contents with strings and comments stripped out
	 */
	public function strip_strings_and_comments( $contents ) {
		$regexes = array(
			$this->comments_regex,
			$this->strings_regex,
			$this->strip_inline_php_regex,
		);
		
		foreach ( $regexes as $regex ) {
			preg_match_all( "/{$regex}/ix", $contents, $matches );

			foreach ( $matches[0] as $match ) {
				$this->replace_match_with_whitespace( $contents, $match );
			}
		}

		return $contents;
	}
}