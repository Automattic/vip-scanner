<?php

abstract class AnalyzerRenderer {
	/**
	 * @var array<AnalyzerRenderer>
	 */
	protected $children = array();
	protected $name = '';
	protected $attributes = array();
	protected $stats = array();
	protected $singular = 'item';
	protected $plural = 'items';
	protected $spacing_char = "\t";
	protected $display_args = array();
	protected $bash_color_codes = array(
		'red'     => 31,
		'green'   => 32,
		'yellow'  => 33,
		'blue'    => 34,
		'magenta' => 35,
		'cyan'    => 36,
		'white'   => 37,
		'black'   => 30,
	);
	
	function __construct( $name, $attributes = array() ) {
		$this->name = $name;
		$this->attributes = $attributes;
	}

	/**
	 * Gets the name of this element.
	 * @return string
	 */
	function name() {
		return $this->name;
	}
	
	/**
	 * Gets a string that should be relatively unique for this element.
	 * @return array
	 */
	function identifier() {
		return $this->name();
	}
	
	/**
	 * Gets the singular noun for this class.
	 * @return string
	 */
	function singular() {
		return $this->singular;
	}
	
	/**
	 * Gets the plural noun for this class.
	 * @return string
	 */
	function plural() {
		return $this->plural;
	}
	
