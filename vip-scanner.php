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

require_once( dirname( __FILE__ ) . '/vip-scanner/vip-scanner.php' );

if ( defined('WP_CLI') && WP_CLI )
	require_once( dirname( __FILE__ ) . '/vip-scanner/class-wp-cli.php' );

if ( is_admin() ) {
	require_once( VIP_SCANNER_DIR . '/admin/class-vip-scanner-ui.php' );
	VIP_Scanner_UI::get_instance();
}
