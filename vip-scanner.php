<?php
/*
Plugin Name: VIP Scanner
Plugin URI: http://vip.wordpress.com
Description: Easy to use UI for the VIP Scanner.
Author: Automattic (Original code by Pross, Otto42, and Thorsten Ott)
Version: 0.8

License: GPLv2
*/

define( 'VIP_SCANNER__PLUGIN_FILE',           __FILE__ );
define( 'VIP_SCANNER__PLUGIN_DIR',            dirname( __FILE__ ) );
define( 'VIP_SCANNER_DIR',                    VIP_SCANNER__PLUGIN_DIR . '/vip-scanner' );
define( 'VIP_SCANNER_CHECKS_DIR',             VIP_SCANNER_DIR . '/checks' );
define( 'VIP_SCANNER_ANALYZERS_DIR',          VIP_SCANNER_DIR . '/analyzers' );
define( 'VIP_SCANNER_BIN_DIR',                VIP_SCANNER_DIR . '/bin' );

define( 'PHP_PARSER_BOOTSTRAP',               VIP_SCANNER__PLUGIN_DIR . '/vendor/PHP-Parser/lib/bootstrap.php' );

register_activation_hook( VIP_SCANNER__PLUGIN_DIR . '/vip-scanner.php', function() {
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
} else {
	wp_die( 'VIP-Scanner could not find PHP-Parser, which it requires to run. ' .
	        'Please refer to the "Requirements" section in readme.md. <br>(tl;dr
	        You probably need to run "git submodule update --init --recursive"
	        inside the vip-scanner folder in the plugins directory to fetch
	        the PHP-Parser submodule that is now required.)' );
}

spl_autoload_register( 'vip_scanner_autoload' );

require_once( VIP_SCANNER_DIR . '/class-vip-scanner.php' );
VIP_Scanner::get_instance();

require_once( VIP_SCANNER_DIR . '/config-vip-scanner.php' );

if ( defined('WP_CLI') && WP_CLI )
	require_once( VIP_SCANNER_DIR . '/class-wp-cli.php' );

if ( is_admin() ) {
	require_once( VIP_SCANNER_DIR . '/admin/class-vip-scanner-ui.php' );
	VIP_Scanner_UI::get_instance();
	VIP_Scanner_Async::get_instance();
}

function vip_scanner_autoload( $class_name ) {
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
}