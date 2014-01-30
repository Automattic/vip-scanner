<?php
/**
 * Plugin Name: VIP Scanner WordPress.com Rules
 * Description: Custom rules for the VIP Scanner specific to WordPress.com
 * Author: Automattic
 * Version: 0.4
 *
 * License: GPLv2
 */

require_once __DIR__ . '/vip-scanner/class.vip-scanner-form.php';

add_action( 'vip_scanner_loaded', 'vip_scanner_custom_load_rules' );

function vip_scanner_custom_load_rules() {
	VIP_Scanner::get_instance()->register_review( 'VIP Theme Review', array(
		'VIPWhitelistCheck' => dirname( __FILE__ ) . '/vip-scanner-wpcom/checks/VIPWhitelistCheck.php',
		'VIPRestrictedPatternsCheck' => dirname( __FILE__ ) . '/vip-scanner-wpcom/checks/VIPRestrictedPatternsCheck.php',
		'VIPRestrictedCommandsCheck' => dirname( __FILE__ ) . '/vip-scanner-wpcom/checks/VIPRestrictedCommandsCheck.php',
		'VIPInitCheck' => dirname( __FILE__ ) . '/vip-scanner-wpcom/checks/VIPInitCheck.php',
		'WordPressCodingStandardsCheck' => null,
		'ClamAVCheck' => null // Pass null to lookup the check normally
	) );
}

add_filter( 'vip_scanner_default_review', function( $default, $review_types ) {
	return array_search( 'VIP Theme Review', $review_types );
}, 10, 2 );

add_filter( 'vip_scanner_email_to', 'vip_scanner_email_to' );
function vip_scanner_email_to() {
	// Disabled email submission.
	// return 'vip-support@wordpress.com';
}

// VIP_Sanner_Form::add_field( $type, $name, $label, $review, $required = false );
$vip_scanner_theme_review = 'VIP Theme Review';
VIP_Scanner_Form::add_field( 'text', 'name', __( 'Name of theme', 'theme-check' ), $vip_scanner_theme_review, true );
VIP_Scanner_Form::add_field( 'email', 'email', __( 'Email', 'theme-check' ), $vip_scanner_theme_review, true );
VIP_Scanner_Form::add_field( 'date', 'launch', __( 'Expected launch date', 'theme-check' ), $vip_scanner_theme_review, true );
VIP_Scanner_Form::add_field( 'textarea', 'description', __( 'Short description of theme', 'theme-check' ), $vip_scanner_theme_review, true );
VIP_Scanner_Form::add_field( 'textarea', 'architecture', __( 'Brief architectural overview', 'theme-check' ), $vip_scanner_theme_review, true );
VIP_Scanner_Form::add_field( 'textarea', 'plugins', __( 'List of plugins this theme uses', 'theme-check' ), $vip_scanner_theme_review );
VIP_Scanner_Form::add_field( 'textarea', 'derivative', __( 'Is this code based off existing code? If so, give details', 'theme-check' ), $vip_scanner_theme_review );
VIP_Scanner_Form::add_field( 'textarea', 'external', __( 'Are there any external services, dependencies, or applications that utilize or rely on the site (e.g. mobile apps)? If so, how do these services interact with the site?', 'theme-check' ), $vip_scanner_theme_review );
VIP_Scanner_Form::add_field( 'checkbox', 'gpl', __( 'Code is GPL compatible or custom-code written in-house' ), $vip_scanner_theme_review, true );
VIP_Scanner_Form::add_field( 'checkbox', 'standards', __( 'Code follows WordPress Coding Standards and properly escapes, santizes, and validates data' ), $vip_scanner_theme_review, true );
VIP_Scanner_Form::add_field( 'textarea', 'error_summary', __( 'If errors were detected, please provide a clear and concise explanation of the results before submitting the theme for review' ), $vip_scanner_theme_review);

add_filter( 'vip_scanner_form_results', 'vip_scanner_form_results', 10, 2 );
function vip_scanner_form_results( $results, $review ) {

	if ( 'VIP Theme Review' != $review )
		return;

	if ( ! isset( $_POST['email'] ) )
		return;

	$email = sanitize_email( $_POST['email'] );
	add_filter( 'vip_scanner_email_headers', function( $headers ) use ( $email ) {
		$headers[] = "Cc: $email";
		return $headers;
	} );

	return $results;
}

add_action( 'vip_scanner_form_success', 'vip_theme_review_form_success' );
function vip_theme_review_form_success() {
	delete_transient( 'vip_theme_review_flash_form_fields' );
}
