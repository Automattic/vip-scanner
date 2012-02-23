<?php
/**
 * Go Go VIP Scanner!
 * Scan all sorts of themes and files and things.
 */
define( 'VIP_SCANNER_DIR', dirname( __FILE__ ) );
define( 'VIP_SCANNER_CHECKS_DIR', VIP_SCANNER_DIR . '/checks' );

require_once( VIP_SCANNER_DIR . '/config-vip-scanner.php' );
require_once( VIP_SCANNER_DIR . '/class-base-check.php' );
require_once( VIP_SCANNER_DIR . '/class-base-scanner.php' );
require_once( VIP_SCANNER_DIR . '/class-directory-scanner.php' );
require_once( VIP_SCANNER_DIR . '/class-theme-scanner.php' );
require_once( VIP_SCANNER_DIR . '/class-content-scanner.php' );
require_once( VIP_SCANNER_DIR . '/class-diff-scanner.php' );
require_once( VIP_SCANNER_DIR . '/class-preg-file.php' );

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
