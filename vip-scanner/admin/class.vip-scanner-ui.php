<?php

/**
 * Class VIP_Scanner_UI
 */
class VIP_Scanner_UI {
	/**
	 * Singleton instance.
	 *
	 * @var VIP_Scanner_UI
	 */
	private static $instance = null;

	/**
	 * @var VIP_Scanner
	 */
	private $vip_scanner;

	/**
	 * Version of the plugin.
	 *
	 * @var string
	 */
	private $version = null;

	/**
	 * Plugin name in slug form.
	 *
	 * @var string
	 */
	const PLUGIN_SLUG = 'vip-scanner';

	/**
	 * Hook of the admin page, returned by add_submenu_page().
	 *
	 * @var string
	 */
	private $submenu_page_hook;

	/**
	 * Available Review Types, provided by the scanner.
	 *
	 * @var string
	 */
	public $review_types;

	/**
	 * Review Type to run if nothing is specified via the UI.
	 *
	 * @var string
	 */
	public $default_review;

	/**
	 * Types of messages returned by the scanner.
	 *
	 * @var array
	 */
	private $blocker_types;

	/**
	 * Email address used for submitting VIP themes for review.
	 *
	 * @var string
	 */
	private $vip_theme_submission_email;

	/**
	 * Retrieves Singleton instance.
	 *
	 * @return VIP_Scanner_UI
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new VIP_Scanner_UI;
		}

		return self::$instance;
	}

	/**
	 * Add actions.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Set up class variables.
	 */
	public function init() {
		$this->vip_scanner = VIP_Scanner::get_instance();
		$this->review_types = $this->vip_scanner->get_review_types();
		$this->default_review = apply_filters( 'vip_scanner_default_review', 2, $this->review_types );

		$this->blocker_types = apply_filters( 'vip_scanner_blocker_types', array(
			BaseScanner::LEVEL_BLOCKER  => esc_html__( 'Blockers', 'vip-scanner' ),
			BaseScanner::LEVEL_WARNING  => esc_html__( 'Warnings', 'vip-scanner' ),
			BaseScanner::LEVEL_NOTE     => esc_html__( 'Notes', 'vip-scanner' ),
		) );

		$this->vip_theme_submission_email = apply_filters( 'vip_scanner_email_to', '' );

		do_action( 'vip_scanner_loaded' );
	}

	/**
	 * Handle tasks that need to be done before rendering the plugin's admin page:
	 * - Export theme for VIP review.
	 * - Submit theme for VIP review.
	 * - Add admin notices.
	 */
	public function admin_init() {
		if ( isset( $_POST['page'], $_POST['action'] ) && self::PLUGIN_SLUG === $_POST['page'] ) {
			if ( 'Export' === $_POST['action']  ) {
				$this->export();
			}
			if ( 'Submit' === $_POST['action'] ) {
				die( 'Submit' );
				$this->submit();
			}
		}

		if ( isset( $_GET['page'], $_GET['message'] ) && self::PLUGIN_SLUG === $_GET['page']  ) {
			switch ( $_GET['message'] ) {
				case 'fail':
					add_action( 'admin_notices', array( $this, 'admin_notice_fail' ) );
					break;

				case 'success':
					add_action( 'admin_notices', array( $this, 'admin_notice_success' ) );
					break;
			}
		}
	}

	/**
	 * Retrieve the version number from the plugin's header.
	 *
	 * @return string
	 */
	public function get_version() {
		if ( is_null( $this->version ) ) {
			$plugin_data = get_plugin_data( __FILE__ );
			$this->version = $plugin_data['Version'];
		}

		return $this->version;
	}

	/**
	 * Add the plugin admin page under Tools.
	 */
	public function add_menu_page() {
		$parent_slug = apply_filters( 'vip_scanner_submenu_page', 'tools.php' );
		$this->submenu_page_hook = add_submenu_page( $parent_slug, esc_html__( 'VIP Scanner', 'vip-scanner' ), esc_html__( 'VIP Scanner', 'vip-scanner' ), 'manage_options', self::PLUGIN_SLUG, array( $this, 'display_admin_page' ) );
	}

