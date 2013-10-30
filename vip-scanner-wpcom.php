<?php
/**
 * Plugin Name: VIP Scanner WordPress.com Rules
 * Description: Custom rules for the VIP Scanner specific to WordPress.com
 * Author: Automattic
 * Version: 0.4
 *
 * License: GPLv2
 */

add_action( 'vip_scanner_loaded', 'vip_scanner_custom_load_rules' );

function vip_scanner_custom_load_rules() {
	VIP_Scanner::get_instance()->register_review( 'VIP Theme Review', array(
		'VIPWhitelistCheck' => dirname( __FILE__ ) . '/vip-scanner-wpcom/checks/VIPWhitelistCheck.php',
		'VIPRestrictedPatternsCheck' => dirname( __FILE__ ) . '/vip-scanner-wpcom/checks/VIPRestrictedPatternsCheck.php',
		'VIPRestrictedCommandsCheck' => dirname( __FILE__ ) . '/vip-scanner-wpcom/checks/VIPRestrictedCommandsCheck.php',
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

add_action( 'vip_scanner_form', 'vip_scanner_form_fields', 10, 2 );
function vip_scanner_form_fields( $review, $blockers ) {

	if ( 'VIP Theme Review' != $review )
		return;

	$fields = get_transient( 'vip_scanner_flash_form_fields' );
	$required = function( $required ) {
		if ( ! isset( $_GET['message'] ) || 'fill-required-fields' != $_GET['message'] )
			return;

		if ( !$required )
			return;

		echo "required";
	}
	?>

	<p class="<?php $required( empty( $fields['name'] ) ); ?>">
		<label>
			<?php _e( 'Name of theme:', 'theme-check' ); ?> <small class="description require-label"><?php _e( '(required)', 'theme-check' ); ?></small><br>
			<input type=text name="name" value="<?php echo isset( $fields['name'] ) ? esc_attr( $fields['name'] ) : ''; ?>">
		</label>
	</p>

	<p class="<?php $required( empty( $fields['email'] ) ); ?>">
		<label>
			<?php $current_user = wp_get_current_user(); ?>
			<?php _e( 'Email:', 'theme-check' ); ?> <small class="require-label"><?php _e( '(required)', 'theme-check' ); ?></small><br>
			<input type=text name="email" value="<?php echo isset( $fields['email'] ) ? esc_attr( $fields['email'] ) : $current_user->user_email; ?>">
		</label>
	</p>

	<p class="<?php $required( empty( $fields['launch'] ) ); ?>">
		<label>
			<?php _e( 'Expected launch date:', 'theme-check' ); ?> <small class="require-label"><?php _e( '(required)', 'theme-check' ); ?></small><br>
			<input type=date name="launch" value="<?php echo isset( $fields['launch'] ) ? esc_attr( $fields['launch'] ) : ''; ?>">
		</label>
	</p>

	<p class="<?php $required( empty( $fields['description'] ) ); ?>">
		<label>
			<?php _e( 'Short description of theme:', 'theme-check' ); ?> <small class="require-label"><?php _e( '(required)', 'theme-check' ); ?></small><br>
			<textarea name="description"><?php echo isset( $fields['description'] ) ? sanitize_text_field( $fields['description'] ) : ''; ?></textarea>
		</label>
	</p>

	<p class="<?php $required( empty( $fields['architecture'] ) ); ?>">
		<label>
			<?php _e( 'Brief architectural overview:', 'theme-check' ); ?> <small class="require-label"><?php _e( '(required)', 'theme-check' ); ?></small><br>
			<textarea name="architecture"><?php echo isset( $fields['architecture'] ) ? sanitize_text_field( $fields['architecture'] ) : ''; ?></textarea>
		</label>
	</p>

	<p>
		<label>
			<?php _e( 'List of plugins this theme uses:', 'theme-check' ); ?><br>
			<textarea name="plugins"><?php echo isset( $fields['plugins'] ) ? sanitize_text_field( $fields['plugins'] ) : ''; ?></textarea>
		</label>
	</p>

	<p>
		<label>
			<?php _e( 'Is this code based off existing code? If so, give details:', 'theme-check' ); ?><br>
			<textarea name="derivative"><?php echo isset( $fields['derivative'] ) ? sanitize_text_field( $fields['derivative'] ) : ''; ?></textarea>
		</label>
	</p>

	<p>
		<label>
			<?php _e( 'Are there any external services, dependencies, or applications that utilize or rely on the site (e.g. mobile apps)? If so, how do these services interact with the site?', 'theme-check' ); ?><br>
			<textarea name="external"><?php echo isset( $fields['external'] ) ? sanitize_text_field( $fields['external'] ) : ''; ?></textarea>
		</label>
	</p>

	<?php if ( $blockers ): ?>
	<p class="<?php $required( empty( $fields['error_summary'] ) ); ?>">
		<?php _e( 'Since some errors were detected, please provide a clear and concise explanation of the results before submitting the theme for review.', 'theme-check' ); ?> <small class="require-label"><?php _e( '(required)', 'theme-check' ); ?></small><br>
		<textarea name="error_summary"><?php echo isset( $fields['error_summary'] ) ? sanitize_text_field( $fields['error_summary'] ) : ''; ?></textarea>
	</p>
	<?php endif; ?>

	<p class="<?php $required( !isset( $fields['gpl'] ) || !$fields['gpl'] ); ?>">
		<label>
			<input type=checkbox name="gpl" <?php checked( isset( $fields['gpl'] ) && $fields['gpl'] ); ?>> <?php _e( 'Code is GPL compatible or custom-code written in-house', 'theme-check' ); ?> <small class="require-label"><?php _e( '(required)', 'theme-check' ); ?></small>
		</label>
	</p>

	<p class="<?php $required( !isset( $fields['standards'] ) || !$fields['standards'] ); ?>">
		<label>
			<input type=checkbox name="standards" <?php checked( isset( $fields['standards'] ) && $fields['standards'] ); ?>> <?php _e( 'Code follows WordPress Coding Standards and properly escapes, santizes, and validates data', 'standards' ); ?> <small class="require-label"><?php _e( '(required)', 'theme-check' ); ?></small>
		</label>
	</p>
<?php }

add_filter( 'vip_scanner_form_results', 'vip_scanner_form_results', 10, 2 );
function vip_scanner_form_results( $results, $review ) {

	if ( 'VIP Theme Review' != $review )
		return;

	$required = array(
		'name',
		'email',
		'launch',
		'description',
		'architecture',
		'gpl',
		'standards',
		'error_summary',
	);

	$fields = array(
		'name'         => sanitize_text_field( $_POST['name'] ),
		'email'        => sanitize_email( $_POST['email'] ),
		'launch'       => sanitize_text_field( $_POST['launch'] ),
		'description'  => sanitize_text_field( $_POST['description'] ),
		'architecture' => sanitize_text_field( $_POST['architecture'] ),
		'plugins'      => sanitize_text_field( $_POST['plugins'] ),
		'derivative'   => sanitize_text_field( $_POST['derivative'] ),
		'external'     => sanitize_text_field( $_POST['external'] ),
		'gpl'          => isset( $_POST['gpl'] ),
		'standards'    => isset( $_POST['standards'] ),
		'error_summary'=> isset( $_POST['error_summary'] ) ? sanitize_text_field( $_POST['error_summary'] ) : true,
	);

	foreach ( $required as $r ) {
		if ( empty( $fields[$r] ) ) {
			$url = add_query_arg( array(
				'page' => 'vip-scanner',
				'message' => 'fill-required-fields',
				'vip-scanner-review-type' => urlencode( $review ),
			) );

			set_transient( 'vip_scanner_flash_form_fields', $fields );
			wp_safe_redirect( $url );
			exit;
		}
	}

	delete_transient( 'vip_scanner_flash_form_fields' );

	$email = $fields['email'];
	add_filter( 'vip_scanner_email_headers', function( $headers ) use ( $email ) {
		$headers[] = "Cc: $email";
		return $headers;
	} );

	// Name
	$results .= __( 'Name of theme:', 'theme-check' ) . ' ';
	$results .= $fields['name'] . PHP_EOL . PHP_EOL;

	// Launch Date
	$results .= __( 'Expected launch date:', 'theme-check' ) . ' ';
	$results .= $fields['launch'] . PHP_EOL . PHP_EOL;

	// Description
	$results .= '## ' . __( 'Short description of theme:', 'theme-check' ) . PHP_EOL;
	$results .= $fields['description'] . PHP_EOL . PHP_EOL;

	// Architectural overview
	$results .= '## ' . __( 'Brief architectural overview:', 'theme-check' ) . PHP_EOL;
	$results .= $fields['architecture'] . PHP_EOL . PHP_EOL;

	// Plugins
	$results .= '## ' . __( 'List of plugins this theme uses:', 'theme-check' ) . PHP_EOL;
	$results .= $fields['plugins'] . PHP_EOL . PHP_EOL;

	// Based off other code?
	$results .= '## ' . __( 'Is this code based off existing code? If so, give details:', 'theme-check' ) . PHP_EOL;
	$results .= $fields['derivative'] . PHP_EOL . PHP_EOL;

	// Dependencies?
	$results .= '## ' . __( 'Are there any external services, dependencies, or applications that utilize or rely on the site (e.g. mobile apps)? If so, how do these services interact with the site?', 'theme-check' ) . PHP_EOL;
	$results .= $fields['external'] . PHP_EOL . PHP_EOL;

	// GPL?
	$results .= '## ' . __( 'Code is GPL compatible or custom-code written in-house', 'theme-check' ) . PHP_EOL;
	$results .= $fields['gpl'] ? 'Yes' : 'No';
	$results .= PHP_EOL . PHP_EOL;

	// Coding standards?
	$results .= '## ' . __( 'Code follows WordPress Coding Standards and properly escapes, santizes, and validates data', 'theme-check' ) . PHP_EOL;
	$results .= $fields['standards'] ? 'Yes' : 'No';
	$results .= PHP_EOL . PHP_EOL;

	// Error summary of remaining issues
	if ( isset( $_POST['error_summary'] ) ) {
		$results .= "## Error Summary" . PHP_EOL;
		$results .= $fields['error_summary'] . PHP_EOL . PHP_EOL;
	}

	return $results;
}

add_action( 'admin_notices', 'vip_scanner_missing_required_fields' );
function vip_scanner_missing_required_fields() {
	if ( ! isset( $_GET['page'], $_GET['message'] ) || 'vip-scanner' != $_GET['page'] || 'fill-required-fields' != $_GET['message'] )
		return;
    ?>
    <div class="error">
        <p><strong><?php _e( 'Warning! Fill the required fields before submitting the form.', 'theme-check' ); ?></strong></p>
    </div>
    <?php
}
