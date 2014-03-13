<?php

abstract class AnalyzerMeta {
	/**
	 * @var array<AnalyzerMeta>
	 */
	protected $child_metas = array();
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
	 * Gets the name of this meta element.
	 * @return string
	 */
	function name() {
		return $this->name;
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
	 * Displays this meta and all child metas as a hierarchical list. Assumes to
	 * already be surround in either a <ul> or <ol>.
	 * 
	 * @param bool $echo Whether or not to echo the output
	 * @return string
	 */
	function display( $echo = true ) {
		$output = '';
		
		// Output the header. Don't escape here because we expect the header to contain html.
		$output .= '<h3>' . $this->display_header() . '</h3>';
		
		$output .= '<div class="meta-group-body">';
		foreach ( $this->child_metas as $meta ) {
			$output .= $meta->display( false );
		}
		$output .= '</div>';
		
		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}
	
	/**
	 * Gets the header to display for this meta. This should include any important attributes.
	 * 
	 * @return string
	 */
	function display_header() {
		return $this->name();
	}
	
	/**
	 * Adds a child AnalyzerMeta to this object.
	 * 
	 * @param AnalyzerMeta $child
	 */
	function add_child_meta( $child ) {
		$this->child_metas[$child->name()] = $child;
	}
	
	/**
	 * Gets this objects the child metas.
	 * @return array<AnalyzerMeta>
	 */
	function get_child_metas() {
		return $this->child_metas;
	}
	
	/**
	 * Gets a textual summary of all of the children of this item
	 * @return string
	 */
	function get_child_summary() {
		$summary = array();

		// Count children of each type
		foreach ( $this->get_child_metas() as $meta ) {
			$singular = $meta->singular();
			if ( !array_key_exists( $singular, $summary ) ) {
				$summary[$singular] = array(
					'count'  => 0,
					'plural' => $meta->plural(),
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
	 * Adds a statistic to this meta item.
	 * @param string $name
	 * @param number $stat
	 */
	function add_stat( $name, $stat ) {
		$this->stats[$name] = $stat;
	}
	
	/**
	 * Gets a statistic from this meta. If the stat is not set returns 0.
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
	 * Gets the attributes for this meta.
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
	 * Sets the attributes for this meta. $attributes should be in the form:
	 *	array( 'attribute_name' => 'attribute_value' );
	 * 
	 * @param array $attributes
	 */
	function set_attributes( $attributes ) {
		$this->attributes = $attributes;
	}
	
	/**
	 * Adds the specified attribute on this meta.
	 * 
	 * @param string $name
	 * @param mixed $attribute
	 */
	function add_attribute( $name, $attribute ) {
		$this->attributes[$name] = $attribute;
	}
}