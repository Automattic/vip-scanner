<?php

class AnalyzedPHPFile extends AnalyzedFile {
	protected $filepath = '';
	protected $filecontents = '';
	protected $processed_file_contents = '';
	/*
	 * @var PhpParser\Parser\Node[]
	 */
	protected $node_tree;

	/**
	 * Return the abstract syntax tree for this PHP file
	 *
	 * @return PhpParser\Parser\Node[]
	 */
	public function get_node_tree() {
		return $this->node_tree;
	}

	/**
	 * Analyzes this file.
	 *
	 * @throws PhpParser\Error if the file cannot be parsed.
	 */
	protected function analyze_file() {
		// Load the contents of the file
		if ( is_null( $this->filecontents ) || ! $this->filecontents ) {
			$this->filecontents = file_get_contents( $this->filepath );
		}
		
		if ( false === $this->filecontents ) {
			return;
		}

		// Parse the tokens
		$parser = new PhpParser\Parser( new PhpParser\Lexer );
		$this->node_tree = $parser->parse( $this->filecontents );

		// Pre-process the parsed elements for further usage
		$traverser = new PhpParser\NodeTraverser;
		$traverser->addVisitor( new ScopeVisitor );
		$traverser->addVisitor( new InAssignmentVisitor );
		$this->node_tree = $traverser->traverse( $this->node_tree );
	}

	protected function get_strings_and_comments_regexes() {
		return array();
	}
}