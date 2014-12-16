<?php

class CodeCheck extends BaseCheck {
	/**
	 * @var CodeCheckVisitor
	 */
	protected $visitor;
	protected $current_php_file;

	function __construct( $checks ) {
		$this->visitor = new CodeCheckVisitor( $checks );
	}

	protected function get_current_filename() {
		return $this->current_php_file->get_filename();
	}

	protected function get_current_line( $line_no ) {
		return trim( $this->get_line( $line_no, $this->current_php_file->get_file_contents() ) );
	}

	protected function add_error( $slug, $description, $level, $file = '', $line = 0 ) {
		if( empty( $line ) ) {
			$line = $this->visitor->get_current_line();
		}

		// TODO: We might want to add a 'scope' value obtained from ScopeVisitor.
		$already_present = array_filter( $this->errors, function( $error ) use( $slug, $description, $level ) {
			return $error['slug'] === $slug && $error['description'] === $description && $error['level'] === $level;
		} );
		// TODO: Move the following logic to BaseCheck.
		// Caveat: For that to work, all checks will need to pass line
		// numbers as keys in BaseCheck's $lines array argument.
		if ( ! empty( $already_present ) ) {
			$indices = array_keys( $already_present );
			$this->errors[ $indices[0] ]['lines'][ $line ] = $this->get_current_line( $line );
		} else {
			$this->errors[] = array(
				'slug'        => $slug,
				'level'       => $level,
				'description' => $description,
				'file'        => empty( $file ) ? $file : $this->get_current_filename(),
				'lines'       => array( $line => $this->get_current_line( $line ) ),
			);
		}
	}

	/**
	 * @see BaseCheck::check()
	 * @param AnalyzedPhpFile[] $files
	 */
	public function check( $files ) {
		foreach ( $files as $php_file ) {
			$this->current_php_file = $php_file;

			$traverser = new PhpParser\NodeTraverser;
			$traverser->addVisitor( $this->visitor );
			$traverser->traverse( $php_file->get_node_tree() );
		}
		$this->current_php_file = null;
		return empty( $this->errors );
	}
}
