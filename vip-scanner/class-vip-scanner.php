<?php
/**
 * Go Go VIP Scanner!
 * Scan all sorts of themes and files and things.
 */

class VIP_Scanner {
	private static $instance;
	var $reviews = array();

	static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			$class_name = __CLASS__;
			self::$instance = new $class_name;
		}
		return self::$instance;
	}

	function get_review_types() {
		return array_keys( $this->reviews );
	}

	function get_review( $name ) {
		if ( isset( $this->reviews[ $name ] ) )
			return $this->reviews[ $name ];
		return false;
	}

	function register_review( $name, $checks, $analyzers = array() ) {
		$this->reviews[ $name ] = array(
			'checks'	=> (array) $checks,
			'analyzers' => (array) $analyzers,
		);
	}

	function run_theme_review( $theme, $review_type, $scanners = array( 'checks', 'analyzers' ) ) {
		$review = $this->get_review( $review_type );
		if ( ! $review )
			return false;

		do_action( 'vip_scanner_pre_theme_review', $theme, $review_type );

		$scanner = new ThemeScanner( $theme, $review );
		$scanner->scan( $scanners );

		do_action( 'vip_scanner_post_theme_review', $theme, $review_type, $scanner );
		return $scanner;
	}
}
