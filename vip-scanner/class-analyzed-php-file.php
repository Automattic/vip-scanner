<?php

class AnalyzedPHPFile extends AnalyzedFile {
	protected $filepath = '';
	protected $filecontents = '';
	protected $processed_file_contents = '';
	
	protected $hierarchy_elements = array(
		'namespaces' => array(),
		'classes'    => array(),
		'functions'  => array(),
		'php'		 => array(),
	);
	
	protected $comments_regex = <<<EOT
		(\/\*(?:(?!\*\/)[\s\S])*\*\/)			# match a multiline comment
			|
		(\/\/(?:(?!\n|\r)[\s\S])*)				# match a single line comment
EOT
		;
	
	protected $strings_regex = <<<EOT
		<<<(?<herestart>\S+)((?!\1)[\s\S])*\3;	# match a heredoc
			|
		(([\'"])((?!\6)[\s\S])*\6)				# match a string
EOT
		;
	
	protected $strip_inline_php_regex = '\?>((?!<\?php)[\s\S])*<\?php';
	
	protected $hierarchy_regexes = array(
		'php'		 => array( 'regex' => <<<EOT
			(								# start of bracket 1
				<\?php							# php opening tag
					(?<contents>((?!\?>)[\s\S])*)   # match anything except a php closing tag
				(\?>)?							# match a closing tag
			)								# end of bracket 1
EOT
			),

		'namespaces' => array( 'regex' => <<<EOT
			namespace\s+(?<name>(\\\\?\w+)+);    # Match the name of the namespace
			(?<contents>((?!namespace)[\s\S])*)	 # match the contents of the namespace
EOT
			, ),

		'classes'    => array( 'regex' => <<<EOT
			((?<abstract>abstract)\s+)? # optionally match an abstract class
			class\s+(?<name>\w+)\s+ # match the classname
			(extends\s+(?<parentclass>\w+)\s*)? # optionally match a parentclass 
			(?<contents>
				(						# start of bracket 7
					{                   # match an opening curly bracket
						(?:
							[^{}]++     # one or more non curly brackets
							  |
							(?7)        # recurse to bracket 7
						)*
					}                   # match a closing curly bracket
				)						# end of bracket 7
			)
EOT
		   , ),

		'functions'  => array( 'regex' => <<<EOT
			\s*(									# match function modifiers (visibility, static, abstract)
					\s*(?<visibility>private|protected|public)\s+
						|
					\s*(?<static>static)\s+
						|
					\s*(?<abstract>abstract)\s+
				){0,3}
			\s*function\s+(?<name>[a-zA-Z0-9_]+\s*) # match the function definition & name
			\((?<args>(\s|\w|[$,_='"])+)?\)\s*		# match the function arguments
			(?<contents>
				(?(abstract);|							# match either the semicolon of an abstract function or a closure
					(									# start of bracket 1
						{								# match an opening curly bracket
							(?:
								[^{}]++					# one or more non curly brackets
								  |
								(?8)					# recurse to bracket 1
							)*
						}								# match a closing curly bracket
					)									# end of bracket 1
				)
			)
EOT
			, ),

		'members'    => array( 'regex' => '', ),
	);
	
	protected $check_hierarchy = array(
		'php' => array(
			'namespaces' => array(
				'classes' => array(
					'functions' => array(),
					'members'   => array(),
				),
			),

			'classes' => array(
				'functions' => array(),
				'members'	=> array(),
			),

			'functions' => array(),
			'members'   => array(),
		),
	);
	
	/**
	 * Analyzes this file.
	 */
	protected function analyze_file() {
		// Load the contents of the file
		if ( is_null( $this->filecontents ) || ! $this->filecontents ) {
			$this->filecontents = file_get_contents( $this->filepath );
		}
		
		if ( false === $this->filecontents ) {
			return;
		}
		
		// Strip strings and comments from the file. Preserve line numbers
		$stripped = $this->strip_strings_and_comments( $this->filecontents );
		
		// Do the php check hierarchy
		$this->processed_file_contents = $this->do_check_hierarchy( '', $this->check_hierarchy, $stripped, 0 );
	}

	protected function get_strings_and_comments_regexes() {
		return array(
			$this->comments_regex,
			$this->strings_regex,
			$this->strip_inline_php_regex,
		);
	}

}