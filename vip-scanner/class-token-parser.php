<?php

class TokenParser {
	const PATH_SEPERATOR = '::';

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

	private $path		  = array();
	private $token_count  = 0;
	private $tokens		  = array();
	private $elements     = array();
	private $index		  = 0;
	private $in_namespace = false;

	function parse_contents( $contents ) {
		$this->tokens = token_get_all( $contents );
		$this->token_count = count( $this->tokens );

		$this->elements = array();
		$levels = array( '(' => 0, '{' => 0, 'path' => array() );

		for ( ; $this->index < $this->token_count; ++$this->index ) {
			$result = $this->parse_next( $levels );
			if ( ! is_null( $result ) ) {
				$this->elements[] = $result;
			} else {
				$this->index++;
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
				while ( '{' !== end( $blocks['path'] ) ) {
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

			$c = array_pop( $blocks['path'] );
			--$blocks[$c];

			$closes |= $blocks[$c] < 0;
			if ( $c === $matching ) {
				break;
			}
		}

		return $closes || ( $true_on_empty && empty( $blocks['path'] ) );
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

			if ( T_WHITESPACE === $token ) {
				continue;
			}

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
			}
		}
	}

	function parse_namespace( $properties = array() ) {
		if ( false !== $this->in_namespace ) {
			while ( $this->in_namespace !== $this->path_up() );
		}
		
		$properties = array_merge(
			array(
				'name' => '',
				'type' => 'namespace',
				'path' => $this->get_current_path_str(),
			),
			$properties
		);

		$state = self::NAMESPACE_OPEN;
		for ( ; $this->index < $this->token_count; ++$this->index ) {
			$this->get_token( $token, $token_contents );

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
					}

					$properties['name'] .= $token_contents;
					break;
			}
		}

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
			if ( T_WHITESPACE === $this->tokens[$this->index][0] ) {
				continue;
			}

			$this->get_token( $token, $token_contents );

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
				'path' => $this->get_current_path_str(),
			),
			$properties
		);

		$levels = array( '(' => 0, '{' => 0, 'path' => array() );
		$state = self::CLASS_MEMBER_OPEN;

		for ( ; $this->index < $this->token_count; ++$this->index ) {
			$this->get_token( $token, $token_contents );

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
							break;
					}

					break;
				case self::CLASS_MEMBER_DEFINITION:
					if ( $token !== T_STRING ) {
						continue;
					}

					$properties['name'] = $this->get_name_with_path( $token_contents );
					$this->add_to_path( $token_contents );
					$state = self::CLASS_MEMBER_FUNC_DEFINITION;
					break;

				case self::CLASS_MEMBER_FUNC_DEFINITION:
					switch ( $token ) {
						case '(':
							$properties['args'] = '';
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
			),
			$properties
		);

		$last_t_string = -1;
		$encountered_assignment = false;
		$state = self::CLASS_MEMBER_OPEN;
		$levels = array( 'path' => array() );

		for ( ; $this->index < $this->token_count; ++$this->index ) {
			$this->get_token( $token, $token_contents );

			if ( T_WHITESPACE === $token ) {
				continue;
			}

			// Checks for an unexpected closing block
			if ( $this->closes_block( $token, $levels ) ) {
				$properties['name'] = $this->get_name_with_path( $properties['name'] );
				$this->index -= 1;
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
					} elseif ( $token === '(' || $token === T_OBJECT_OPERATOR || $token === T_DOUBLE_COLON ) {
						// Looks like theres a function call within the variable assignment
						if ( $last_t_string !== -1 ) {
							// There's a function call in this variable assignment!
							// e.g: $var = somefunc();
							$start_index   = $this->index;
							$this->index   = $last_t_string;
							$last_t_string = -1;
							$func_call     = $this->parse_function_call( array( 'in_var' => $properties['name'] ) );

							// Un-capture the opening bracket
							if ( $token === '(' ) {
								--$levels['('];
								array_pop( $levels['path'] );
							}

							// If the function call returns null, it wasn't a function call
							if ( is_null( $func_call ) ) {
								$this->index = $start_index;
								continue;
							}

							$properties['children'][] = $func_call;

							// Append the function arguments to the variable definition (the function name will already be there)
							$properties['contents'] .= '(' . implode( ', ', $func_call['args'] ) . ')';
						} else {
							$properties['contents'] .= $token_contents;
						}
					} elseif ( $token !== ';' ) {
						if ( $token === T_STRING || $token === T_VARIABLE ) {
							$last_t_string = $this->index;
						} elseif ( $last_t_string !== -1 ) {
							$last_t_string = -1;
						}

						$properties['contents'] .= $token_contents;
					} else {
						break 2;
					}

					break;
			}
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
			),
			$properties
		);

		$levels = array( '(' => 0, '{' => 0, 'path' => array() );
		$state = self::POTENTIAL_FUNCTION_CALL;
		$current_arg = '';

		for ( ; $this->index < $this->token_count; ++$this->index ) {
			$this->get_token( $token, $token_contents );

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

			foreach ( $parser->parse_contents( "<?php $arg" ) as $element ) {
				$this->elements[] = $element;
			}

			$parser->reset();
		}

		return $properties;
	}
}