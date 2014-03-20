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
	
	function identifier() {
		return $this->file->get_filepath();
	}
	
	function display_header() {
		$header_items = array();

		$header_items[] = __( 'File:', 'theme-check' );
		$header_items[] = $this->stylize_text( esc_html( $this->name() ), array( 'bold' => true, 'classes' => array( 'renderer-file-name' ) ) );
		$header_items[] = $this->stylize_text( 
			sprintf( '(%s lines%s)', 
				number_format( $this->get_stat( 'line_count' ) ),
				esc_html( count( $this->children ) > 0 ? ', ' . $this->get_child_summary() : '' )
			),
			array( 'small' => true )
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
	
	protected function skip_stats() {
		return array_merge( array( 'line_count' ), parent::skip_stats() );
	}
}