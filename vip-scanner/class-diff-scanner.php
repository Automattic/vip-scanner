<?php

class DiffScanner extends BaseScanner
{
	/**
	 * @param mixed $content Content of the file
	 * @param mixed $checks The Check classes to run
	 */
	function __construct( $diff, $checks ) {

		$diffs = preg_split( "/^(Index: .+)$/m", $diff, -1, PREG_SPLIT_DELIM_CAPTURE );

		foreach( $diffs as $diff ) {
			// TODO
		}

		parent::__construct( array( sprintf( 'file.%s', $content_type ) => $content ), $checks );
	}
}