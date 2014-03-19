<?php

class RendererGroup extends AnalyzerRenderer {
	protected $singular = '';
	protected $analyzed_prefixes = null;
	protected $prefixes = null;
	protected $num_prefixes = 0;
			
	function __construct( $plural, $singular, $attributes = array() ) {
		parent::__construct( $plural, $attributes );
		$this->singular = $singular;
	}
	
	function display( $echo = true, $args = array() ) {
		$output = '';
		$defaults = array(
			'bare'  => false,
			'level' => 0,
			'depth' => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$this->display_args = $args;

		$use_prefixes = !is_null( $this->analyzed_prefixes );
		if ( $use_prefixes ) {
			$colours = $this->randColor( $this->num_prefixes );
		}

		// Output the header. Don't escape here because we expect the header to contain html.
		$header_text = $this->process_header_args( $this->display_header( $args ), $args );
		if ( ! $args['bare'] ) {
			$header_classes = array( 'renderer-group-header' );
			if ( isset( $args['body_classes'] ) ) {
				$header_classes = array_merge( $header_classes, $args['body_classes'] );
			}

			if ( $this->is_empty() ) {
				$header_classes[] = 'renderer-group-empty';
			}

			$output .= '<h3 class="' . esc_attr( implode( ' ', $header_classes ) ) . '">' . $header_text . '</h3>';
			
			$body_classes = array( 'renderer-group-body' );
			if ( isset( $args['body_classes'] ) ) {
				$body_classes = array_merge( $body_classes, $args['body_classes'] );
			}

			$output .= '<div class="' . esc_attr( implode( ' ', $body_classes ) ) . '">';
			$output .= '<div class="renderer-group-children">';
		} else {
			$output .= str_repeat( $this->spacing_char, $args['level'] ) . $header_text . "\n";
			$args['level'] += 1;
		}

		if ( 0 === $args['depth'] || $args['level'] < $args['depth'] ) {
			foreach ( $this->children as $child ) {
				$name = $child->name();
				if ( $use_prefixes && array_key_exists( $name, $this->analyzed_prefixes ) ) {
					$args['highlight_substrs'] = array( array(
						'str'   => $this->analyzed_prefixes[$name],
						'color' => $colours[array_search( $this->analyzed_prefixes[$name], $this->prefixes )],
					) );
				}

				$output .= $child->display( false, $args );
			}
		}

		if ( ! $args['bare'] ) {
			$output .= '</div>';
		}
		
		$output .= $this->display_attributes( $args );
		
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

	function display_header() {
		return sprintf( 
			'%s (%s)',
			$this->stylize_text( esc_html( ucwords( $this->name() ) ), array( 'bold' => true, 'classes' => array( 'renderer-class-name' ) ) ),
			empty( $this->children ) ? '0' : esc_html( $this->get_child_summary() )
		);
	}

	function analyze_prefixes() {
		$similar = array();

		foreach ( $this->children as $child1 ) {
			$child1_name = $child1->name();
			foreach ( $this->children as $child2 ) {
				if ( $child1 === $child2 ) {
					continue;
				}

				$child2_name = $child2->name();

				$common = $this->get_common_prefix( $child1_name, $child2_name );

				if ( strlen( $common ) < 3 ) {
					continue;
				}

				if ( !isset($similar[$common] ) ) {
					$similar[$common] = array();
				}

				$similar[$common][] = $child1_name;
				$similar[$common][] = $child2_name;
			}
		}

		// Sort based on highest number of children
		uasort( $similar, function( $a, $b ) {
			$ac = count( $a );
			$bc = count( $b );

			if ($ac == $bc) {
				return 0;
			}

			return ($ac > $bc) ? -1 : 1;
		} );

		$this->num_prefixes = count( $similar );
		$this->prefixes = array_keys( $similar );

		// Iterate through, assigning each child a group
		$this->analyzed_prefixes = array();
		foreach ( $similar as $common_str => $common_elements ) {
			foreach ( $common_elements as $element ) {
				if ( !array_key_exists( $element, $this->analyzed_prefixes ) ) {
					$this->analyzed_prefixes[$element] = $common_str;
				}
			}
		}
	}

	function get_common_prefix( $str1, $str2 ) {
		$common = array();
		$str2len = strlen( $str2 );

		foreach ( str_split( $str1 ) as $index => $chr ) {
			if ( $index === $str2len || $str2[$index] !== $chr ) {
				return implode( '', $common );
			}

			$common[] = $chr;
		}

		return implode( '', $common );
	}

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