	/**
	 * Displays this and all children as a hierarchical list. Assumes to
	 * already be surround in either a <ul> or <ol>.
	 * 
	 * @param bool $echo Whether or not to echo the output
	 * @return string
	 */
	function display( $echo = true, $args = array() ) {
		$output = '';
		
		$defaults = array(
			'bare'  => false,
			'level' => 0,
			'depth' => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$this->display_args = $args;

		// Output the header. Don't escape here because we expect the header to contain html.
		$header_text = $this->process_header_args( $this->display_header( $args ), $args );
		if ( ! $args['bare'] ) {
			$header_classes = array( 'renderer-group-header' );
			if ( $this->is_empty() ) {
				$header_classes[] = 'renderer-group-empty';
			}

			if ( isset( $args['body_classes'] ) ) {
				$header_classes = array_merge( $header_classes, $args['body_classes'] );
			}

			$output .= '<h3 class="' . esc_attr( implode( ' ', $header_classes ) ) . '">' . $header_text . '</h3>';
			
			$classes = array( 'renderer-group-body' );
			if ( isset( $args['classes'] ) ) {
				$classes = array_merge( $classes, $args['classes'] );
			}

			// Output the body container div
			$output .= '<div class="' . implode( ' ', $classes ) . '">';
			$output .= '<div class="renderer-group-children">';
		} else {
			$output .= str_repeat( $this->spacing_char, $args['level'] ) . $header_text . "\n";
			$args['level'] += 1;
		}

		if ( 0 === $args['depth'] || $args['level'] < $args['depth'] ) {
			foreach ( $this->children as $child ) {
				$output .= $child->display( false, $args );
			}
		}

		if ( ! $args['bare'] ) {
			$output .= '</div>';
		}

		// Output attributes
		$output .= $this->display_attributes( $args );
		
		// Output stats
		$output .= $this->display_stats( $args );

		if ( ! $args['bare'] ) {
			$output .= '</div>';
		}

		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}
	
	/**
	 * Gets the HTML for displaying this renderers' stats.
	 * @param array $args
	 * @return string
	 */
	function display_stats( $args ) {
		$output = '';
		if ( !empty( $this->stats ) ) {
			$skip_stats = $this->skip_stats();
			if ( ! isset( $args['bare'] ) || ! $args['bare'] ) {
				$classes = array( 'renderer-group-stats' );
				if ( isset( $args['stats_classes'] ) ) {
					$classes = array_merge( $classes, $args['stats_classes'] );
				}

				$output .= '<div class="' . implode( ' ', $classes ) . '"><ul>';
				foreach ( $this->stats as $slug => $stat ) {
					if ( ! in_array( $slug, $skip_stats ) ) {
						$output .= sprintf( '<li><strong>%s</strong>: %s</li>', esc_html( $slug ), number_format( $stat ) ); 
					}
				}
				$output .= '</ul></div>';
			} else {
				foreach ( $this->stats as $slug => $stat ) {
					if ( ! in_array( $slug, $skip_stats ) ) {
						$output .= sprintf( "%s> %s: %s\n", str_repeat( $this->spacing_char, $args['level'] ), $slug, number_format( $stat ) );
					}
				}
			}
		}
		return $output;
	}
	
	/**
	 * Gets the HTML for displaying this renderers' attributes.
	 * @param array $args
	 * @return string
	 */
	function display_attributes( $args ) {
		$output = '';
		if ( !empty( $this->attributes ) ) {
			$skip_attributes = $this->skip_attributes();
			if ( ! $args['bare'] ) {
				$classes = array( 'renderer-group-attributes' );
				if ( isset( $args['attributes_classes'] ) ) {
					$classes = array_merge( $classes, $args['attributes_classes'] );
				}

				$output .= '<div class="' . implode( ' ', $classes ) . '"><ul>';
				foreach ( $this->attributes as $slug => $attribute ) {
					if ( ! in_array( $slug, $skip_attributes ) && ! empty( $attribute ) ) {
						$output .= $this->display_html_attribute( $slug, $attribute, $args );
					}
				}
				$output .= '</ul></div>';
			} else {
				foreach ( $this->attributes as $slug => $attribute ) {
					if ( ! in_array( $slug, $skip_attributes ) && ! empty( $attribute ) ) {
						if ( is_string( $attribute ) ) {
							$output .= sprintf( "%s> %s: %s\n", str_repeat( $this->spacing_char, $args['level'] ), $slug, $attribute );
						} elseif ( is_numeric( $attribute ) ) {
							$output .= sprintf( "%s> %s: %s\n", str_repeat( $this->spacing_char, $args['level'] ), $slug, number_format( $attribute ) );
						} elseif ( is_array( $attribute ) ) {
							$output .= sprintf( "%s> %s: %s\n", str_repeat( $this->spacing_char, $args['level'] ), $slug, implode( ', ', $attribute ) );
						}
					}
				}
			}
		}
		return $output;
	}

	function display_html_attribute( $slug, $attribute, $args ) {
		$fstring = '<li><strong>%s</strong>: %s</li>';
		if ( is_string( $attribute ) ) {
			return sprintf( $fstring, esc_html( $slug ), esc_html( $attribute ) );
		} elseif ( is_numeric( $attribute ) ) {
			return sprintf( $fstring, esc_html( $slug ), number_format( $attribute ) );
		} elseif ( is_bool( $attribute ) ) {
			return sprintf( $fstring, esc_html( $slug ), $attribute ? __( 'true', 'vip-scanner' ) : __( 'false', 'vip-scanner' ) );
		} elseif ( is_array( $attribute ) ) {
			$output = '';
			foreach ( $attribute as $key => $value ) {
				$output .= $this->display_html_attribute( $key, $value, $args );
			}
			return sprintf( $fstring, esc_html( $slug ), "<ul>$output</ul>" );
		}
	}
	
	/**
	 * Transforms the $header text as specified by the $args.
	 * 
	 * Setting `highlight_substrs` is used to highlight parts of a string with a colour.
	 * 
	 * @param array $args
	 * @return string
	 */
	function process_header_args( $header, $args ) {
		if ( isset( $args['highlight_substrs'] ) ) {
			foreach ( $args['highlight_substrs'] as $highlight_arg ) {
					$escaped_str = esc_html( $highlight_arg['str'] );
					$header = str_replace(
						$escaped_str,
						$this->stylize_text( $escaped_str, array( 'color' => $highlight_arg['color'] ) ),
						$header
					);
			}
		}

		return $header;
	}

	/**
	 * Gets the header to display. This should include any important attributes.
	 * 
	 * @return string
	 */
	function display_header() {
		return $this->name();
	}
	
	/**
	 * Applies styling to text depending on which output system is being used. If
	 * we're rendering a web UI it will return text with HTML. If we are outputting
	 * to the shell then it will return bash text modififers.
	 * @param string $text
	 * @param array $opts
	 * @return string
	 */
	function stylize_text( $text, $opts ) {
		if ( $this->display_args['bare'] ) {
			$f_str = '';
			if ( isset( $opts['bold'] ) ) {
				$f_str .= '01;';
			}
			if ( isset( $opts['code'] ) ) {
				$f_str .= '02;';
			}
			if ( isset( $opts['underline'] ) ) {
				$f_str .= '04;';
			}
			if ( isset( $opts['small'] ) ) {
				$f_str .= $this->bash_color_codes['blue'] . ';';
			}
			if ( isset( $opts['color'] ) ) {
				if ( isset( $this->bash_color_codes[$opts['color']] ) ) {
					$f_str .= $this->bash_color_codes[$opts['color']] . ';';
				}
			}

			if ( !empty( $f_str ) ) {
				$f_str = substr( $f_str, 0, -1 );
				return "\e[{$f_str}m$text\e[0m";
			}
		} else {
			$styles = array();
			$classes = '';
			if ( isset( $opts['classes'] ) ) {
				$classes = implode( ' ', $opts['classes'] );
			}

			if ( isset( $opts['bold'] ) ) {
				$text = "<strong class='$classes'>$text</strong>";
			}
			if ( isset( $opts['code'] ) ) {
				$text = "<code class='$classes'>$text</code>";
			}
			if ( isset( $opts['small'] ) ) {
				$text = "<small class='$classes'>$text</small>";
			}
			if ( isset( $opts['underline'] ) ) {
				$styles[] = 'font-decoration: underline;';
			}
			if ( isset( $opts['color'] ) ) {
				$styles[] = 'color: ' . $opts['color'] . ';';
			}

			if ( !empty( $styles ) ) {
				return '<span classes="' . $classes . '" style="' . implode( ' ', $styles ) . '">' . $text . '</span>';
			}
		}
		
		return $text;
	}
	
	/**
	 * Adds a child AnalyzerRenderer to this object.
	 * 
	 * @param AnalyzerRenderer $child
	 */
	function add_child( $child ) {
		$this->children[$child->identifier()] = $child;
	}
	
	/**
	 * Gets this objects the children.
	 * @return array<AnalyzerRenderer>
	 */
	function get_children() {
		return $this->children;
	}
	
	/**
	 * Gets a textual summary of all of the children of this item
	 * @return string
	 */
	function get_child_summary() {
		$summary = array();

		// Count children of each type
		foreach ( $this->get_children() as $child ) {
			$singular = $child->singular();
			if ( !array_key_exists( $singular, $summary ) ) {
				$summary[$singular] = array(
					'count'  => 0,
					'plural' => $child->plural(),
				);
			}

			$summary[$singular]['count']++;
		}

		// Build the string
		$summary_string = array();
		foreach ( $summary as $singular => $info ) {
			$summary_string[] = sprintf( '%s %s', number_format( $info['count'] ), $info['count'] == 1 ? $singular : $info['plural'] );
		}

		return implode( ', ', $summary_string );
	}
	
	/**
	 * Adds a statistic to this item.
	 * @param string $name
	 * @param number $stat
	 */
	function add_stat( $name, $stat ) {
		$this->stats[$name] = $stat;
	}
	
	/**
	 * Gets a statistic from this item. If the stat is not set returns 0.
	 * @param string $name
	 * @return int
	 */
	function get_stat( $name ) {
		if ( isset( $this->stats[$name] ) ) {
			return $this->stats[$name];
		} else {
			return 0;
		}
	}
	
	/**
	 * Gets the attributes for this item.
	 * @return array
	 */
	function get_attributes() {
		return $this->attributes;
	}
	
	/**
	 * Gets a single attribute.
	 * @param string $name The name of the attribute to get
	 * @return string
	 */
	function get_attribute( $name ) {
		if ( isset( $this->attributes[$name] ) ) {
			return $this->attributes[$name];
		} else {
			return '';
		}
	}
	
	/**
	 * Sets the attributes for this item. $attributes should be in the form:
	 *	array( 'attribute_name' => 'attribute_value' );
	 * 
	 * @param array $attributes
	 */
	function set_attributes( $attributes ) {
		$this->attributes = $attributes;
	}
	
	/**
	 * Adds the specified attribute on this item.
	 * 
	 * @param string $name
	 * @param mixed $attribute
	 */
	function add_attribute( $name, $attribute ) {
		$this->attributes[$name] = $attribute;
	}
	
	/**
	 * Checks whether this renderer has anything to display. True if it is empty
	 * of false if it has contents.
	 * 
	 * @return boolean
	 */
	function is_empty() {
		return empty( $this->children ) && empty( $this->attributes ) && empty( $this->stats );
	}
	
	/**
	 * Returns an array of attribute names that should not be displayed in the ui.
	 * @return array
	 */
	protected function skip_attributes() {
		return array( 'contents', 'name', 'children' );
	}
	
	/**
	 * Returns an array of attribute names that should not be displayed in the ui.
	 * @return array
	 */
	protected function skip_stats() {
		return array();
	}
}