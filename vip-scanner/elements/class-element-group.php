<?php

class ElementGroup extends BaseElement {

	/**
	 * @var array<BaseElement>
	 */
	protected $children = array();
	protected $singular = '';
	protected $plural = '';
	protected $analyzed_prefixes = null;
	protected $prefixes = null;
	protected $num_prefixes = 0;
			
	function __construct( $plural, $singular, $attributes = array() ) {
		$this->singular = $singular;
		$this->plural   = $plural;
		$this->name     = $plural;
	}

	/**
	 * Get the building blocks of the header to display
	 * @return array of arrays with 'content' and 'style' keys
	 */
	function get_header() {
		return array(
			array(
				'content' => esc_html( ucwords( $this->name() ) ),
				'style'   => array(
					'bold' => true,
					'classes' => array( 'renderer-class-name' ),
				)
			)
		);
	}

	/**
	 * Get this element's children.
	 * @return array<BaseElement>
	 */
	function get_children( $color_callback = null ) {
		$this->analyze_prefixes();
		if ( ! is_null( $color_callback ) ) {
			$colors = call_user_func( $color_callback, $this->num_prefixes );
		}

		foreach ( $this->children as $child ) {
			$name = $child->name();
			if ( array_key_exists( $name, $this->analyzed_prefixes ) && isset( $colors ) ) {
				$color = $colors[ array_search( $this->analyzed_prefixes[ $name ], $this->prefixes ) ];
				$child->set_prefix( $this->analyzed_prefixes[ $name ], array( 'color' => $color ) );
			}
		}
		return $this->children;
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
}