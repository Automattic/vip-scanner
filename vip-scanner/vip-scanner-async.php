<?php

class VIP_Scanner_Async {
	private static $instance;

	/**
	 * @var VIP_Scanner
	 */
	private $vip_scanner_instance;

	/**
	 * The default async scan interval in minutes.
	 * @var int
	 */
	private $default_async_scan_interval = 15;

	private $report_levels = array( 
		BaseScanner::LEVEL_BLOCKER,
		BaseScanner::LEVEL_WARNING,
		BaseScanner::LEVEL_NOTE
	);

	private function __construct() {
		$this->vip_scanner_instance = VIP_Scanner::get_instance();

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'wp_before_admin_bar_render', array( $this, 'add_admin_bar_node' ) );
		add_action( 'wp_ajax_vip-scanner-do_async_scan', array( $this, 'ajax_do_scan' ) );
		add_action( 'wp_ajax_vip-scanner-get_errors', array( $this, 'ajax_get_errors' ) );
	}

	static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			$class_name = __CLASS__;
			self::$instance = new $class_name;
		}
		return self::$instance;
	}

	function init() {
		register_post_type( AsyncDirectoryScanner::ASYNC_SCAN_CPT, array(
			'public' => false,
			'label'  => AsyncDirectoryScanner::ASYNC_SCAN_CPT,
		) );
	}

	function admin_init() {
		// register our cron interval
		add_filter( 'cron_schedules', array( $this, 'add_cron_schedules' ) );

		// schedule our cron
		$this->schedule_cron();

		// Add the cron action
		add_action( 'vip-scanner_do_scheduled_async_scan', array( $this, 'do_scheduled_async_scan' ) );
	}

	function admin_enqueue_scripts() {
		wp_enqueue_script( 'vip_scanner_async_js', plugins_url( '../js/vip-scanner-async.js' , __FILE__ ), array( 'jquery' ) );
		wp_localize_script( 'vip_scanner_async_js', 'vip_scanner_i18n', array(
			'no_issues'		  => __( 'No issues', 'vip-scanner' ),
			'single_issue'	  => __( '1 issue', 'vip-scanner' ),
			'multiple_issues' => __( '{issue_count} issues', 'vip-scanner' ),
			'review_header'   => __( 'Review: {review_name}', 'vip-scanner' ),
			'theme_header'    => __( 'Theme: {theme_name}', 'vip-scanner' ),
			'levels'		  => array(
				BaseScanner::LEVEL_BLOCKER => array(
					'none'     => __( '0 blockers', 'vip-scanner' ),
					'single'   => __( '1 blocker', 'vip-scanner' ),
					'multiple' => __( '{issue_count} blockers', 'vip-scanner' ),
				),
				BaseScanner::LEVEL_WARNING => array(
					'none'     => __( '0 warnings', 'vip-scanner' ),
					'single'   => __( '1 warning', 'vip-scanner' ),
					'multiple' => __( '{issue_count} warnings', 'vip-scanner' ),
				),
				BaseScanner::LEVEL_NOTE => array(
					'none'     => __( '0 notes', 'vip-scanner' ),
					'single'   => __( '1 note', 'vip-scanner' ),
					'multiple' => __( '{issue_count} notes', 'vip-scanner' ),
				),
			),
		) );

		wp_localize_script( 'vip_scanner_async_js', 'vip_scanner_settings', array(
			'default_async_scan_interval' => $this->default_async_scan_interval,
		) );
	}

	function add_admin_bar_node() {
		global $wp_admin_bar;

		// Leave all titles empty: they will be filled by AJAX

		$wp_admin_bar->add_node( apply_filters( 'vip-scanner-admin-bar-node', array(
			'id' => 'vip-scanner',
			'title' => '',
			'parent' => 'top-secondary',
			'href'   => add_query_arg( array( 'vip-scanner-review-type' => urlencode( $this->get_default_review_type() ) ), menu_page_url( 'vip-scanner', false ) ),
			'meta' => array(
				'title' => esc_html__( 'Go to VIP Scanner to see all code issues.', 'vip-scanner' ),
			),
		) ) );

		$wp_admin_bar->add_node( array(
			'id' => "vip-scanner-theme",
			'title' => '',
			'parent' => 'vip-scanner',
		) );

		$wp_admin_bar->add_node( array(
			'id' => "vip-scanner-review",
			'title' => '',
			'parent' => 'vip-scanner',
		) );

		foreach ( $this->report_levels as $level ) {
			$wp_admin_bar->add_node( apply_filters( "vip-scanner-admin-bar-node-$level", array(
				'id' => "vip-scanner-$level",
				'title' => '',
				'parent' => 'vip-scanner',
			) ) );
		}
	}

	function add_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['vip-scanner'] ) ) {
			$schedules['vip-scanner-interval'] = array(
				'interval' => $this->default_async_scan_interval *  60,
				'display'  => sprintf( __( 'Once every %s minutes.', 'vip-scanner' ), number_format( $this->default_async_scan_interval ) ),
			);
		}

		return $schedules;
	}

	function schedule_cron() {
		if ( false === wp_next_scheduled( 'do_scheduled_async_scan' ) ) {
			$schedules = wp_get_schedules();
			wp_schedule_event( time() + $schedules['vip-scanner-interval']['interval'], 'vip-scanner-interval', 'do_scheduled_async_scan' );
		}
	}

	function ajax_do_scan() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'error' => 'insufficient_permissions', 'message' => __( 'You do not have sufficient permissions to perform that action.', 'vip-scanner' ) ) );
		}

		$this->do_scheduled_async_scan();
		$review = $this->get_default_review_type();

		$data = array(
			'theme'  => wp_get_theme()->display( 'Name' ),
			'review' => $review,
			'issues' => $this->get_cached_issues_summary( $review ),
		);

		wp_send_json_success( $data );
	}

	function ajax_get_errors() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'error' => 'insufficient_permissions', 'message' => __( 'You do not have sufficient permissions to perform that action.', 'vip-scanner' ) ) );
		}

		$review = $this->get_default_review_type();
		if ( false === $this->get_stored_theme_review( $review ) ) {
			$this->ajax_do_scan();
		}

		$data = array(
			'theme'  => wp_get_theme()->display( 'Name' ),
			'review' => $review,
			'issues' => $this->get_cached_issues_summary( $review ),
		);

		wp_send_json_success( $data );
	}

	function do_scheduled_async_scan() {
		// Get the current theme
		$theme = get_stylesheet();

		return $this->run_theme_review( $theme, $this->get_default_review_type(), array( 'checks' ) );
	}

	function run_directory_review( $path, $review_type, $scanners = array( 'checks', 'analyzers' ) ) {
		$review = $this->vip_scanner_instance->get_review( $review_type );
		if ( ! $review )
			return false;

		$scanner = new AsyncDirectoryScanner( $path, $review );
		$scanner->scan( $scanners );

		$this->store_theme_review( $review_type, $path );
		$this->cache_scan_results( $review_type, $path, $scanner );

		return $scanner;
	}

	function run_theme_review( $theme, $review_type, $scanners = array( 'checks', 'analyzers' ) ) {
		$path = sprintf( '%s/%s', get_theme_root(), $theme );

		return $this->run_directory_review( $path, $review_type, $scanners );
	}

	function get_default_review_type() {
		$review_type = get_option( 'vip-scanner-default-async-review-type', null );

		if ( is_null( $review_type ) ) {
			$review_types = $this->vip_scanner_instance->get_review_types();
			$review_type = $review_types[ VIP_Scanner_UI::get_instance()->default_review ];
		}

		return $review_type;
	}

	function set_default_review_type( $review_type ) {
		update_option( 'vip-scanner-default-async-review-type', $review_type );
	}

	function store_theme_review( $review, $path ) {
		$review = sanitize_title_with_dashes( $review );
		update_option( "vip-scanner-review-$review", $path );
	}

	function get_stored_theme_review( $review ) {
		$review = sanitize_title_with_dashes( $review );
		return get_option( "vip-scanner-review-$review", false );
	}

	function cache_scan_results( $review, $path, $scanner ) {
		$review = sanitize_title_with_dashes( $review );

		// Cache the results
		foreach ( $this->report_levels as $level ) {
			$errors = $scanner->get_errors( array( $level ) );
			$file_errors = array();

			// Split up errors by file
			foreach ( $errors as $error ) {
				$file = '';
				if ( is_array( $error['file'] ) ) {
					if ( !empty( $error['file'][0] ) ) {
						$file = $error['file'][0];
					}
				} elseif ( isset( $error['file'] ) ) {
					$file = str_replace( $path, '', $error['file'] );
				}

				if ( !isset( $file_errors[$file] ) ) {
					$file_errors[$file] = array();
				}

				$file_errors[$file][] = $error;
			}

			// Summarize the number of errors by file
			$option = "vip-scanner-file-errors-$review-$level";
			$file_error_counts = get_option( $option, array() );
			foreach ( $file_errors as $file => $errors ) {
				$file_error_counts[$file] = count( $errors );
			}

			// Store this information for later use
			update_option( $option, $file_error_counts );
		}
	}

	function get_cached_issues_summary( $review ) {
		$issues = array();

		$review = sanitize_title_with_dashes( $review );
		foreach ( $this->report_levels as $level ) {
			$file_error_counts = get_option( "vip-scanner-file-errors-$review-$level", array() );
			$issues[$level] = $file_error_counts;
		}

		return $issues;
	}
}

VIP_Scanner_Async::get_instance();