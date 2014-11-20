<?php

class ElementRenderer {
	protected $element;
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

	function __construct( $element ) {
		$this->element = $element;
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
		$header_text = $this->display_header( $this->element->get_header() );
		if ( $this->element->get_child_summary() ) {
			$header_text .= ' ' . $this->stylize_text( '(' . $this->display_child_summary( $this->element->get_child_summary() ) . ')',
					array( 'small' => true) );
		}
		if ( ! $args['bare'] ) {
			$header_classes = array( 'renderer-group-header' );
			if ( isset( $args['body_classes'] ) ) {
				$header_classes = array_merge( $header_classes, $args['body_classes'] );
			}

			if ( $this->element->is_empty() ) {
				$header_classes[] = 'renderer-group-empty';
			}

			$output .= '<h3 class="' . esc_attr( implode( ' ', $header_classes ) ) . '">' . $header_text . '</h3>';

			$body_classes = array( 'renderer-group-body' );
			if ( isset( $args['body_classes'] ) ) {
				$body_classes = array_merge( $body_classes, $args['body_classes'] );
			}

			// Output the body container div
			$output .= '<div class="' . esc_attr( implode( ' ', $body_classes ) ) . '">';
			$output .= '<div class="renderer-group-children">';
		} else {
			$output .= str_repeat( $this->spacing_char, $args['level'] ) . $header_text . "\n";
			$args['level'] += 1;
		}

		if ( 0 === $args['depth'] || $args['level'] < $args['depth'] ) {
			foreach ( $this->element->get_children( array( $this, 'randColor' ) ) as $child ) {
				$r = new ElementRenderer( $child );
				$output .= $r->display( false, $args );
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
		} else {
			$output .= "\n";
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
		if ( $this->element->get_stats() ) {
			$skip_stats = $this->element->skip_stats();
			if ( ! isset( $args['bare'] ) || ! $args['bare'] ) {
				$classes = array( 'renderer-group-stats' );
				if ( isset( $args['stats_classes'] ) ) {
					$classes = array_merge( $classes, $args['stats_classes'] );
				}

				$output .= '<div class="' . implode( ' ', $classes ) . '"><ul>';
				foreach ( $this->element->get_stats() as $slug => $stat ) {
					if ( ! in_array( $slug, $skip_stats ) ) {
						$output .= sprintf( '<li><strong>%s</strong>: %s</li>', esc_html( $slug ), number_format( $stat ) ); 
					}
				}
				$output .= '</ul></div>';
			} else {
				foreach ( $this->element->get_stats() as $slug => $stat ) {
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
		if ( $this->element->get_attributes() ) {
			$skip_attributes = $this->element->skip_attributes();
			if ( ! $args['bare'] ) {
				$classes = array( 'renderer-group-attributes' );
				if ( isset( $args['attributes_classes'] ) ) {
					$classes = array_merge( $classes, $args['attributes_classes'] );
				}

				$output .= '<div class="' . implode( ' ', $classes ) . '"><ul>';
				foreach ( $this->element->get_attributes() as $slug => $attribute ) {
					if ( ! in_array( $slug, $skip_attributes ) && ! empty( $attribute ) ) {
						$output .= $this->display_html_attribute( $slug, $attribute, $args );
					}
				}
				$output .= '</ul></div>';
			} else {
				foreach ( $this->element->get_attributes() as $slug => $attribute ) {
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
			if ( substr_count( $attribute, "\n" ) ) {
				return sprintf( $fstring, esc_html( $slug ), '<pre>' . esc_html( $attribute ) . '</pre>' );
			} else {
				return sprintf( $fstring, esc_html( $slug ), esc_html( $attribute ) );
			}
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
	 * Gets the header to display. This should include any important attributes.
	 * 
	 * @return string
	 */
	function display_header( $header, $level = 0 ) {
		$output = '';
		foreach( $header as $piece ) {
			if ( ! is_array( $piece ) ) {
				$output .= $piece . ' ';
				continue;
			}

			if ( isset ( $piece['content'] ) && is_array( $piece['content'] ) ) {
				$content = $this->display_header( $piece['content'], $level+1 );
			} else {
				$content = $piece['content'];
			}

			if ( empty( $piece['style'] ) ) {
				$output .= $content;
			} else {
				$output .= $this->stylize_text( $content, $piece['style'] );
			}
			if ( $level === 0 ) {
				$output .= ' ';
			}
		}
		return rtrim( $output );
	}

	/**
	 * Display an aggregate summary of this analyzer's children
	 * @see BaseElement::get_child_summary()
	 * @param array $summary the summary to display
	 */
	function display_child_summary( $summary ) {
		$summary_string = array();
		foreach ( $summary as $singular => $info ) {
			$summary_string[] = sprintf( '%s %s', number_format( $info['count'] ), $info['count'] == 1 ? $singular : $info['plural'] );
		}

		return implode( ', ', $summary_string );
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
	 * Generates $numColors random colours. Used to highlight prefixes.
	 * @param int $numColors
	 * @return array
	 */
	function randColor( $numColors ) {
		if ( $this->display_args['bare'] ) {
			$code = 0;
			$keys = array_keys( $this->bash_color_codes );
			$numColors = count( $keys );
			$codes = array();

			for ( $i = 0; $i < $numColors; $i++ ) {
				$codes[] = $keys[$code++ % $numColors];
				//				$code[] = $keys[1];
			}

			return $codes;
		} else {
			$base = array( 200, 200, 200 );
			$str = array();

			for( $i = 0; $i < $numColors; $i++ ) {
				$colour = array(
						( $base[0] + rand( 0, 255 ) ) / 2,
						( $base[1] + rand( 0, 255 ) ) / 2,
						( $base[2] + rand( 0, 255 ) ) / 2,
				);

				$str[$i] = sprintf( 'rgb( %d, %d, %d )', $colour[0], $colour[1], $colour[2] );
			}
		}

		return $str;
	}
}