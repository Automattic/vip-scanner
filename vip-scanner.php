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

register_activation_hook( VIP_SCANNER__PLUGIN_DIR . '/class.vip-scanner.php', function() {
	if ( ! file_exists( PHP_PARSER_BOOTSTRAP ) ) {
		wp_die( esc_html__( 'VIP-Scanner could not find PHP-Parser, which it requires to run. Please refer to the "Requirements" section in readme.md.', 'vip-scanner' ) );
	}
} );

/**
 * To avoid a name collision, verify that the PHP-Parser has not been already loaded, for example by another plugin.
 */
if ( class_exists( 'PhpParser\Parser' ) ) {
	wp_die( esc_html__( 'A PHP-Parser instance was loaded before VIP-scanner. If another plugin uses PHP-Parser, please deactivate it.', 'vip-scanner' ) );
}

/**
 * Verify that the PHP-Parser bootstrap file exists and load the parser.
 */
if ( ! file_exists( PHP_PARSER_BOOTSTRAP ) ) {
	wp_die(
		esc_html__( 'VIP-Scanner could not find PHP-Parser, which it requires to run. Please refer to the "Requirements" section in readme.md.', 'vip-scanner' ) . '<br />' .
		esc_html__( 'tl;dr: You probably need to run "git submodule update --init --recursive" inside the vip-scanner folder in the plugins directory to fetch the PHP-Parser submodule that is now required.' , 'vip-scanner' )
	);
} else {
	require_once PHP_PARSER_BOOTSTRAP;
}

spl_autoload_register( 'vip_scanner_autoload' );

/**
 * Load the VIP Scanner.
 */
require_once( VIP_SCANNER_DIR . '/class.vip-scanner.php' );

/**
 * Load the review types. Depends on the VIP Scanner being loaded.
 */
require_once( VIP_SCANNER_DIR . '/config-vip-scanner.php' );

/**
 * Load the CLI command. Depends on the VIP Scanner and the configuration being loaded.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once( VIP_SCANNER_DIR . '/class-wp-cli.php' );
}

/**
 * Initialize admin.
 */
if ( is_admin() ) {
	VIP_Scanner_UI::get_instance();
	VIP_Scanner_Async::get_instance();
}

function vip_scanner_autoload( $class_name ) {
		// Handle classes that aren't named according to the common scheme.
		$other = array(
			'VIP_PregFile'      => 'class-preg-file.php',
			'VIP_Scanner_Async' => 'vip-scanner-async.php',
			'AnalyzedPHPFile'   => 'class-analyzed-php-file.php',
			'AnalyzedCSSFile'   => 'class-analyzed-css-file.php',
			'ElementGroup'      => 'elements/class-element-group.php',
			'VIP_Scanner_UI'    => 'admin/class.vip-scanner-ui.php',
		);

		if ( array_key_exists( $class_name, $other ) ) {
			require VIP_SCANNER_DIR . '/' . $other[ $class_name ];

			return;
		}

		// Example: $class_name === 'ThemeScanner'
		// Example: theme-scanner
		$hyphenated_name = strtolower( preg_replace('/([a-z])([A-Z])/', '$1-$2', $class_name ) );
		// Example: scanner
		$category = substr( strrchr( $hyphenated_name, '-' ), 1 );
		// Example: scanners
		$plural = substr( $category, -1 ) === 'y' ? substr( $category, 0, -1 ) . 'ies' : $category . 's';

		$possible_locations = array(
			// Example: vip-scanner/scanners/class-theme-scanner.php
			VIP_SCANNER_DIR . '/' . $plural . '/class-' . $hyphenated_name . '.php',
			// Example: vip-scanner/class.theme-scanner.php
			VIP_SCANNER_DIR . '/class.' . $hyphenated_name . '.php',
			// Example: vip-scanner/class-theme-scanner.php
			VIP_SCANNER_DIR . '/class-' . $hyphenated_name . '.php',
		);

		foreach( $possible_locations as $location ) {
			if ( file_exists( $location ) ) {
				require $location ;

				return;
			}
		}
}