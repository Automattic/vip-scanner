<?php

class VIP_Scanner_Async {
	const SCANNER_RESULT_CPT = 'scanresult';
	const REVIEW_TAXONOMY = 'vip-scan-review';

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
		add_action( 'vip_scanner_post_theme_review', array( $this, 'post_external_theme_review' ), 10, 3 );
		add_action( 'wp_ajax_vip-scanner-do_async_scan', array( $this, 'ajax_do_scan' ) );
		add_action( 'wp_ajax_vip-scanner-get_errors_summary', array( $this, 'ajax_get_errors_summary' ) );
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

		register_post_type( self::SCANNER_RESULT_CPT, array(
			'public' => false,
			'label'  => self::SCANNER_RESULT_CPT,
		) );

		register_taxonomy( self::REVIEW_TAXONOMY, self::SCANNER_RESULT_CPT, array( 'public' => false ) );
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

		wp_enqueue_style( 'vip_scanner_async_css', plugins_url( '../css/vip-scanner-async.css' , __FILE__ ) );
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

	function post_external_theme_review( $theme, $review, $scanner ) {
		$this->cache_scan_results( $review, $this->get_theme_path( $theme ), $scanner );
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
			'issues' => $this->get_cached_results_summary( $review ),
		);

		wp_send_json_success( $data );
	}

	function ajax_get_errors_summary() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'error' => 'insufficient_permissions', 'message' => __( 'You do not have sufficient permissions to perform that action.', 'vip-scanner' ) ) );
		}

		$review = $this->get_default_review_type();

		$data = array(
			'theme'  => wp_get_theme()->display( 'Name' ),
			'review' => $review,
			'issues' => $this->get_cached_results_summary( $review ),
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

		$this->cache_scan_results( $review_type, $path, $scanner );

		return $scanner;
	}

	function run_theme_review( $theme, $review_type, $scanners = array( 'checks', 'analyzers' ) ) {
		return $this->run_directory_review( $this->get_theme_path( $theme ), $review_type, $scanners );
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

	function cache_scan_results( $review, $path, $scanner ) {
		$review = sanitize_title_with_dashes( $review );

		$results = array();
		$error_counts = array();

		// Cache the results
		foreach ( $this->report_levels as $level ) {
			$error_counts[$level] = 0;
			$errors = $scanner->get_errors( array( $level ) );
			$results = array();

			// Split up errors by file
			foreach ( $errors as $error ) {
				++$error_counts[$level];

				$file = '';
				if ( is_array( $error['file'] ) ) {
					if ( !empty( $error['file'][0] ) ) {
						$file = $error['file'][0];
					}
				} elseif ( isset( $error['file'] ) ) {
					$file = str_replace( $path, '', $error['file'] );
				}

				if ( !isset( $results[$file] ) ) {
					$results[$file] = array_fill_keys( $this->report_levels, array() );
				}

				$results[$file][$level] = $error;
			}
		}

		$this->insert_cache_post( $review, $path, $results, $error_counts );
	}

	function get_cached_results_summary( $review = null, $path = null ) {
		$query_args = array(
			'post_type' => self::SCANNER_RESULT_CPT,
			'fields'    => 'ids',
		);

		// Do a taxonomy query if the review is specified
		if ( ! is_null( $review ) ) {
			$query_args[self::REVIEW_TAXONOMY] = sanitize_title_with_dashes( $review );
		}

		// Do a post name query if the path is specified
		if ( ! is_null( $path ) ) {
			$query_args['name'] = sanitize_title_with_dashes( $this->normalize_path_str( $path ) );
		}

		// Do the query and parse the issue counts
		$issues = array_fill_keys( $this->report_levels, 0 );
		$issue_query = new WP_Query( $query_args );

		while ( $issue_query->have_posts() ) {
			$post_id = $issue_query->next_post();
			$error_counts = get_post_meta( $post_id, 'vip-scanner-error-counts', true );

			foreach ( $this->report_levels as $level ) {
				$issues[$level] += isset( $error_counts[$level] ) ? intval( $error_counts[$level] ) : 0;
			}
		}

		return $issues;
	}

	private function insert_cache_post( $review, $path, $results, $error_counts ) {
		$review_slug     = sanitize_title_with_dashes( $review );
		$normalized_path = sanitize_title_with_dashes( $this->normalize_path_str( $path ) );

		$post_args = array(
			'post_type'    => self::SCANNER_RESULT_CPT,
			'post_name'    => $normalized_path,
			'post_date'    => date( 'Y-m-d H:i:s' ),
			'tax_input'    => array(
				self::REVIEW_TAXONOMY => $review_slug,
			),
		);

		// Check if the post already exists and add the id to args if it does
		$posts_query = new WP_Query( array(
			'post_type'			  => self::SCANNER_RESULT_CPT,
			'name'   			  => $normalized_path,
			'fields'			  => 'ids',
			self::REVIEW_TAXONOMY => $review_slug,
		) );

		$update = $posts_query->have_posts();
		if ( $update ) {
			$post_args['ID'] = $posts_query->next_post();
		}

		$id = wp_insert_post( $post_args );

		if ( is_wp_error( $id ) ) {
			var_dump( $id );
			return $id;
		}

		// Save the error counts meta
		$content = json_encode( $results );
		if ( $update ) {
			update_post_meta( $id, 'vip-scanner-error-counts', $error_counts );
			update_post_meta( $id, 'vip-scanner-results', $content );
		} else {
			add_post_meta( $id, 'vip-scanner-error-counts', $error_counts, true );
			add_post_meta( $id, 'vip-scanner-results', $content, true );
		}

		return true;
	}

	private function normalize_path_str( $path ) {
		$str_size = strlen( $path );
		if ( $path[$str_size - 1] == '/' ) {
			$path = substr( $path, 0, $str_size - 1 );
		}

		return $path;
	}

	private function get_theme_path( $theme ) {
		return $this->normalize_path_str( sprintf( '%s/%s', get_theme_root(), $theme ) );
	}
}

VIP_Scanner_Async::get_instance();