<?php

class DiffScanner extends BaseScanner
{
	/**
	 * @param mixed $content Content of the file
	 * @param mixed $checks The Check classes to run
	 */
	function __construct( $diff, $checks ) {

		$diff_lines = explode( "\n", $diff );
		$file_name = NULL;

		// Parse diff format into a nice array with line numbers
		// props Thorsten Ott
		foreach( $diff_lines as $line ) {
			switch( true ) {
				case preg_match( '/^No differences encountered/', $line ):
				case preg_match( '/^$/', $line ):
				case preg_match( '/^(\-\-\-|\+\+\+)/', $line ):
					break;
				case preg_match( '/^@@ [-+]([0-9]+)*,([0-9]+)* [+-]([0-9]+)*,([0-9]+)* @@/', $line, $match ):
					$old_start = $match[1];
					$new_start = $match[3];
					$length = strlen( $new_start ) + 1;
					if ( $length <= 4 )
						$length = 4;
					break;
				case preg_match( '/^ (.*)/', $line, $match ):
					$diff_split[$file_name][$new_start] = $match[1];
					$old_start++;
					$new_start++;
					break;
				case preg_match( '/^\+(.*)/', $line, $match ):
					$diff_split[$file_name][$new_start] = $match[1];
					$new_start++;
					break;
				case preg_match( '/^\-(.*)/', $line, $match ):
					$old_start++;
					break;
				case preg_match( '/^Index: (.+)/', $line, $match ):
					$file_name = $match[1];
					break;
				case preg_match( '/^diff -r/', $line ):
					break;
			}
		}

		$diff_files = array();
		foreach ( $diff_split as $diff_file => $diff_entry ) {
			$i = 0;
			$diff_entry_merged = '';

			foreach ( $diff_entry as $diff_entry_line_number => $diff_entry_line ) {
				$i++;
				while ( $i < $diff_entry_line_number ) {
					$diff_entry_merged .= PHP_EOL; // line number $i
					$i++;
				}
				$diff_entry_merged .= $diff_entry_line . PHP_EOL; // line number $diff_entry_line_number
			}
			$diff_files[ $diff_file ] = $diff_entry_merged;
		}

		parent::__construct( $diff_files, $checks );
	}
}
