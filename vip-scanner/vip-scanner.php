<?php
/**
 * Go Go VIP Scanner!
 * Scan all sorts of themes and files and things.
 */
define( 'VIP_SCANNER_DIR', dirname( __FILE__ ) );
define( 'VIP_SCANNER_CHECKS_DIR', VIP_SCANNER_DIR . '/checks' );
define( 'VIP_SCANNER_ANALYZERS_DIR', VIP_SCANNER_DIR . '/analyzers' );
define( 'VIP_SCANNER_BIN_DIR', VIP_SCANNER_DIR . '/bin' );

define( 'PHP_PARSER_BOOTSTRAP', VIP_SCANNER_DIR . '/../vendor/PHP-Parser/lib/bootstrap.php' );

register_activation_hook( dirname( VIP_SCANNER_DIR ) . '/vip-scanner.php', function() {
	if ( ! file_exists( PHP_PARSER_BOOTSTRAP ) ) {
		wp_die( 'VIP-Scanner could not find PHP-Parser, which it requires to run. ' .
				'Please refer to the "Requirements" section in readme.md.' );
	}
} );

// Has PHP-Parser been already loaded, e.g. by another plugin?
if ( class_exists( 'PhpParser\Parser' ) ) {
	wp_die( 'A PHP-Parser instance was loaded before VIP-scanner. ' .
			'If another plugin uses PHP-Parser, please deactivate it.' );
} elseif ( file_exists( PHP_PARSER_BOOTSTRAP ) ) {
	require_once PHP_PARSER_BOOTSTRAP;
}

require_once( VIP_SCANNER_DIR . '/config-vip-scanner.php' );

spl_autoload_register( function( $class_name ) {

	// Class names that are in files that aren't found by our scheme below.
	$other = array(
		'VIP_PregFile'      => 'class-preg-file.php',
		'VIP_Scanner_Async' => 'vip-scanner-async.php',
		'AnalyzedPHPFile'   => 'class-analyzed-php-file.php',
		'AnalyzedCSSFile'   => 'class-analyzed-css-file.php',
		'ElementGroup'      => 'elements/class-element-group.php',
	);

	if ( array_key_exists( $class_name, $other ) ) {
		require VIP_SCANNER_DIR . '/' . $other[ $class_name ];
		return;
	}

	// Example: $class_name === 'ThemeScanner'
	$hyphenated_name = strtolower( preg_replace('/([a-z])([A-Z])/', '$1-$2', $class_name ) ); // Example: theme-scanner
	$category = substr( strrchr( $hyphenated_name, '-' ), 1 ); // Example: scanner
	$plural = substr( $category, -1 ) === 'y' ? substr( $category, 0, -1 ) . 'ies' : $category . 's'; // Example: scanners

	$possible_locations = array(
		VIP_SCANNER_DIR . '/' . $plural . '/class-' . $hyphenated_name . '.php', // Example: vip-scanner/scanners/class-theme-scanner.php
		VIP_SCANNER_DIR . '/class-' . $hyphenated_name . '.php', // Example: vip-scanner/class-theme-scanner.php
	);

	foreach( $possible_locations as $location ) {
		if ( file_exists( $location ) ) {
			require $location ;
			return;
		}
	}
} );

if ( is_admin() ) {
	VIP_Scanner_Async::get_instance();
}

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

// Initialize!
VIP_Scanner::get_instance();