	/**
	 * Enqueue the plugin's stylesheets and CSS.
	 *
	 * @param string $current_hook Hook of the admin page currently displayed.
	 */
	public function admin_enqueue_scripts( $current_hook ) {
		if ( $this->submenu_page_hook !== $current_hook ) {
			return;
		}

		wp_enqueue_style( 'vip-scanner-css', plugins_url( 'css/vip-scanner.css', VIP_SCANNER__PLUGIN_FILE ), array(), '20120320' );
		wp_enqueue_style( 'vip-scanner-admin-ui-css', plugins_url( 'css/jquery-ui.min.css', VIP_SCANNER__PLUGIN_FILE ), array(), '1.11.2' );

		wp_enqueue_script( 'vip-scanner-js', plugins_url( 'js/vip-scanner.js', VIP_SCANNER__PLUGIN_FILE ), array( 'jquery' ), '20120320' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'jquery-ui-core' );
	}

	/**
	 * Main admin page method.
	 *
	 * Handles all the logic related to running reviews and displaying results.
	 */
	public function display_admin_page() {
		global $title;

		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'vip-scanner' ) );
		}

		?>
		<div id="vip-scanner" class="wrap">
			<h2><?php echo esc_html( $title ); ?></h2>
			<div class="scanner-wrapper">
				<?php
					if ( isset( $_GET[ 'vip-scanner-review-type' ] ) ) {
						$this->do_theme_review();
					} else {
						echo '<h3>Theme to scan: <span class="theme-name">' . get_stylesheet() . '</h3>';
						$this->display_vip_scanner_form();
					}
				?>
			</div>
		</div>
	<?php
	}

	function display_vip_scanner_form() {
		$current_review = isset( $_GET[ 'vip-scanner-review-type' ] ) ? sanitize_text_field( $_GET[ 'vip-scanner-review-type' ] ) : $this->review_types[ $this->default_review ];
		?>
		<form method="GET">
			<input type="hidden" name="page" value="<?php echo self::PLUGIN_SLUG; ?>" />
			<select name="vip-scanner-review-type">
				<?php foreach ( $this->review_types as $review_type ) : ?>
					<option <?php selected( $current_review, $review_type ); ?> value="<?php echo esc_attr( $review_type ); ?>"><?php echo esc_html( $review_type ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php submit_button( 'Scan', 'primary', false, false ); ?>
		</form>
	<?php
	}

	/**
	 * Run a scan, and display the results.
	 *
	 *
	 */
	function do_theme_review() {
		$theme = get_stylesheet();
		$review_types = $this->vip_scanner->get_review_types();
		$review = isset( $_GET[ 'vip-scanner-review-type' ] ) ? sanitize_text_field( $_GET[ 'vip-scanner-review-type' ] ) : $review_types[ $this->default_review ]; // TODO: eugh, need better error checking

		$scanner = $this->vip_scanner->run_theme_review( $theme, $review );

		$transient_key = 'vip_scanner_' . $this->get_version() . '_' . md5( $theme . $review );
		if ( $scanner !== get_transient( $transient_key ) )
			@set_transient( $transient_key, $scanner );

		if ( $scanner ) {
			$this->display_theme_review_result( $scanner, $theme );

			// Only display submission form for VIP Reviews.
			if ( 'VIP Theme Review' === $_GET['vip-scanner-review-type'] ) {
				$this->display_vip_review_submission_form( $review, $scanner );
			}
		} else {
			$this->display_scan_error();
		}
	}

	/**
	 * Output a scan result message in array form as an HTML list element.
	 *
	 * @global SyntaxHighlighter $SyntaxHighlighter SyntaxHighlighter Evolved plugin instance.
	 *
	 * @param VIP_Scanner $scanner Object with the scan results.
	 * @param string      $theme   Theme name.
	 */
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
			<div class="scan-results result-<?php echo $pass ? 'pass' : 'fail'; ?>"><?php echo $pass ? __( 'Passed the Scan with no errors!', 'vip-scanner' ) : __( 'Failed to pass Scan', 'vip-scanner' ); ?></div>

			<table class="scan-results-table">
				<tr>
					<th><?php _e( 'Total Files', 'vip-scanner' ); ?></th>
					<td><?php echo intval( $report['total_files'] ); ?></td>
				</tr>
				<tr>
					<th><?php _e( 'Total Checks', 'vip-scanner' ); ?></th>
					<td><?php echo intval( $report['total_checks'] ); ?></td>
				</tr>
			</table>
		</div>

		<h2 class="nav-tab-wrapper">
			<a href="#errors" class="nav-tab"><?php echo absint( $errors ); ?> <?php _e( 'Errors', 'vip-scanner' ); ?></a>
			<a href="#notes" class="nav-tab"><?php echo absint( $notes ); ?> <?php _e( 'Notes', 'vip-scanner' ); ?></a>
			<a href="#analysis" class="nav-tab"><?php _e( 'Analysis', 'vip-scanner' ); ?></a>
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
						$this->display_theme_review_result_row( $result, $theme );
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
						$this->display_theme_review_result_row( $result, $theme );
					}
					?>
				</ul>
			<?php endforeach; ?>
		</div>

		<div id="analysis">
			<div id="analysis-accordion">
				<?php
				$empty = array();
				foreach ( $scanner->elements as $element ) {
					if ( $element->name() !== 'Files' ) {
						$element->analyze_prefixes();
					}

					// Display empty elements after the others
					if ( $element->is_empty() ) {
						$empty[] = $element;
						continue;
					}

					$r = new ElementRenderer( $element );
					$r->display();
				}

				foreach ( $empty as $element ) {
					$r = new ElementRenderer( $element );
					$r->display();
				}

				?>
			</div>
		</div>
	<?php
	}

	/**
	 * Output a scan result message in array form as an HTML list element.
	 *
	 * @global SyntaxHighlighter $SyntaxHighlighter SyntaxHighlighter Evolved plugin instance.
	 *
	 * @param array  $error Scan result message.
	 * @param string $theme Theme name.
	 */
	function display_theme_review_result_row( $error, $theme ) {
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

	/**
	 * Output the VIP submission form.
	 *
	 * @param string      $review   Type of review.
	 * @param VIP_Scanner $scanner  Object with the scan results.
	 */
	public function display_vip_review_submission_form( $review, $scanner ) {
		?>
		<h2>Export Theme for VIP Review</h2>

			<form method="POST" class="export-form">

				<?php do_action( 'vip_scanner_form', $review, count( $scanner->get_errors( array_keys( $this->blocker_types ) ) ) ); ?>

				<p>
					<?php
					// hide submit button if $to is empty
					if ( !empty( $this->vip_theme_submission_email ) )
						submit_button( __( 'Submit', 'vip-scanner' ), 'primary', 'action', false );
					?>
					<?php submit_button( __( 'Export', 'vip-scanner' ), 'secondary', 'action', false ); ?>
				</p>

				<?php wp_nonce_field( 'export' ); ?>
				<input type="hidden" name="review" value="<?php echo esc_attr( $review ) ?>">
				<input type="hidden" name="page" value="<?php echo self::PLUGIN_SLUG; ?>">
			</form>
		<?php
	}

	/**
	 * Creates a plain text version of the scan results screen.
	 *
	 * @param VIP_Scanner $scanner  Object with the scan results.
	 * @param string      $theme    Theme name.
	 * @param string      $review   Type of review.
	 *
	 * @return string Plain text scan results.
	 */
	function get_plaintext_theme_review_export( $scanner, $theme, $review ) {
		$results = "";

		$results .= $title = apply_filters( 'vip_scanner_export_title', "$theme - $review", $review ) . PHP_EOL;
		$title_len = strlen( $title );
		$results .= str_repeat( '=', $title_len ) . PHP_EOL;

		$version_str = ' ' . sprintf( __( 'VIP Scanner %s', 'vip-scanner' ), $this->get_version() ) . ' ';
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

		$results .= __( 'Total Files', 'vip-scanner' );
		$results .= ':  ';
		$results .= intval( $report['total_files'] );
		$results .= PHP_EOL;

		$results .= __( 'Total Checks', 'vip-scanner' );
		$results .= ': ';
		$results .= intval( $report['total_checks'] );
		$results .= PHP_EOL;

		$results .= __( 'Errors', 'vip-scanner' );
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

	/**
	 * Transform an scan result message in array from into a plaintext string.
	 *
	 * @param array  $error Scan result message.
	 * @param string $theme Theme name.
	 *
	 * @return string Markdown-formatted, text only scan result message.
	 */
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

	/**
	 * Replace select HTML tags in a string with Markdown.
	 *
	 * @param string $row String containing HTML.
	 *
	 * @return string Markdown-formatted string with all HTML tags stripped.
	 */
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

	/**
	 * Display an error message when the scan could not be run successfully.
	 */
	function display_scan_error() {
		echo 'Uh oh! Looks like something went wrong :(';
	}

	/**
	 * Retrieve scan results, uses the review stored in a transient if possible.
	 *
	 * @param string $theme  Name of the theme.
	 * @param string $review Review type.
	 *
	 * @return VIP_Scanner Object with the scan results.
	 */
	function get_cached_theme_review( $theme, $review ) {
		$transient_key = 'vip_scanner_' . $this->get_version() . '_' . md5( $theme . $review );

		if ( false === $scanner = get_transient( $transient_key ) ) {
			$scanner = $this->vip_scanner->run_theme_review( $theme, $review );
			@set_transient( $transient_key, $scanner );
		}

		return $scanner;
	}

	/**
	 * Saves the scan results, the information entered into the submission form as a plain text file.
	 */
	function export() {
		check_admin_referer( 'export' );

		// Turn off error reporting during an export
		error_reporting(0);

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
			'page' => self::PLUGIN_SLUG,
			'message' => 'fail',
			'vip-scanner-review-type' => urlencode( $review ),
		) );

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Sends the scanner results, the information entered into the submission form, and the scanned theme as a ZIP to
	 * the email address stored in `$vip_theme_submission_email`.
	 *
	 * Used for submitting themes to the VIP team for review.
	 */
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

		if ( $scanner && !empty( $this->vip_theme_submission_email ) ) {
			$zip = self::create_zip();

			// redirect with error message
			if ( !$zip )
				return;

			$mail = wp_mail(
				$this->vip_theme_submission_email,
				$subject,
				$message,
				$headers,
				array( $zip )
			);

			unlink( $zip );
		}

		$args = array(
			'page' => self::PLUGIN_SLUG,
			'message' => 'success',
			'vip-scanner-review-type' => urlencode( $review ),
		);

		// Error message if the wp_mail didn't work
		if ( !$mail ) {
			$args['message'] = 'fail';
		} else {
			do_action( 'vip_scanner_form_success' );
		}

		die();
		wp_safe_redirect( add_query_arg( $args ) );
		exit;
	}

	/**
	 * Creates a ZIP file from a directory.
	 *
	 * @param string $directory Directory to ZIP. Defaults to the stylesheet directory of the active theme.
	 * @param string $name      Name of the ZIP file. Defaults to the theme name and the date.
	 * @param bool   $overwrite Whether to overwrite an existing ZIP file of the same name.
	 *
	 * @return string|bool Path to the created ZIP file, or false on failure.
	 */
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

	/**
	 * Displays a failure message. Callback for `admin_notices` action.
	 */
	function admin_notice_fail() {
	?>
		<div class="error">
			<p><strong><?php esc_html_e( 'Fail! Something broke.', 'vip-scanner' ); ?></strong></p>
		</div>
	<?php
	}

	/**
	 * Displays a success message. Callback for `admin_notices` action.
	 */
	function admin_notice_success() {
	?>
		<div class="updated">
			<p><strong><?php esc_html_e( 'Success!', 'vip-scanner' ); ?></strong></p>
		</div>
	<?php
	}
}