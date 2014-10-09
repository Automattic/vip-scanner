<?php

class TokenParser {
	const PATH_SEPERATOR      = '::';
	const ANON_FUNCTION_LABEL = '{closure}';

	const CLASS_OPEN				   = 0;
	const CLASS_PARSE_PARENT		   = 1;
	const CLASS_DEFINITION			   = 2;
	const CLASS_BEGIN				   = 3;
	const CLASS_BODY				   = 4;
	const CLASS_MEMBER_OPEN			   = 5;
	const CLASS_MEMBER_BODY			   = 6;
	const CLASS_MEMBER_FUNC_DEFINITION = 7;
	const CLASS_MEMBER_DEFINITION	   = 8;
	const CLASS_MEMBER_FUNC_BODY	   = 9;
	const NAMESPACE_OPEN			   = 10;
	const NAMESPACE_DEFINITION		   = 11;
	const POTENTIAL_FUNCTION_CALL	   = 12;
	const FUNCTION_CALL_ARGS		   = 13;
	const STRING_VARIABLE = 14;

	private $path		  = array();
	private $token_count  = 0;
	private $tokens		  = array();
	private $elements     = array();
	private $index		  = 0;
	private $in_namespace = false;
	private $line		  = 1;

	private $function_indicators = array(
		T_STRING,
		T_EVAL,
		T_EMPTY,
		T_EXIT,
		T_HALT_COMPILER,
		T_INCLUDE,
		T_INCLUDE_ONCE,
		T_REQUIRE,
		T_REQUIRE_ONCE,
		T_ISSET,
		T_LIST,
		T_PRINT,
		T_UNSET,
	);

	function parse_contents( $contents ) {
		$this->tokens = token_get_all( $contents );
		$this->token_count = count( $this->tokens );

		$this->elements = array();
		$levels = array( '(' => 0, '{' => 0, 'path' => array() );

		for ( ; $this->index < $this->token_count; ++$this->index ) {
			$result = $this->parse_next( $levels );
			if ( ! is_null( $result ) ) {
				$this->elements[] = $result;
			}
		}

		return $this->elements;
	}

	function reset() {
		$this->path         = array();
		$this->in_namespace = false;
		$this->index        = 0;
		$this->token_count  = 0;
		$this->token_count  = array();
		$this->elements     = array();
		$this->line			= 1;
	}

	function closes_block( $closure, &$blocks, $true_on_empty = false ) {
		switch ( $closure ) {
			case '(':
			case '{':
			case '[':
				// Add this block to the path as we enter it
				if ( ! isset( $blocks[$closure] ) ) {
					$blocks[$closure] = 0;
				}

				++$blocks[$closure];
				$blocks['path'][] = $closure;
				return false;

			case ')':
				$matching = '(';
				break;

			case '}':
				$matching = '{';
				break;

			case ']':
				$matching = '[';
				break;

			case ';':
				$matching = '{';

				if ( empty( $blocks['path'] ) ) {
					return true;
				}

				// Remove this block from the path (leaving the last curly brace)
				$closes = false;
				while ( count( $blocks['path'] ) && '{' !== end( $blocks['path'] ) ) {
					$c = array_pop( $blocks['path'] );
					--$blocks[$c];

					$closes |= $blocks[$c] < 0;
				}

				return $closes || ( $true_on_empty && empty( $blocks['path'] ) );

			default:
				return false;
		}

		if ( empty( $blocks['path'] ) ) {
			return true;
		}

		// Remove this block from the path
		$closes = false;
		while ( count( $blocks['path'] ) ) {
			// ( and [ cannot break a curly brace.
			if ( end( $blocks['path'] ) === '{' && $matching != '{' ) {
				break;
			}

			if ( $this->is_curly_braced_variable( $matching ) ) {
				return false;
			}

			$c = array_pop( $blocks['path'] );
			--$blocks[$c];

			$closes |= $blocks[$c] < 0;
			if ( $c === $matching ) {
				break;
			}
		}

		return $closes || ( $true_on_empty && empty( $blocks['path'] ) );
	}

