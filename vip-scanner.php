<?php
/*
Plugin Name: VIP Scanner
Plugin URI: http://vip.wordpress.com
Description: Easy to use UI for the VIP Scanner.
Author: Automattic (Original code by Pross, Otto42, and Thorsten Ott)
Version: 0.7

License: GPLv2
*/
require_once( dirname( __FILE__ ) . '/vip-scanner/vip-scanner.php' );

if ( defined('WP_CLI') && WP_CLI )
	require_once( dirname( __FILE__ ) . '/vip-scanner/class-wp-cli.php' );

class VIP_Scanner_UI {
	const   key      = 'vip-scanner';
	private $version = null;

	public $default_review;
	private static $instance;
	private $blocker_types;

	private $to;

	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	function init() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );

		$this->blocker_types = apply_filters( 'vip_scanner_blocker_types', array(
			'blocker'  => __( 'Blockers', 'theme-check' ),
			'warning'  => __( 'Warnings', 'theme-check' ),
			'required' => __( 'Required', 'theme-check' ),
		) );

		do_action( 'vip_scanner_loaded' );

		$review_types = VIP_Scanner::get_instance()->get_review_types();
		$this->default_review = apply_filters( 'vip_scanner_default_review', 0, $review_types );

		$this->to = apply_filters( 'vip_scanner_email_to', '' );
	}

	function admin_init() {
		if ( isset( $_POST['page'], $_POST['action'] ) && $_POST['page'] == self::key && $_POST['action'] == 'Export' )
			$this->export();

		if ( isset( $_POST['page'], $_POST['action'] ) && $_POST['page'] == self::key && $_POST['action'] == 'Submit' )
			$this->submit();

		// Handle admin notices
		if ( isset( $_GET['page'], $_GET['message'] ) && self::key == $_GET['page'] ) {

			switch( $_GET['message'] ) {
				case 'fail':
					add_action( 'admin_notices', array( $this, 'admin_notice_fail' ) );
					break;

				case 'success':
					add_action( 'admin_notices', array( $this, 'admin_notice_success' ) );
					break;
			}
		}
	}

	static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			$class_name = __CLASS__;
			self::$instance = new $class_name;
		}
		return self::$instance;
	}

	function get_version() {
		if ( is_null( $this->version ) ) {
			// Load plugin version from plugin data
			$plugin_data = get_plugin_data( __FILE__ );
			$this->version = $plugin_data['Version'];
		}

		return $this->version;
	}

	function add_menu_page() {
		$submenu_page = apply_filters( 'vip_scanner_submenu_page', 'tools.php' );
		$hook = add_submenu_page( $submenu_page, 'VIP Scanner', 'VIP Scanner', 'manage_options', self::key, array( $this, 'display_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	function admin_enqueue_scripts( $hook ) {
		if ( ! isset( $_GET['page'] ) || 'vip-scanner' != $_GET['page'] )
			return;

		wp_enqueue_style( 'vip-scanner-css', plugins_url( 'css/vip-scanner.css', __FILE__ ), array(), '20120320' );
		wp_enqueue_script( 'vip-scanner-js', plugins_url( 'js/vip-scanner.js', __FILE__ ), array('jquery'), '20120320' );
		wp_enqueue_script( 'jquery-ui-accordion');
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_style('vip-scanner-admin-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/smoothness/jquery-ui.min.css');
	}

	function display_admin_page() {
		global $title;

		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		?>
		<div id="vip-scanner" class="wrap">
			<?php screen_icon( 'themes' ); ?>
			<h2><?php echo esc_html( $title ); ?></h2>
			<div class="scanner-wrapper">
			<?php $this->do_theme_review(); ?>
			</div>
		</div>
		<?php
	}

	function display_vip_scanner_form() {
		$themes = wp_get_themes();
		$review_types = VIP_Scanner::get_instance()->get_review_types();
		$current_theme = isset( $_GET[ 'vip-scanner-theme-name' ] ) ? sanitize_text_field( $_GET[ 'vip-scanner-theme-name' ] ) : get_stylesheet();
		$current_review = isset( $_GET[ 'vip-scanner-review-type' ] ) ? sanitize_text_field( $_GET[ 'vip-scanner-review-type' ] ) : $review_types[ $this->default_review ]; // TODO: eugh, need better error checking
		?>
		<form method="GET">
			<input type="hidden" name="page" value="<?php echo self::key; ?>" />
			<select name="vip-scanner-review-type">
				<?php foreach ( $review_types as $review_type ) : ?>
					<option <?php selected( $current_review, $review_type ); ?> value="<?php echo esc_attr( $review_type ); ?>"><?php echo esc_html( $review_type ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php submit_button( 'Scan', 'primary', false, false ); ?>
		</form>
		<?php
	}

	function do_theme_review() {
		$theme = get_stylesheet();
		$review_types = VIP_Scanner::get_instance()->get_review_types();
		$review = isset( $_GET[ 'vip-scanner-review-type' ] ) ? sanitize_text_field( $_GET[ 'vip-scanner-review-type' ] ) : $review_types[ $this->default_review ]; // TODO: eugh, need better error checking

		$scanner = VIP_Scanner::get_instance()->run_theme_review( $theme, $review );

		$transient_key = 'vip_scanner_' . $this->get_version() . '_' . md5( $theme . $review );
		if ( $scanner !== get_transient( $transient_key ) )
			@set_transient( $transient_key, $scanner );

		if ( $scanner ):
			$this->display_theme_review_result( $scanner, $theme );
			?>

			<h2>Export Theme for VIP Review</h2>

			<form method="POST" class="export-form">

				<?php do_action( 'vip_scanner_form', $review, count( $scanner->get_errors( array_keys( $this->blocker_types ) ) ) ); ?>

				<p>
					<?php
						// hide submit button if $to is empty
						if ( !empty( $this->to ) )
							submit_button( __( 'Submit', 'theme-check' ), 'primary', 'action', false );
					?>
					<?php submit_button( __( 'Export', 'theme-check' ), 'secondary', 'action', false ); ?>
				</p>

				<?php wp_nonce_field( 'export' ); ?>
				<input type="hidden" name="review" value="<?php echo esc_attr( $review ) ?>">
				<input type="hidden" name="page" value="<?php echo self::key; ?>">
			</form>

		<?php
		else:
			$this->display_scan_error();
		endif;
	}

	function display_theme_review_result( $scanner, $theme ) {
		global $SyntaxHighlighter;
		if ( isset( $SyntaxHighlighter ) ) {
			add_action( 'admin_footer', array( &$SyntaxHighlighter, 'maybe_output_scripts' ) );
		}

		$report       = $scanner->get_results();

		$error_levels = $scanner->get_error_levels();
		$note_types   = array_diff( $error_levels, array_keys( $this->blocker_types ) );

		$blockers     = $scanner->get_errors( array_keys( $this->blocker_types ) );
		$notes        = count( $note_types ) ? $scanner->get_errors( $note_types ) : array();

		$errors       = count( $blockers );
		$notes        = count( $notes );;
		$pass         = !$errors;

		?>
		<div class="scan-info">
			<span>Scanned Theme: <span class="theme-name"><?php echo $theme; ?></span></span>
			<?php $this->display_vip_scanner_form(); ?>
		</div>

		<div class="scan-report">
			<div class="scan-results result-<?php echo $pass ? 'pass' : 'fail'; ?>"><?php echo $pass ? __( 'Passed the Scan with no errors!', 'theme-check' ) : __( 'Failed to pass Scan', 'theme-check' ); ?></div>

			<table class="scan-results-table">
				<tr>
					<th><?php _e( 'Total Files', 'theme-check' ); ?></th>
					<td><?php echo intval( $report['total_files'] ); ?></td>
				</tr>
				<tr>
					<th><?php _e( 'Total Checks', 'theme-check' ); ?></th>
					<td><?php echo intval( $report['total_checks'] ); ?></td>
				</tr>
			</table>
		</div>

		<h2 class="nav-tab-wrapper">
			<a href="#errors" class="nav-tab"><?php echo absint( $errors ); ?> <?php _e( 'Errors', 'theme-check' ); ?></a>
			<a href="#notes" class="nav-tab"><?php echo absint( $notes ); ?> <?php _e( 'Notes', 'theme-check' ); ?></a>
			<a href="#analysis" class="nav-tab"><?php _e( 'Analysis', 'theme-check' ); ?></a>
		</h2>

		<div id="errors">
			<?php foreach( $this->blocker_types as $type => $title ):
				$errors = $scanner->get_errors( array( $type ) );

				if ( ! count( $errors ) )
					continue;
				?>
				<h3><?php echo esc_html( $title ); ?></h3>
				<ol class="scan-results-list">
					<?php
					foreach( $errors as $result ) {
						$this->display_theme_review_result_row( $result, $scanner, $theme );
					}
					?>
				</ol>
			<?php endforeach; ?>
		</div>

		<div id="notes">
			<?php foreach( $note_types as $type ):
				$errors = $scanner->get_errors( array( $type ) );
				$title = ucfirst( $type . 's' );

				if ( ! count( $errors ) )
					continue;
				?>
				<h3><?php echo esc_html( $title ); ?></h3>
				<ul class="analysis-results-list">
					<?php
					foreach( $errors as $result ) {
						$this->display_theme_review_result_row( $result, $scanner, $theme );
					}
					?>
				</ul>
			<?php endforeach; ?>
		</div>
			
		<div id="analysis">
			<div id="analysis-accordion">
				<?php 
				$empty = array();
				foreach ( $scanner->renderers as $renderer ) {
					if ( $renderer->name() !== 'Files' ) {
						$renderer->analyze_prefixes();
					}
					
					// Display empty renderers after the others
					if ( $renderer->is_empty() ) {
						$empty[] = $renderer;
						continue;
					}

					$renderer->display();
				}
				
				foreach ( $empty as $renderer ) {
					$renderer->display();
				}
				
				?>
			</div>
		</div>
		<?php
	}

	function display_theme_review_result_row( $error, $scanner, $theme ) {
		global $SyntaxHighlighter;

		$level = $error['level'];
		$description = $error['description'];

		$file = '';
		if ( is_array( $error['file'] ) ) {
			if ( ! empty( $error['file'][0] ) )
				$file .= $error['file'][0];
			if ( ! empty( $error['file'][1] ) )
				$file .= ': ' . $error['file'][1];
		} else if ( ! empty( $error['file'] ) ) {
			$file_full_path = $error['file'];
			$file_theme_path = substr( $file_full_path, strrpos( $file_full_path, sprintf( '/%s/', $theme ) ) );
			$file = strrchr( $file_full_path, sprintf( '/%s/', $theme ) );
			if ( ! $file && ! empty( $file_theme_path ) )
				$file = $file_theme_path;
		}

		$lines = ! empty( $error['lines'] ) ? $error['lines'] : array();

		?>
		<li class="scan-result-<?php echo strtolower( $level ); ?>">
			<span class="scan-description"><?php echo $description; ?></span>

			<?php if( ! empty( $file ) ) : ?>
				<span class="scan-file">
					<?php echo $file; ?>
				</span>
			<?php endif; ?>

			<?php if( ! empty( $lines ) ) : ?>
				<div class="scan-lines">
				<?php foreach( $lines as $line ) : ?>
					<div class="scan-line">
						<?php
						if ( isset( $SyntaxHighlighter ) ) {
							// TODO: Should detect file type and set appropriate brush
							$line_shortcode = '[sourcecode language="php" htmlscript="true" light="true"]' . html_entity_decode( $line ) . '[/sourcecode]';
							echo $SyntaxHighlighter->parse_shortcodes( $line_shortcode );
						} else {
							echo '<pre>' . html_entity_decode( $line ) . '</pre>';
						}
						?>
					</div>
				<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</li>
		<?php
	}

	function get_plaintext_theme_review_export( $scanner, $theme, $review ) {
		$results = "";

		$results .= $title = apply_filters( 'vip_scanner_export_title', "$theme - $review", $review ) . PHP_EOL;
		$title_len = strlen( $title );
		$results .= str_repeat( '=', $title_len ) . PHP_EOL;

		$version_str = ' ' . sprintf( __( 'VIP Scanner %s', 'theme-check' ), $this->get_version() ) . ' ';
		$side_spacing = ( $title_len - strlen( $version_str ) ) / 2.;
		$results .= str_repeat( '=', ceil( $side_spacing ) ) . $version_str . str_repeat( '=', floor( $side_spacing ) ) . PHP_EOL;
		$results .= str_repeat( '=', $title_len ) . PHP_EOL . PHP_EOL;

		$form_results = apply_filters( 'vip_scanner_form_results', '', $review );

		if ( !empty( $form_results ) ) {
			$results .= $form_results;
			$results .= str_repeat( '-', 25 ) . ' Scanner Results ' . str_repeat( '-', 25 ) . PHP_EOL . PHP_EOL;
		}

		$report   = $scanner->get_results();
		$blockers = count( $scanner->get_errors( array_keys( $this->blocker_types ) ) );

		$results .= __( 'Total Files', 'theme-check' );
		$results .= ':  ';
		$results .= intval( $report['total_files'] );
		$results .= PHP_EOL;

		$results .= __( 'Total Checks', 'theme-check' );
		$results .= ': ';
		$results .= intval( $report['total_checks'] );
		$results .= PHP_EOL;

		$results .= __( 'Errors', 'theme-check' );
		$results .= ':       ';
		$results .= intval( $blockers );
		$results .= PHP_EOL;

		$results .= PHP_EOL;

		foreach( $this->blocker_types as $type => $title ) {
			$errors = $scanner->get_errors( array( $type ) );

			if ( ! count( $errors ) )
				continue;

			$results .= "## " . esc_html( $title ) . PHP_EOL;

			foreach ( $errors as $result )
				$results .= $this->get_plaintext_result_row( $result, $theme ) . PHP_EOL;

			$results .= PHP_EOL;
		}

		return $results;
	}

	function get_plaintext_result_row( $error, $theme ) {
		$description = $error['description'];

		$file = '';
		if ( is_array( $error['file'] ) ) {
			if ( ! empty( $error['file'][0] ) )
				$file .= $error['file'][0];
			if ( ! empty( $error['file'][1] ) )
				$file .= ': ' . $error['file'][1];
		} else if ( ! empty( $error['file'] ) ) {
			$file_full_path = $error['file'];
			$file_theme_path = substr( $file_full_path, strrpos( $file_full_path, sprintf( '/%s/', $theme ) ) );
			$file = strrchr( $file_full_path, sprintf( '/%s/', $theme ) );
			if ( ! $file && ! empty( $file_theme_path ) )
				$file = $file_theme_path;
		}

		$line = "";

		if ( $file )
			$line .= "$file - ";

		$line .= $description;

		return $this->format_plaintext_row( "* $line" );
	}

	function format_plaintext_row( $row ) {
		// Markdown code
		$row = str_replace( array( '<var>', '</var>', '<code>', '</code>' ), '`', $row );

		// Markdown <em>
		$row = str_replace( array( '<em>', '</em>', '<i>', '</i>' ), '*', $row );

		// Markdown <strong>
		$row = str_replace( array( '<strong>', '</strong>', '<b>', '</b>' ), '**', $row );

		$row = strip_tags( $row );
		return $row;
	}

	function display_scan_error() {
		echo 'Uh oh! Looks like something went wrong :(';
	}

	function get_cached_theme_review( $theme, $review ) {
		$transient_key = 'vip_scanner_' . $this->get_version() . '_' . md5( $theme . $review );

		if ( false === $scanner = get_transient( $transient_key ) ) {
			$scanner = VIP_Scanner::get_instance()->run_theme_review( $theme, $review );
			@set_transient( $transient_key, $scanner );
		}

		return $scanner;
	}

	function export() {

		// Check nonce and permissions
		check_admin_referer( 'export' );

		if ( ! isset( $_POST['review'] ) )
			return;

		$theme = get_stylesheet();
		$review = sanitize_text_field( $_POST[ 'review' ] );
		$scanner = $this->get_cached_theme_review( $theme, $review );

		if ( $scanner ) {
			$filename = date( 'Ymd' ) . '.' . $theme . '.' . $review . '.VIP-Scanner.txt';
			header( 'Content-Type: text/plain' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

			echo $this->get_plaintext_theme_review_export( $scanner, $theme, $review );

			do_action( 'vip_scanner_form_success' );

			exit;
		}

		// redirect with error message
		$url = add_query_arg( array(
			'page' => self::key,
			'message' => 'fail',
			'vip-scanner-review-type' => urlencode( $review ),
		) );

		wp_safe_redirect( $url );
		exit;
	}

	function submit() {
		$mail = false;

		// Check nonce and permissions
		check_admin_referer( 'export' );

		if ( ! isset( $_POST['review'] ) )
			return;

		$theme = get_stylesheet();
		$review = sanitize_text_field( $_POST[ 'review' ] );
		$scanner = $this->get_cached_theme_review( $theme, $review );

		$message = $this->get_plaintext_theme_review_export( $scanner, $theme, $review );
		$subject = apply_filters( 'vip_scanner_email_subject', "[VIP Scanner] $theme - $review", $theme, $review );
		$headers = apply_filters( 'vip_scanner_email_headers', array() );

		if ( $scanner && !empty( $this->to ) ) {
			$zip = self::create_zip();

			// redirect with error message
			if ( !$zip )
				return;

			$mail = wp_mail(
				$this->to,
				$subject,
				$message,
				$headers,
				array( $zip )
			);

			unlink( $zip );
		}

		$args = array(
			'page' => self::key,
			'message' => 'success',
			'vip-scanner-review-type' => urlencode( $review ),
		);

		// Error message if the wp_mail didn't work
		if ( !$mail ) {
			$args['message'] = 'fail';
		} else {
			do_action( 'vip_scanner_form_success' );
		}

		wp_safe_redirect( add_query_arg( $args ) );
		exit;
	}

	private static function create_zip( $directory = '', $name = '', $overwrite = true ) {
		if ( empty( $directory ) )
			$directory = get_stylesheet_directory();

		if ( empty( $name ) ) {
			$stylesheet = explode( '/', get_stylesheet() );
			$stylesheet = $stylesheet[count( $stylesheet ) - 1];
			$name = $stylesheet . '.' . date( 'Y-m-d' ) . '.zip';
		}

		$upload_dir = wp_upload_dir();
		$destination = $upload_dir['basedir'] . '/' . $name;

		if ( ! is_dir( $directory ) )
			return;

		if ( file_exists( $destination ) && !$overwrite )
			return false;

		$zip = new ZipArchive();

		if( $zip->open( $destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE ) !== true )
			return false;

		// Iterative BFS algo to add all files to zip
		$dirs = array( $directory );
		while ( !empty( $dirs ) ) {
			$glob = array_shift( $dirs ) . '/*';
			foreach ( glob( $glob ) as $file ) {

				// Don't add directories to the zip
				if ( is_dir( $file ) )
					continue;

				$local_path = ltrim( str_replace( $directory, '', $file ), '/' );
				$zip->addFile( $file, $local_path );
			}

			// Find all sub directories
			$dirs = array_merge( $dirs, glob( $glob, GLOB_ONLYDIR ) );
		}

		$zip->close();

		return file_exists( $destination ) ? $destination : false;
	}

	function admin_notice_fail() {
		if ( ! isset( $_GET['page'], $_GET['message'] ) || 'vip-scanner' != $_GET['page'] || 'fail' != $_GET['message'] )
			return;
	    ?>
	    <div class="error">
	        <p><strong><?php _e( 'Fail! Something broke.', 'theme-check' ); ?></strong></p>
	    </div>
	    <?php
	}

	function admin_notice_success() {
		if ( ! isset( $_GET['page'], $_GET['message'] ) || 'vip-scanner' != $_GET['page'] || 'success' != $_GET['message'] )
			return;
	    ?>
	    <div class="updated">
	        <p><strong><?php _e( 'Success!', 'theme-check' ); ?></strong></p>
	    </div>
	    <?php
	}
}

// Initialize!
$vip_scanner = VIP_Scanner_UI::get_instance();
