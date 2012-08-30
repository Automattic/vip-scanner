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

	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		do_action( 'vip_scanner_loaded' );
	}

	function init() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
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
			<?php $this->display_vip_scanner_form(); ?>
			<?php $this->do_theme_review(); ?>
		</div>
		<?php
	}

	function display_vip_scanner_form() {
		$themes = wp_get_themes();
		$review_types = VIP_Scanner::get_instance()->get_review_types();
		$current_theme = isset( $_POST[ 'vip-scanner-theme-name' ] ) ? sanitize_text_field( $_POST[ 'vip-scanner-theme-name' ] ) : get_stylesheet();
		$current_review = isset( $_POST[ 'vip-scanner-review-type' ] ) ? sanitize_text_field( $_POST[ 'vip-scanner-review-type' ] ) : $review_types[0]; // TODO: eugh, need better error checking
		?>
		<form method="POST">
			<p>Select a theme and the review that you want to run:</p>
			<select name="vip-scanner-theme-name">
				<?php foreach ( $themes as $name => $location ) : ?>
					<option <?php selected( $current_theme, $location['Stylesheet'] ); ?> value="<?php echo esc_attr( $location['Stylesheet'] ); ?>"><?php echo esc_html( $location['Name'] ); ?></option>
				<?php endforeach; ?>
			</select>
			<select name="vip-scanner-review-type">
				<?php foreach ( $review_types as $review_type ) : ?>
					<option <?php selected( $current_review, $review_type ); ?> value="<?php echo esc_attr( $review_type ); ?>"><?php echo esc_html( $review_type ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php submit_button( 'Check it!', 'primary', 'submit', false ); ?>
			<?php wp_nonce_field( 'vip-scan-theme', 'vip-scanner-nonce' ); ?>
			<input type="hidden" name="page" value="<?php echo self::key; ?>" />
		</form>
		<?php
	}

	function do_theme_review() {
		if( ! isset( $_POST[ 'vip-scanner-nonce' ] ) || ! wp_verify_nonce( $_POST[ 'vip-scanner-nonce' ], 'vip-scan-theme' ) )
			return;

		if ( ! isset( $_POST[ 'vip-scanner-theme-name' ] ) )
			return;

		$theme = sanitize_text_field( $_POST[ 'vip-scanner-theme-name' ] );
		$review = isset( $_POST[ 'vip-scanner-review-type' ] ) ? sanitize_text_field( $_POST[ 'vip-scanner-review-type' ] ) : $review_types[0]; // TODO: eugh, need better error checking

		$scanner = VIP_Scanner::get_instance()->run_theme_review( $theme, $review );
		if ( $scanner )
			$this->display_theme_review_result( $scanner, $theme );
		else
			$this->display_scan_error();
	}

	function display_theme_review_result( $scanner, $theme ) {
		global $SyntaxHighlighter;
		if ( isset( $SyntaxHighlighter ) ) {
			add_action( 'admin_footer', array( &$SyntaxHighlighter, 'maybe_output_scripts' ) );
		}

		$report   = $scanner->get_results();
		$blockers = $scanner->get_errors( array( 'blocker', 'warning', 'required' ) ); // TODO allow to be filtered.
		$pass     = ! count( $blockers );
		?>
		<h4>Scanning: <?php echo $theme; ?></h4>

		<table class="scan-results-table">
			<tr>
				<th><?php _e( 'Scan Result', 'theme-check' ); ?></th>
				<td class="<?php echo $pass ? 'pass' : 'fail'; ?>"><?php echo $pass ? __( 'Pass', 'theme-check' ) : __( 'Fail', 'theme-check' ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Total Files', 'theme-check' ); ?></th>
				<td><?php echo intval( $report['total_files'] ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Total Checks', 'theme-check' ); ?></th>
				<td><?php echo intval( $report['total_checks'] ); ?></td>
			</tr>
			<tr>
				<th><?php _e( 'Total Errors', 'theme-check' ); ?></th>
				<td><?php echo count( $blockers ); ?></td>
			</tr>
		</table>

		<ol class="scan-results-list">
			<?php
			$results = $scanner->get_errors();
			foreach( $results as $result ) {
				$this->display_theme_review_result_row( $result, $scanner, $theme );
			}
			?>
		</ol>
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
			<span class="scan-level"><?php echo $level; ?></span>
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

	function display_scan_error() {
		echo 'Uh oh! Looks like something went wrong :(';
	}
}

// Initialize!
VIP_Scanner_UI::get_instance();