	function is_curly_braced_variable( $matching ) {
		if ( '{' !== $matching ) {
			return false;
		}
		$index = $this->index;
		//if there's a variable before current closing curly braced token
		if ( T_VARIABLE === $this->tokens[$index - 1][0] ) {
			//if it's a simple curly braced variable inside a string
			if ( ( true === isset( $this->tokens[$index - 3][1] )
			     && T_ENCAPSED_AND_WHITESPACE === $this->tokens[$index - 3][1] )
				|| '"' === $this->tokens[$this->index - 3]
			) {
				return true;
			}
			/*
			if ( T_OBJECT_OPERATOR === $this->tokens[$index - 2][0] ){
				if ( T_VARIABLE === $this->tokens[$index - 3][0]
			          && ( ( true === isset( $this->tokens[$index - 3][1] )
			                 && T_ENCAPSED_AND_WHITESPACE === $this->tokens[$index - 4][1] )
			               || '"' === $this->tokens[$this->index - 4] )
			) {
				return true;
			}
			}/**/
		}
		return false;
	}

	function get_current_path_str() {
		return implode( self::PATH_SEPERATOR, $this->path );
	}

	function add_to_path( $breadcrumb ) {
		$this->path[] = $breadcrumb;
	}

	function get_name_with_path( $name ) {
		if ( empty( $this->path ) ) {
			return $name;
		} else {
			return implode( self::PATH_SEPERATOR, $this->path ) . self::PATH_SEPERATOR . $name;
		}
	}

	function path_up() {
		return array_pop( $this->path );
	}

	function get_token( &$token, &$token_contents ) {
		if ( is_array( $this->tokens[$this->index] ) ) {
			$token = $this->tokens[$this->index][0];
			$token_contents = $this->tokens[$this->index][1];
		} else {
			$token = $token_contents = $this->tokens[$this->index];
		}

		return $token;
	}

	function parse_next( &$levels, $break_on = '' ) {
		$properties = array();

		for ( ; $this->index < $this->token_count; ++$this->index ) {
			$this->get_token( $token, $token_contents );

			// Checks for an unexpected closing block
			if ( $this->closes_block( $token, $levels, true ) ) {
					return;
			}

			switch ( $token ) {
				case T_STATIC:
					$properties['static'] = 'static';
					break;

				case T_PUBLIC:
				case T_PROTECTED:
				case T_PRIVATE:
					$properties['visibility'] = strtolower( substr( token_name( $token ), 2 ) );
					break;

				case T_ABSTRACT:
					$properties['abstract'] = 'abstract';
					break;

				case T_NAMESPACE:
					return $this->parse_namespace( $properties );

				case T_CLASS:
					return $this->parse_class( $properties );

				case T_FUNCTION:
					return $this->parse_function( $properties );

				case T_VAR:
				case T_VARIABLE:
				case T_CONST:
					return $this->parse_variable( $properties );

				case T_STRING:

				case T_EVAL:
				case T_EMPTY:
				case T_EXIT:
				case T_HALT_COMPILER:
				case T_INCLUDE:
				case T_INCLUDE_ONCE:
				case T_REQUIRE:
				case T_REQUIRE_ONCE:
				case T_ISSET:
				case T_LIST:
				case T_PRINT:
				case T_UNSET:
					$result = $this->parse_function_call( $properties );

					if ( is_null( $result ) ) {
						$result = $this->parse_variable( $properties );
					}

					return $result;

				case $break_on:
					return;

				case T_DOC_COMMENT:
					$properties['documentation'] = $this->clean_doc_string( $token_contents );
					// lack of break on purpose

				default:
					$this->parse_contents_line_breaks( $token_contents );
					break;
			}
		}
	}

	function parse_namespace( $properties = array() ) {
		$properties = array_merge(
			array(
				'name' => '',
				'type' => 'namespace',
			),
			$properties
		);

		$state = self::NAMESPACE_OPEN;
		for ( ; $this->index < $this->token_count; ++$this->index ) {
			$this->get_token( $token, $token_contents );

			$this->parse_contents_line_breaks( $token_contents );

			if ( T_WHITESPACE === $token ) {
				continue;
			}

			switch ( $state ) {
				case self::NAMESPACE_OPEN:
					if ( T_NAMESPACE !== $token ) {
						continue;
					}

					$state = self::NAMESPACE_DEFINITION;
					break;

				case self::NAMESPACE_DEFINITION:
					if ( ';' === $token ) {
						break 2;
					} elseif ( T_NS_SEPARATOR === $token ) {
						if ( empty( $properties['name'] ) ) {
							// This is not a namespace declaration, but the use of
							// a namespace member.
							$levels = array( 'path' => array() );
							++$this->index;
							$element = $this->parse_next( $levels, ';' );

							if ( ! is_null( $element ) ) {
								$element['name'] = 'namespace' . $token_contents . $element['name'];
							}

							return $element;
						}
					}

					$properties['name'] .= $token_contents;
					break;
			}
		}

		if ( false !== $this->in_namespace ) {
			while ( $this->in_namespace !== $this->path_up() );
		}

		$properties['path'] = $this->get_current_path_str();

		$this->in_namespace = $properties['name'];
		$properties['name'] = $this->get_name_with_path( $properties['name'] );
		$this->add_to_path( $this->in_namespace );
		return $properties;
	}

