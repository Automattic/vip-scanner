<?php
/*
Plugin Name: VIP Scanner
Plugin URI: http://vip.wordpress.com
Description: Easy to use UI for the VIP Scanner.
Author: Automattic (Original code by Pross, Otto42, and Thorsten Ott)
Version: 0.3

License: GPLv2
*/
require_once( dirname( __FILE__ ) . '/vip-scanner/vip-scanner.php' );

class VIP_Scanner_UI {
	const key = 'vip-scanner';

	private static $instance;
	private $blocker_types;

	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		do_action( 'vip_scanner_loaded' );

		$this->blocker_types = apply_filters( 'vip_scanner_blocker_types', array(
			'blocker'  => __( 'Blockers', 'theme-check' ),
			'warning'  => __( 'Warnings', 'theme-check' ),
			'required' => __( 'Required', 'theme-check' ),
		) );
	}

	function init() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
	}

	function admin_init() {
		if ( isset( $_POST['page'], $_POST['action'] ) && $_POST['page'] == self::key && $_POST['action'] == 'Export' )
			$this->export();

		if ( isset( $_POST['page'], $_POST['action'] ) && $_POST['page'] == self::key && $_POST['action'] == 'Submit' )
			$this->submit();
	}

	static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			$class_name = __CLASS__;
			self::$instance = new $class_name;
		}
		return self::$instance;
	}

	function add_menu_page() {
		$hook = add_submenu_page( 'tools.php', 'VIP Scanner', 'VIP Scanner', 'manage_options', self::key, array( $this, 'display_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	function admin_enqueue_scripts( $hook ) {
		if ( 'tools_page_' . self::key !== $hook )
			return;

		wp_enqueue_style( 'vip-scanner-css', plugins_url( 'css/vip-scanner.css', __FILE__ ), array(), '20120320' );
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
			<?php $this->do_theme_review(); ?>
		</div>
		<?php
	}

	function display_vip_scanner_form() {
		$themes = wp_get_themes();
		$review_types = VIP_Scanner::get_instance()->get_review_types();
		$current_theme = isset( $_GET[ 'vip-scanner-theme-name' ] ) ? sanitize_text_field( $_GET[ 'vip-scanner-theme-name' ] ) : get_stylesheet();
		$current_review = isset( $_GET[ 'vip-scanner-review-type' ] ) ? sanitize_text_field( $_GET[ 'vip-scanner-review-type' ] ) : $review_types[0]; // TODO: eugh, need better error checking
		?>
		<form method="GET">
			<input type="hidden" name="page" value="<?php echo self::key; ?>" />
			<select name="vip-scanner-review-type">
				<?php foreach ( $review_types as $review_type ) : ?>
					<option <?php selected( $current_review, $review_type ); ?> value="<?php echo esc_attr( $review_type ); ?>"><?php echo esc_html( $review_type ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php submit_button( 'Check it!', 'primary', false, false ); ?>
		</form>
		<?php
	}

	function do_theme_review() {
		$theme = get_stylesheet();
		$review_types = VIP_Scanner::get_instance()->get_review_types();
		$review = isset( $_GET[ 'vip-scanner-review-type' ] ) ? sanitize_text_field( $_GET[ 'vip-scanner-review-type' ] ) : $review_types[0]; // TODO: eugh, need better error checking

		$scanner = VIP_Scanner::get_instance()->run_theme_review( $theme, $review );
		if ( $scanner ):
			$this->display_theme_review_result( $scanner, $theme );
			?>

			<h2>Export Theme for VIP Review</h2>

			<form method="POST" class="export-form">
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
				<?php if ( count( $scanner->get_errors( $this->blocker_types ) ) ): ?>
				<p>
					<?php _e( 'Since some errors were detected, please provide a clear and concise explanation of the results before submitting the theme for review.', 'theme-check' ); ?><br>
					<textarea name="summary"></textarea>
				</p>
				<?php endif; ?>

				<p>
					<?php submit_button( __( 'Submit', 'theme-check' ), 'primary', 'action', false ); ?>
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

		$report   = $scanner->get_results();
		$blockers = $scanner->get_errors( array_keys( $this->blocker_types ) );
		$pass     = ! count( $blockers );
		$errors   = count($blockers);
		$notes    = count($scanner->get_errors()) - $errors;
		
		?>
		<div class="scan-info">
			Scanned Theme: <span class="theme-name"><?php echo $theme; ?></span>
			<?php $this->display_vip_scanner_form(); ?>
		</div>
		
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
		
		<h2 class="nav-tab-wrapper"><?php // Note: These are static tabs ?>
			<a href="#" class="nav-tab nav-tab-active"><?php echo $errors; ?> <?php echo __( 'Errors', 'theme-check' ); ?></a>
			<a href="#" class="nav-tab"><?php echo $notes; ?> <?php echo __( 'Notes', 'theme-check' ); ?></a>
		</h2>

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

		$name         = sanitize_text_field( $_POST['name'] );
		$launch       = sanitize_text_field( $_POST['launch'] );
		$description  = sanitize_text_field( $_POST['description'] );
		$architecture = sanitize_text_field( $_POST['architecture'] );
		$plugins      = sanitize_text_field( $_POST['plugins'] );
		$derivative   = sanitize_text_field( $_POST['derivative'] );
		$external     = sanitize_text_field( $_POST['external'] );
		$gpl          = isset( $_POST['gpl'] );
		$standards    = isset( $_POST['standards'] );

		$title = "$name - $review";

		$results .= $title . PHP_EOL;
		$results .= str_repeat( '=', strlen( $title ) ) . PHP_EOL . PHP_EOL;

		$results .= __( 'Name of theme:', 'theme-check' ) . ' ';
		$results .= $name . PHP_EOL . PHP_EOL;

		$results .= __( 'Expected launch date:', 'theme-check' ) . ' ';
		$results .= $launch . PHP_EOL . PHP_EOL;

		$results .= __( 'Short description of theme:', 'theme-check' ) . PHP_EOL;
		$results .= wordwrap( $description, 110 ) . PHP_EOL . PHP_EOL;

		$results .= __( 'Brief architectural overview:', 'theme-check' ) . PHP_EOL;
		$results .= wordwrap( $architecture, 110 ) . PHP_EOL . PHP_EOL;

		$results .= __( 'List of plugins this theme uses:', 'theme-check' ) . PHP_EOL;
		$results .= wordwrap( $plugins, 110 ) . PHP_EOL . PHP_EOL;

		$results .= __( 'Is this code based off existing code? If so, give details:', 'theme-check' ) . PHP_EOL;
		$results .= wordwrap( $derivative, 110 ) . PHP_EOL . PHP_EOL;

		$results .= __( 'Are there any external services, dependencies, or applications that utilize or rely on the site (e.g. mobile apps)? If so, how do these services interact with the site?', 'theme-check' ) . PHP_EOL;
		$results .= wordwrap( $external, 110 ) . PHP_EOL . PHP_EOL;

		$results .= __( 'Code is GPL compatible or custom-code written in-house', 'theme-check' ) . PHP_EOL;
		$results .= $gpl ? 'Yes' : 'No';
		$results .= PHP_EOL . PHP_EOL;

		$results .= __( 'Code follows WordPress Coding Standards and properly escapes, santizes, and validates data', 'theme-check' ) . PHP_EOL;
		$results .= $standards ? 'Yes' : 'No';
		$results .= PHP_EOL . PHP_EOL;

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
				$results .= wordwrap( $this->get_plaintext_result_row( $result, $theme ), 110 ) . PHP_EOL;

			$results .= PHP_EOL;
		}

		if ( isset( $_POST['summary'] ) ) {
			$results .= "## Summary" . PHP_EOL;
			$results .= wordwrap( strip_tags( $_POST['summary'] ?: 'No summary given' ), 110 ) . PHP_EOL;
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

	function export() {

		// Check nonce and permissions
		check_admin_referer( 'export' );

		if ( ! isset( $_POST['review'] ) )
			return;

		$theme = get_stylesheet();
		$review = sanitize_text_field( $_POST[ 'review' ] );
		$scanner = VIP_Scanner::get_instance()->run_theme_review( $theme, $review );

		if ( $scanner ) {
			$filename = date( 'Ymd' ) . '.' . $theme . '.' . $review . '.VIP-Scanner.txt';
			header( 'Content-Type: text/plain' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

			echo $this->get_plaintext_theme_review_export( $scanner, $theme, $review );

			exit;
		}

		// redirect with error message
	}

	function submit() {

		// Check nonce and permissions
		check_admin_referer( 'export' );

		if ( ! isset( $_POST['review'] ) )
			return;

		$theme = get_stylesheet();
		$review = sanitize_text_field( $_POST[ 'review' ] );
		$scanner = VIP_Scanner::get_instance()->run_theme_review( $theme, $review );

		$to = '';
		$subject = "[Theme Review] $theme";

		if ( $scanner && !empty( $to ) ) {
			$zip = self::create_zip();

			wp_mail(
				$to,
				$subject,
				$this->get_plaintext_theme_review_export( $scanner, $theme, $review ),
				'',
				array( $zip )
			);

			unlink( $zip );
		}
	}

	private static function create_zip( $directory = '', $name = '', $overwrite = false ) {
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
}

// Initialize!
VIP_Scanner_UI::get_instance();
