<?php
/**
 * Go Go VIP Scanner!
 * Scan all sorts of themes and files and things.
 */

define( 'VIP_SCANNER_CHECKS_DIR', __DIR__ . '/checks' );

require_once( __DIR__ . '/config-vip-scanner.php' );
require_once( __DIR__ . '/class-base-check.php' );
require_once( __DIR__ . '/class-base-scanner.php' );
require_once( __DIR__ . '/class-directory-scanner.php' );
require_once( __DIR__ . '/class-theme-scanner.php' );
require_once( __DIR__ . '/class-content-scanner.php' );
require_once( __DIR__ . '/class-diff-scanner.php' );

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

	function register_review( $name, $checks ) {
		$this->reviews[ $name ] = (array) $checks;
	}

	function run_theme_review( $theme, $review_type ) {
		$review = $this->get_review( $review_type );
		if ( ! $review )
			return false;

		$scanner = new ThemeScanner( $theme, $review );
		$scanner->scan();
		return $scanner;
	}
}

// Initialize!
VIP_Scanner::get_instance();
