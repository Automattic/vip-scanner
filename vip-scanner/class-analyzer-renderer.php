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

		// Output the header. Don't escape here because we expect the header to contain html.
		$header_classes = array( 'renderer-group-header' );
		if ( isset( $args['body_classes'] ) ) {
			$header_classes = array_merge( $header_classes, $args['body_classes'] );
		}

		$output .= '<h3 class="' . esc_attr( implode( ' ', $header_classes ) ) . '">' . $this->process_header_args( $this->display_header(), $args ) . '</h3>';

		$classes = array( 'renderer-group-body' );
		if ( isset( $args['classes'] ) ) {
			$classes = array_merge( $classes, $args['classes'] );
		}

		// Output the body container div
		$output .= '<div class="' . implode( ' ', $classes ) . '">';
		$output .= '<div class="renderer-group-children">';
		foreach ( $this->children as $child ) {
			$output .= $child->display( false );
		}
		$output .= '</div>';
		
		// Output attributes
		$output .= $this->display_attributes( $args );
		
		// Output stats
		$output .= $this->display_stats( $args );
		
		$output .= '</div>';

		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}
	
	function display_stats( $args ) {
		$output = '';
		if ( !empty( $this->stats ) ) {
			$classes = array( 'renderer-group-stats' );
			if ( isset( $args['stats_classes'] ) ) {
				$classes = array_merge( $classes, $args['stats_classes'] );
			}

			$output .= '<div class="' . implode( ' ', $classes ) . '"><ul>';
			foreach ( $this->stats as $slug => $stat ) {
				$output .= sprintf( '<li><strong>%s</strong>: %s</li>', esc_html( $slug ), number_format( $stat ) );
			}
			$output .= '</ul></div>';
		}
		return $output;
	}
	
	function display_attributes( $args ) {
		$output = '';
		if ( !empty( $this->stats ) ) {
			$classes = array( 'renderer-group-attributes' );
			if ( isset( $args['attributes_classes'] ) ) {
				$classes = array_merge( $classes, $args['attributes_classes'] );
			}

			$output .= '<div class="' . implode( ' ', $classes ) . '"><ul>';
			foreach ( $this->attributes as $slug => $stat ) {
				$output .= sprintf( '<li><strong>%s</strong>: %s</li>', esc_html( $slug ), esc_html( $stat ) );
			}
			$output .= '</ul></div>';
		}
		return $output;
	}
	
	function process_header_args( $header, $args ) {
		if ( isset( $args['highlight_substrs'] ) ) {
			foreach ( $args['highlight_substrs'] as $highlight_arg ) {
				$escaped_str = esc_html( $highlight_arg['str'] );
				$header = str_replace(
					$escaped_str,
					sprintf( '<span style="color: %s;">%s</span>', esc_attr( $highlight_arg['color'] ), $escaped_str ),
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
}