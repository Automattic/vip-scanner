<?php

class FileRenderer extends AnalyzerRenderer {
	protected $singular = 'file';
	protected $plural = 'files';
	protected $file = null;
	
	/**
	 * 
	 * @param AnalyzedFile $file The file this meta is for.	
	 * @param array $attributes
	 */
	function __construct( $file, $attributes = array() ) {
		parent::__construct( $file->get_filename(), $attributes );
		$this->set_file( $file );
	}
	
	function display_header() {
		$header_items = array();

		$header_items[] = sprintf(
			__( '%s<strong class="renderer-file-name">%s</strong> <small>(%s lines%s)</small>', 'theme-review' ),
			__( 'File: ', 'theme-check' ),
			esc_html( $this->name() ),
			number_format( $this->get_stat( 'line_count' ) ),
			esc_html( count( $this->children ) > 0 ? ', ' . $this->get_child_summary() : '' )
		);
		
		return implode( ' ', $header_items );
	}
	
	function set_file( $file ) {
		$this->file = $file;
		
		// Count the number of lines in this file
		$this->add_stat( 'line_count', substr_count( $this->file->get_file_contents(), "\n" ) );
	}
	
	function get_file() {
		return $this->file;
	}
}