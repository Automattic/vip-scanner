<?php
/**
 * Plugin Name: VIP Scanner WordPress.com Rules
 * Description: Custom rules for the VIP Scanner specific to WordPress.com
 * Author: Automattic
 * Version: 0.3
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

	<?php if ( $blockers ): ?>
	<p class="<?php $required( empty( $fields['error_summary'] ) ); ?>">
		<?php _e( 'Since some errors were detected, please provide a clear and concise explanation of the results before submitting the theme for review.', 'theme-check' ); ?> <small class="require-label"><?php _e( '(required)', 'theme-check' ); ?></small><br>
		<textarea name="error_summary"><?php echo isset( $fields['error_summary'] ) ? sanitize_text_field( $fields['error_summary'] ) : ''; ?></textarea>
	</p>
	<?php endif; ?>
<?php }

add_filter( 'vip_scanner_form_results', 'vip_scanner_form_results', 10, 2 );
function vip_scanner_form_results( $results, $review ) {

	if ( 'VIP Theme Review' != $review )
		return;

	$required = array(
		'error_summary',
	);

	$fields = array(
		'error_summary'=> isset( $_POST['error_summary'] ) ? sanitize_text_field( $_POST['error_summary'] ) : true,
	);

	foreach ( $required as $r ) {
		if ( empty( $fields[$r] ) ) {
			$url = add_query_arg( array(
				'page' => 'vip-scanner',
				'message' => 'fill-required-fields',
				'vip-scanner-review-type' => urlencode( $review ),
			) );

			wp_safe_redirect( $url );
			exit;
		}
	}

	$email = sanitize_email( $_POST['email'] );
	add_filter( 'vip_scanner_email_headers', function( $headers ) use ( $email ) {
		$headers[] = "Cc: $email";
		return $headers;
	} );

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