	function parse_class( $properties = array() ) {
		$properties = array_merge(
			array(
				'name' => '',
				'type' => 'class',
				'abstract' => '',
				'parentclass' => '',
				'children' => array(),
				'path' => $this->get_current_path_str(),
			),
			$properties
		);

		$state  = self::CLASS_BEGIN;
		$levels = array( '(' => 0, '{' => 0, 'path' => array() );

		for ( ; $this->index < $this->token_count; ++$this->index ) {
			$this->get_token( $token, $token_contents );

			$this->parse_contents_line_breaks( $token_contents );
			if ( T_WHITESPACE === $token ) {
				continue;
			}

			// Checks for an unexpected closing block
			if ( $this->closes_block( $token, $levels ) ) {
				if ( $state !== self::CLASS_BEGIN && $state !== self::CLASS_OPEN ) {
					$this->path_up();
				}

				return $properties;
			}

			switch ( $state ) {
				case self::CLASS_BEGIN:
					if ( T_ABSTRACT === $token ) {
						$properties['abstract'] = 'abstract';
						continue;
					}

					if ( T_CLASS !== $token ) {
						continue;
					}

					$properties['line'] = $this->line;
					$state = self::CLASS_OPEN;
					break;

				case self::CLASS_OPEN:
					if ( T_STRING !== $token ) {
						continue;
					}

					$properties['name'] = $this->get_name_with_path( $token_contents );
					$this->add_to_path( $token_contents );
					$state = self::CLASS_DEFINITION;
					break;

				case self::CLASS_DEFINITION:
					switch ( $token ) {
						case T_EXTENDS:
							$state = self::CLASS_PARSE_PARENT;
							break;
						case '{':
							$state = self::CLASS_BODY;
							break;
					}
					break;

				case self::CLASS_PARSE_PARENT:
					if ( T_STRING !== $token ) {
						continue;
					}

					$properties['parentclass'] = $token_contents;
					$state = self::CLASS_DEFINITION;
					break;

				case self::CLASS_BODY:
					if ( '}' !== $token ) {
						$member = $this->parse_class_member( $levels );
						if ( ! is_null( $member ) ) {
							$properties['children'][] = $member;
						}

						continue;
					}

					$this->path_up();
					return $properties;
			}
		}

		return $properties;
	}

	function parse_class_member( &$levels ) {
		$properties = array(
			'type' => false,
			'name' => '',
			'contents' => '',
		);

		for ( ; $this->index < $this->token_count; ++$this->index ) {
			$this->get_token( $token, $token_contents );

			if ( T_WHITESPACE === $this->tokens[$this->index][0] ) {
				$this->parse_contents_line_breaks( $token_contents );
				continue;
			}

			if ( $this->closes_block( $token, $levels ) ) {
				return;
			}

			switch ( $token ) {
				case T_STATIC:
					$properties['static'] = 'static';
					break;

				case T_PUBLIC:
				case T_PROTECTED:
				case T_PRIVATE:
					$properties['visibility'] = strtolower( substr( token_name( $token ), 2 ) );
					break;

				case T_ABSTRACT:
					$properties['abstract'] = 'abstract';
					break;

				case T_FUNCTION:
					return $this->parse_function( $properties );

				case T_CONST:
				case T_VARIABLE:
				case T_VAR:
					return $this->parse_variable( $properties );

				case T_DOC_COMMENT:
					$properties['documentation'] = $this->clean_doc_string( $token_contents );
					// lack of break on purpose

				default:
					$this->parse_contents_line_breaks( $token_contents );
					break;
			}
		}

		return;
	}

