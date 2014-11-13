<?php

class ContentScanner extends BaseScanner
{
	/**
	 * @param mixed $content Content of the file
	 * @param mixed $content_type File extension
	 * @param mixed $checks The Check classes to run
	 */
	function __construct( $content, $content_type, $checks ) {
		// Pass in the content as a fake file
		parent::__construct( array( sprintf( 'file.%s', $content_type ) => $content ), $checks ); // pass it in as a fake file
	}
}
