<?php

abstract class BaseElement {
	/**
	 * @var array<BaseElement>
	 */
	protected $children = array();
	protected $attributes = array();
	protected $stats = array();
	protected $singular = 'item';
	protected $plural = 'items';
	protected $prefix = '';
	protected $prefix_style = array();

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
	 * Get the building blocks of the header to display
	 * @return array of arrays with 'content' and 'style' keys
	 */
	abstract public function get_header();

	/**
	 * Get the attributes for this item.
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
		if ( isset( $this->attributes[ $name ] ) ) {
			return $this->attributes[ $name ];
		} else {
			return '';
		}
	}

	/**
	 * Adds the specified attribute on this item.
	 *
	 * @param string $name
	 * @param mixed $attribute
	 */
	function add_attribute( $name, $attribute ) {
		$this->attributes[ $name ] = $attribute;
	}

	/**
	 * Returns an array of attribute names that should not be displayed in the ui.
	 * @return array
	 */
	function skip_attributes() {
		return array( );
	}

	/**
	 * Get the statistics from this item.
	 * @return int
	 */
	function get_stats() {
		return $this->stats;
	}

	/**
	 * Returns an array of statistics names that should not be displayed in the ui.
	 * @return array
	 */
	public function skip_stats() {
		return array();
	}

	/**
	 * Get a statistic from this item. If the stat is not set returns 0.
	 * @param string $name
	 * @return int
	 */
	function get_stat( $name ) {
		if ( isset( $this->stats[ $name ] ) ) {
			return $this->stats[ $name ];
		} else {
			return 0;
		}
	}

	/**
	 * Add a statistic to this item.
	 * @param string $name
	 * @param number $stat
	 */
	function add_stat( $name, $stat ) {
		$this->stats[ $name ] = $stat;
	}

	/**
	 * Get this object's children.
	 * @param mixed optional data
	 * @return array<BaseElement>
	 */
	function get_children( $options = null) {
		return $this->children;
	}

	/**
	 * Add a child to this element
	 *
	 * @param BaseElement $child
	 */
	function add_child( $child ) {
		$this->children[ $child->identifier() ] = $child;
	}

	/**
	 * Checks whether this element has anything to display. True if it is empty
	 * of false if it has contents.
	 *
	 * @return boolean
	 */
	function is_empty() {
		return empty( $this->children ) && empty( $this->attributes ) && empty( $this->stats );
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
				$summary[ $singular ] = array(
						'count'  => 0,
						'plural' => $child->plural(),
				);
			}

			$summary[ $singular ]['count']++;
		}
		return $summary;
	}

	/**
	 * Set a prefix and corresponding style for this object's name
	 *
	 * This can be used to style the prefix differently from the rest using
	 * prefixed_name().
	 *
	 * @param string $prefix
	 * @param array $style
	 */
	function set_prefix( $prefix, $style ) {
		$this->prefix = $prefix;
		$this->prefix_style = $style;
	}

	/**
	 * Split this object's name into a prefix and rest
	 * @return array
	 */
	function prefixed_name(){
		$len = strlen( $this->prefix );

		return array(
			array(
				'content' => esc_html( substr( $this->name(), 0, $len ) ),
				'style'   => $this->prefix_style,
			),
			array(
				'content' => esc_html( substr( $this->name(), $len ) ),
			)
		 );
	}
}