	function parse_function( $properties = array() ) {
		$properties = array_merge(
			array(
				'type' => false,
				'name' => '',
				'chlidren' => array(),
				'visibility' => 'public',
				'abstract' => '',
				'args' => '',
				'path' => $this->get_current_path_str(),
			),
			$properties
		);

		$levels = array( '(' => 0, '{' => 0, 'path' => array() );
		$state = self::CLASS_MEMBER_OPEN;

		for ( ; $this->index < $this->token_count; ++$this->index ) {
			$this->get_token( $token, $token_contents );

			$this->parse_contents_line_breaks( $token_contents );

			if ( T_WHITESPACE === $token ) {
				continue;
			}

			// Checks for an unexpected closing block
			if ( $this->closes_block( $token, $levels ) ) {
				if ( $state !== self::CLASS_MEMBER_DEFINITION && $state !== self::CLASS_MEMBER_OPEN ) {
					$this->path_up();
				}

				return $properties;
			}

			switch ( $state ) {
				case self::CLASS_MEMBER_OPEN:
					switch ( $token ) {
						case T_PUBLIC:
						case T_PROTECTED:
						case T_PRIVATE:
							$properties['visibility'] = strtolower( substr( token_name( $token ), 2 ) );
							break;

						case T_ABSTRACT:
							$properties['abstract'] = 'abstract';
							break;

						case T_FUNCTION:
							$state = self::CLASS_MEMBER_DEFINITION;
							$properties['type'] = 'function';
							$properties['line'] = $this->line;
							break;
					}

					break;
				case self::CLASS_MEMBER_DEFINITION:
					if ( $token !== T_STRING && $token !== '(' ) {
						continue;
					}

					if ( $token === '(' ) {
						// This is an anonymous function
						$name = self::ANON_FUNCTION_LABEL;
					} else {
						$name = $token_contents;
					}

					$properties['name'] = $this->get_name_with_path( $name );
					$this->add_to_path( $name );
					$state = self::CLASS_MEMBER_FUNC_DEFINITION;
					break;

				case self::CLASS_MEMBER_FUNC_DEFINITION:
					switch ( $token ) {
						case '(':
							break;
						case ')':
							$state = self::CLASS_MEMBER_FUNC_BODY;
							break;
						default:
							$properties['args'] .= $token_contents;
					}

					break;

				case self::CLASS_MEMBER_FUNC_BODY:
					if ( ! empty( $properties['abstract'] ) && $token === ';' ) {
						break 2;
					} elseif ( $token !== '}' ) {
						if ( $token === '{' ) {
							// Don't append the opening bracket to the contents
							if ( $levels['{'] === 1 ) {
								break;
							}
						}

						// If this token is a closure, skip parsing
						if ( in_array( $token, array( '(', '{', '[', ']', '}', ')' ) ) ) {
							break;
						}

						$result = $this->parse_next( $levels );
						if ( ! is_null( $result ) ) {
							$properties['children'][] = $result;
						}
					}

					// Return if we've closed every closure.
					if ( $levels['{'] === 0 ) {
						break 2;
					}

					break;
			}
		}

		$this->path_up();
		return $properties;
	}

	function parse_variable( $properties = array() ) {
		$properties = array_merge(
			array(
				'type' => false,
				'name' => '',
				'contents' => '',
				'path' => $this->get_current_path_str(),
				'line' => $this->line,
			),
			$properties
		);

		$encountered_assignment = false;
		$state = self::CLASS_MEMBER_OPEN;
		$levels = array( 'path' => array() );

		for ( ; $this->index < $this->token_count; ++$this->index ) {
			$this->get_token( $token, $token_contents );

			$this->parse_contents_line_breaks( $token_contents );

			if ( T_WHITESPACE === $token ) {
				continue;
			}

			// Checks for an unexpected closing block
			if ( $this->closes_block( $token, $levels ) ) {

				$this->index -= 1;

				if ( false === $properties['type'] ) {
					return;
				}

				$properties['name'] = $this->get_name_with_path( $properties['name'] );
				return $properties;
			}

			switch ( $state ) {
				case self::CLASS_MEMBER_OPEN:
					switch ( $token ) {
						case T_CONST:
							$properties['type'] = 'const';
							break;

						case T_VARIABLE:
							$properties['name'] = substr( $token_contents, 1 );
						case T_VAR:
							$properties['type'] = 'variable';

							break;
						case T_STRING:
							return;
					}

					$state = self::CLASS_MEMBER_DEFINITION;
					break;

				case self::CLASS_MEMBER_DEFINITION:
					switch ( $token ) {
						case T_STRING:
							$properties['name'] = $token_contents;
							break;

						case T_VARIABLE:
							$properties['name'] = substr( $token_contents, 1 );
							break;

						case T_OBJECT_OPERATOR:
						case T_DOUBLE_COLON:
							// A T_OBJECT_OPERATOR before an assignment (=) means that this isn't a variable instantiation, but rather a function call on an object.
							return $this->parse_function_call( array( 'name' => $properties['name'] ) );

						case '=':
							$encountered_assignment = true;
							break;

						default:
							break 3;
					}

					$state = self::CLASS_MEMBER_BODY;
					break;

				case self::CLASS_MEMBER_BODY:
					if ( ! $encountered_assignment && $token === '=' ) {
						$encountered_assignment = true;
						continue;
					} elseif ( $token !== ';' || ! empty( $levels['path'] ) ) {
						$element = $this->parse_next( $levels, ';' );

						if ( !empty( $element ) ) {
							$element['in_var'] = $properties['name'];
							$properties['children'][] = $element;
						}

						if ( $this->tokens[$this->index] === ';' && empty( $levels['path'] ) ) {
							break 2;
						}
					} else {
						break 2;
					}

					break;
			}
		}

		if ( false === $properties['type'] ) {
			return;
		}

		$properties['name'] = $this->get_name_with_path( $properties['name'] );
		return $properties;
	}

