<?php

/**
 * Class VIP_Scanner
 */
class VIP_Scanner {
	/**
	 * Singleton instance.
	 *
	 * @var VIP_Scanner
	 */
	private static $instance = null;

	/**
	 * Available review types and associated checks and analyzers.
	 *
	 * @var array
	 */
	var $reviews = array();

	/**
	 * Retrieves Singleton instance.
	 *
	 * @return VIP_Scanner
	 */
	static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new VIP_Scanner;
		}

		return self::$instance;
	}

	/**
	 * Retrieve names of review types.
	 *
	 * @return array Review type names.
	 */
	function get_review_types() {
		return array_keys( $this->reviews );
	}

	/**
	 * Retrieve checks and analyzers of a review.
	 *
	 * @param string $name Name of review.
	 *
	 * @return array|bool Array of checks and analyzers. False if review type does not exist.
	 */
	function get_review( $name ) {
		if ( isset( $this->reviews[ $name ] ) )
			return $this->reviews[ $name ];
		return false;
	}

	/**
	 * @param string $name      Name of review.
	 * @param array  $checks    Check class names.
	 * @param array  $analyzers Analyzer class names.
	 */
	function register_review( $name, $checks, $analyzers = array() ) {
		$this->reviews[ $name ] = array(
			'checks'	=> (array) $checks,
			'analyzers' => (array) $analyzers,
		);
	}

	/**
	 * Run the checks and analyzers of a review.
	 *
	 * @param string $theme       Name of the theme to scan.
	 * @param string $review_type Name of review to run.
	 * @param array  $scanners    Class names of the checks and analyzers to run.
	 *
	 * @return bool|ThemeScanner  ThemeScanner instance. False if the review type does not exist.
	 */
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

// Initialize!
VIP_Scanner::get_instance();
