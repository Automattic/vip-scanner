<?php

class FileElement extends BaseElement {
	protected $singular = 'file';
	protected $plural = 'files';
	protected $file = null;

	/**
	 *
	 * @param AnalyzedFile $file The file this meta is for.	
	 * @param array $attributes
	 */
	function __construct( $file ) {
		$this->name = $file->get_filename();
		$this->set_file( $file );
	}

	function identifier() {
		return $this->get_file()->get_filepath();
	}

	function get_header() {
		$header_items = array();

		$header_items[] = __( 'File:', 'theme-check' );
		$header_items[] = array( 'content' => esc_html( $this->name() ), 'style' => array( 'bold' => true, 'classes' => array( 'renderer-file-name' ) ) );

		return $header_items;
	}

	/**
	 * Gets a textual summary of all of the children of this item
	 * @return string
	 */
	function get_child_summary() {
		$lines = array(
			'line' => array(
				'count'  => $this->get_stat( 'line_count' ),
				'plural' => 'lines',
			),
		);
		return array_merge( $lines, parent::get_child_summary() );
	}

	function set_file( $file ) {
		$this->file = $file;
		
		// Count the number of lines in this file
		$this->add_stat( 'line_count', substr_count( $this->file->get_file_contents(), "\n" ) );
	}

	function get_file() {
		return $this->file;
	}

	/**
	 * Returns an array of attribute names that should not be displayed in the ui.
	 * @return array
	 */
	public function skip_stats() {
		return array_merge( array( 'line_count' ), parent::skip_stats() );
	}
}