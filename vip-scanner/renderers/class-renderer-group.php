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

	function display_header() {
		return sprintf( 
			'%s (%s)',
			$this->stylize_text( esc_html( ucwords( $this->name() ) ), array( 'bold' => true, 'classes' => array( 'renderer-class-name' ) ) ),
			empty( $this->children ) ? '0' : esc_html( $this->get_child_summary() )
		);
	}

	/**
	 * Analyzes the groups childrens' names for prefixes. Attempts to find the 
	 * longest/most common prefix within the children.
	 * 
	 * The output of this function is placed in the class members $analyzed_prefixes,
	 * $prefixes, and $num_prefixes.
	 */
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

	/**
	 * Searches two strings for a common prefix. Returns the longest string of matching
	 * characters from the start of the string.
	 * @param string $str1
	 * @param string $str2
	 * @return string
	 */
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