	function parse_function_call( $properties = array() ) {
		$properties = array_merge(
			array(
				'type' => 'function_call',
				'name' => '',
				'args' => array(),
				'path' => $this->get_current_path_str(),
				'line' => $this->line,
			),
			$properties
		);

		$levels = array( '(' => 0, '{' => 0, 'path' => array() );
		$state = self::POTENTIAL_FUNCTION_CALL;
		$current_arg = '';

		for ( ; $this->index < $this->token_count; ++$this->index ) {
			$this->get_token( $token, $token_contents );

			$this->parse_contents_line_breaks( $token_contents );

			if ( T_WHITESPACE === $token ) {
				continue;
			}

			// Checks for an unexpected closing block
			if ( $this->closes_block( $token, $levels, true ) ) {
				if ( $state === self::FUNCTION_CALL_ARGS ) {
					break;
				} else {
					$this->index -= 1;
					return;
				}
			}

			switch ( $state ) {
				case self::POTENTIAL_FUNCTION_CALL:
					switch ( $token ) {
						case T_VARIABLE:
							$properties['name'] .= substr( $token_contents, 1 );
							break;

						case T_STRING:
						case T_EVAL:
						case T_EMPTY:
						case T_EXIT:
						case T_HALT_COMPILER:
						case T_INCLUDE:
						case T_INCLUDE_ONCE:
						case T_REQUIRE:
						case T_REQUIRE_ONCE:
						case T_ISSET:
						case T_LIST:
						case T_PRINT:
						case T_UNSET:
							$properties['name'] .= $token_contents;
							break;

						case T_DOUBLE_COLON:
						case T_OBJECT_OPERATOR:
							$properties['name'] .= self::PATH_SEPERATOR;
							break;

						case '(':
							$state = self::FUNCTION_CALL_ARGS;
							break;

						default:
							// This doesn't look like a proper function call. Get out.
							return;
					}
					break;

				case self::FUNCTION_CALL_ARGS:
					switch ( $token ) {
						case ')':
							if ( 0 === $levels['('] ) {
								break 3;
							} elseif ( $levels['('] < 0 ) {
								break 3;
							}

							$current_arg .= $token_contents;
							break;

						case '(':
							$current_arg .= $token_contents;
							break;

						default:
							if ( 0 === $levels['('] ) {
								if ( is_string( $token ) ) {
									break 3;
								}
							}

							// Check if this is a new argument
							if ( 1 === $levels['('] && ',' === $token ) {
								$properties['args'][] = $current_arg;
								$current_arg = '';
								break;
							}

							$current_arg .= $token_contents;
					}
					break;
			}
		}

		if ( ! empty( $current_arg ) ) {
			$properties['args'][] .= $current_arg;
		}

		$parser = new TokenParser();
		foreach ( $properties['args'] as $arg ) {
			if ( ! empty( $properties['path'] ) ) {
				$parser->add_to_path( $properties['path'] );
			}

			$parser->line = $this->line;
			foreach ( $parser->parse_contents( "<?php $arg" ) as $element ) {
				$this->elements[] = $element;
			}
			$this->line = $parser->line;

			$parser->reset();
		}

		return $properties;
	}

	function parse_contents_line_breaks( $whitespace ) {
		$this->line += substr_count( $whitespace, "\n" );
	}

	private function clean_doc_string( $docstring ) {
		// Remove the leading /** and trailing */
		$docstring = substr( $docstring, 3, strlen( $docstring ) - 6 );

		// Remove line beginnings
		$docstring = preg_replace( '/(\r|\n)+\h*\**\h?/', "\n",  $docstring );

		// Remove empty lines at the start
		$docstring = preg_replace( '/\A(\r|\n)+\s*/', '', $docstring );

		// Remove empty lines at the end
		$docstring = preg_replace( '/(\r|\n)+\s*(\r|\n)*\Z/', '', $docstring );

		return $docstring;
	}
}