<?php

class FileMeta extends AnalyzerMeta {
	protected $singular = 'file';
	protected $plural = 'files';
	
	/**
	 * 
	 * @param AnalyzedFile $file The file this meta is for.	
	 * @param array $attributes
	 */
	function __construct( $file, $attributes = array() ) {
		parent::__construct( $file->get_filename(), $attributes );

		// Count the number of lines in this file
		$this->add_stat( 'line_count', substr_count( $file->get_file_contents(), "\n" ) );
	}
	
	function display_header() {
		$header_items = array();

		$header_items[] = sprintf(
			__( '%s<strong class="meta-file-name">%s</strong> <small>(%s lines%s)</small>', 'theme-review' ),
			__( 'File: ', 'theme-check' ),
			esc_html( $this->name() ),
			number_format( $this->get_stat( 'line_count' ) ),
			esc_html( count( $this->child_metas ) > 0 ? ', ' . $this->get_child_summary() : '' )
		);
		
		return implode( ' ', $header_items );
	}
}