<?php
/**
 * Plugin Name: VIP Scanner WordPress.com Rules
 * Description: Custom rules for the VIP Scanner specific to WordPress.com
 * Author: Automattic
 * Version: 0.3
 *
 * License: GPLv2
 */

add_action( 'vip_scanner_loaded', 'vip_scanner_custom_load_rules' );

function vip_scanner_custom_load_rules() {
	VIP_Scanner::get_instance()->register_review( 'VIP Theme Review', array(
		'VIPWhitelistCheck' => dirname( __FILE__ ) . '/vip-scanner-wpcom/checks/VIPWhitelistCheck.php',
		'VIPRestrictedPatternsCheck' => dirname( __FILE__ ) . '/vip-scanner-wpcom/checks/VIPRestrictedPatternsCheck.php',
		'VIPRestrictedCommandsCheck' => dirname( __FILE__ ) . '/vip-scanner-wpcom/checks/VIPRestrictedCommandsCheck.php',
	) );
}


add_action( 'vip_scanner_form', 'vip_scanner_form_fields', 10, 2 );
function vip_scanner_form_fields( $review, $blockers ) {

	if ( 'VIP Theme Review' != $review )
		return;

	?>
	<p>
		<label>
			<?php _e( 'Name of theme:', 'theme-check' ); ?><br>
			<input type=text name="name">
		</label>
	</p>

	<p>
		<label>
			<?php _e( 'Expected launch date:', 'theme-check' ); ?><br>
			<input type=date name="launch">
		</label>
	</p>

	<p>
		<label>
			<?php _e( 'Short description of theme:', 'theme-check' ); ?><br>
			<textarea name="description"></textarea>
		</label>
	</p>

	<p>
		<label>
			<?php _e( 'Brief architectural overview:', 'theme-check' ); ?><br>
			<textarea name="architecture"></textarea>
		</label>
	</p>

	<p>
		<label>
			<?php _e( 'List of plugins this theme uses:', 'theme-check' ); ?><br>
			<textarea name="plugins"></textarea>
		</label>
	</p>

	<p>
		<label>
			<?php _e( 'Is this code based off existing code? If so, give details:', 'theme-check' ); ?><br>
			<textarea name="derivative"></textarea>
		</label>
	</p>

	<p>
		<label>
			<?php _e( 'Are there any external services, dependencies, or applications that utilize or rely on the site (e.g. mobile apps)? If so, how do these services interact with the site?', 'theme-check' ); ?><br>
			<textarea name="external"></textarea>
		</label>
	</p>

	<?php if ( $blockers ): ?>
	<p>
		<?php _e( 'Since some errors were detected, please provide a clear and concise explanation of the results before submitting the theme for review.', 'theme-check' ); ?><br>
		<textarea name="summary"></textarea>
	</p>
	<?php endif; ?>

	<p>
		<label>
			<input type=checkbox name="gpl"> <?php _e( 'Code is GPL compatible or custom-code written in-house', 'theme-check' ); ?>
		</label>
	</p>

	<p>
		<label>
			<input type=checkbox name="standards"> <?php _e( 'Code follows WordPress Coding Standards and properly escapes, santizes, and validates data', 'standards' ); ?>
		</label>
	</p>
<?php }

add_filter( 'vip_scanner_form_results', 'vip_scanner_form_results', 10, 2 );
function vip_scanner_form_results( $results, $review ) {

	if ( 'VIP Theme Review' != $review )
		return;

	$name         = sanitize_text_field( $_POST['name'] );
	$launch       = sanitize_text_field( $_POST['launch'] );
	$description  = sanitize_text_field( $_POST['description'] );
	$architecture = sanitize_text_field( $_POST['architecture'] );
	$plugins      = sanitize_text_field( $_POST['plugins'] );
	$derivative   = sanitize_text_field( $_POST['derivative'] );
	$external     = sanitize_text_field( $_POST['external'] );
	$gpl          = isset( $_POST['gpl'] );
	$standards    = isset( $_POST['standards'] );

	// Name
	$results .= __( 'Name of theme:', 'theme-check' ) . ' ';
	$results .= $name . PHP_EOL . PHP_EOL;

	// Launch Date
	$results .= __( 'Expected launch date:', 'theme-check' ) . ' ';
	$results .= $launch . PHP_EOL . PHP_EOL;

	// Description
	$results .= __( 'Short description of theme:', 'theme-check' ) . PHP_EOL;
	$results .= wordwrap( $description, 110 ) . PHP_EOL . PHP_EOL;

	// Architectural overview
	$results .= __( 'Brief architectural overview:', 'theme-check' ) . PHP_EOL;
	$results .= wordwrap( $architecture, 110 ) . PHP_EOL . PHP_EOL;

	// Plugins
	$results .= __( 'List of plugins this theme uses:', 'theme-check' ) . PHP_EOL;
	$results .= wordwrap( $plugins, 110 ) . PHP_EOL . PHP_EOL;

	// Based off other code?
	$results .= __( 'Is this code based off existing code? If so, give details:', 'theme-check' ) . PHP_EOL;
	$results .= wordwrap( $derivative, 110 ) . PHP_EOL . PHP_EOL;

	// Dependencies?
	$results .= __( 'Are there any external services, dependencies, or applications that utilize or rely on the site (e.g. mobile apps)? If so, how do these services interact with the site?', 'theme-check' ) . PHP_EOL;
	$results .= wordwrap( $external, 110 ) . PHP_EOL . PHP_EOL;

	// GPL?
	$results .= __( 'Code is GPL compatible or custom-code written in-house', 'theme-check' ) . PHP_EOL;
	$results .= $gpl ? 'Yes' : 'No';
	$results .= PHP_EOL . PHP_EOL;

	// Coding standards?
	$results .= __( 'Code follows WordPress Coding Standards and properly escapes, santizes, and validates data', 'theme-check' ) . PHP_EOL;
	$results .= $standards ? 'Yes' : 'No';
	$results .= PHP_EOL . PHP_EOL;

	// Summary of remaining issues
	if ( isset( $_POST['summary'] ) ) {
		$results .= "## Summary" . PHP_EOL;
		$results .= wordwrap( strip_tags( $_POST['summary'] ?: 'No summary given' ), 110 ) . PHP_EOL . PHP_EOL;
	}

	return $results;
}
