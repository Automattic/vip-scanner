<?php

require_once( 'CodeCheckTestBase.php' );

class VIPRestrictedCommandsTest extends CodeCheckTestBase {

	public function testRestrictedWPCore() {
		$line = 3;
		$expected_errors = array(
			array( 'slug' => 'update_post_caches', 'level' => 'Note', 'description' => 'Post cache alteration', 'file' => 'VIPRestrictedCommandsCheck1.inc', 'lines' => ++$line ),

			array( 'slug' => 'update_option', 'level' => 'Note', 'description' => 'Updating option', 'file' => 'VIPRestrictedCommandsCheck1.inc', 'lines' => ++$line ),
			array( 'slug' => 'get_option',    'level' => 'Note', 'description' => 'Getting option',  'file' => 'VIPRestrictedCommandsCheck1.inc', 'lines' => ++$line ),
			array( 'slug' => 'add_option',    'level' => 'Note', 'description' => 'Adding Option',   'file' => 'VIPRestrictedCommandsCheck1.inc', 'lines' => ++$line ),
			array( 'slug' => 'delete_option', 'level' => 'Note', 'description' => 'Deleting Option', 'file' => 'VIPRestrictedCommandsCheck1.inc', 'lines' => ++$line ),

			array( 'slug' => 'wp_remote_get', 'level' => 'Warning', 'description' => 'Uncached Remote operation, please use one of these functions: http://vip.wordpress.com/documentation/best-practices/fetching-remote-data/', 'file' => 'VIPRestrictedCommandsCheck1.inc', 'lines' => ++$line ),

			array( 'slug' => 'wp_schedule_event',        'level' => 'Warning', 'description' => 'WP Cron usage', 'file' => 'VIPRestrictedCommandsCheck1.inc', 'lines' => ++$line ),
			array( 'slug' => 'wp_schedule_single_event', 'level' => 'Warning', 'description' => 'WP Cron usage', 'file' => 'VIPRestrictedCommandsCheck1.inc', 'lines' => ++$line ),
			array( 'slug' => 'wp_clear_scheduled_hook',  'level' => 'Warning', 'description' => 'WP Cron usage', 'file' => 'VIPRestrictedCommandsCheck1.inc', 'lines' => ++$line ),
			array( 'slug' => 'wp_next_scheduled',        'level' => 'Warning', 'description' => 'WP Cron usage', 'file' => 'VIPRestrictedCommandsCheck1.inc', 'lines' => ++$line ),
			array( 'slug' => 'wp_unschedule_event',      'level' => 'Warning', 'description' => 'WP Cron usage', 'file' => 'VIPRestrictedCommandsCheck1.inc', 'lines' => ++$line ),
			array( 'slug' => 'wp_get_schedule',          'level' => 'Warning', 'description' => 'WP Cron usage', 'file' => 'VIPRestrictedCommandsCheck1.inc', 'lines' => ++$line ),

			array( 'slug' => 'add_feed', 'level' => 'Warning', 'description' => 'Custom feed implementation', 'file' => 'VIPRestrictedCommandsCheck1.inc', 'lines' => ++$line ),

			array( 'slug' => 'query_posts', 'level' => 'Blocker', 'description' => 'Rewriting the main loop. WP_Query or get_posts (with suppress_filters => false) might be better functions: http://developer.wordpress.com/2012/05/14/querying-posts-without-query_posts/', 'file' => 'VIPRestrictedCommandsCheck1.inc', 'lines' => ++$line ),
		);
		$actual_errors = $this->checkFile( 'VIPRestrictedCommandsCheck1.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}

	public function testMultisite() {
		$line = 3;
		$expected_errors = array(
			array( 'slug' => 'switch_to_blog',       'level' => 'Blocker', 'description' => 'Switching blog context', 'file' => 'VIPRestrictedCommandsCheck2.inc', 'lines' => ++$line ),
			array( 'slug' => 'restore_current_blog', 'level' => 'Blocker', 'description' => 'Switching blog context', 'file' => 'VIPRestrictedCommandsCheck2.inc', 'lines' => ++$line ),
			array( 'slug' => 'ms_is_switched',       'level' => 'Blocker', 'description' => 'Querying blog context',  'file' => 'VIPRestrictedCommandsCheck2.inc', 'lines' => ++$line ),
			array( 'slug' => 'wp_get_sites',         'level' => 'Blocker', 'description' => 'Querying network sites', 'file' => 'VIPRestrictedCommandsCheck2.inc', 'lines' => ++$line ),
		);
		$actual_errors = $this->checkFile( 'VIPRestrictedCommandsCheck2.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}

	public function testUncached() {
		$line = 3;
		$expected_errors = array(
			// Uncached functions
			array( 'slug' => 'count_user_posts',        'level' => BaseScanner::LEVEL_WARNING, 'description' => 'Uncached Function. Use wpcom_vip_count_user_posts() instead.', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'get_category_by_slug',    'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to wpcom_vip_get_category_by_slug()', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'wp_get_post_categories',  'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to get_the_terms() along with wp_list_pluck() see: http://vip.wordpress.com/documentation/uncached-functions/', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'wp_get_post_tags',        'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to get_the_terms() along with wp_list_pluck() see: http://vip.wordpress.com/documentation/uncached-functions/', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'get_cat_ID',              'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to wpcom_vip_get_term_by()', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'get_term_by',             'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to wpcom_vip_get_term_by()', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'get_page_by_title',       'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or cached', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'get_page_by_path',        'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to wpcom_vip_get_page_by_title()', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'wp_get_object_terms',     'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or cached', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'wp_get_post_terms',       'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to get_the_terms() along with wp_list_pluck() see: http://vip.wordpress.com/documentation/uncached-functions/', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'get_posts',               'level' => 'Warning', 'description' => 'Uncached function. Use WP_Query or ensure suppress_filters is false', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'wp_get_recent_posts',     'level' => 'Warning', 'description' => 'Uncached function. Use WP_Query or ensure suppress_filters is false', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'get_children',            'level' => 'Blocker', 'description' => 'Similar to get_posts(), but also performs a no-LIMIT query among other bad things by default. Alias of break_my_site_now_please(). Do not use.', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'term_exists',             'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to wpcom_vip_term_exists()', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'get_term_link',           'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to wpcom_vip_get_term_link()', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'get_tag_link',            'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to wpcom_vip_get_term_link()', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'get_category_link',       'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to wpcom_vip_get_term_link()', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'url_to_post_id',          'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to wpcom_vip_url_to_postid()', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'url_to_postid',           'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to wpcom_vip_url_to_postid()', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'wp_old_slug_redirect',    'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to wpcom_vip_old_slug_redirect()', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'get_adjacent_post',       'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to wpcom_vip_get_adjacent_post()', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'get_previous_post',       'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to wpcom_vip_get_adjacent_post()', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'get_next_post',           'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to wpcom_vip_get_adjacent_post()', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'previous_post_link',      'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to wpcom_vip_get_adjacent_post()', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'next_post_link',          'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to wpcom_vip_get_adjacent_post()', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'attachment_url_to_postid',  'level' => 'Warning', 'description' => 'Uncached function. Should be used on a limited basis or changed to wpcom_vip_attachment_url_to_postid()', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
		);
		$line += 2; // Skip two lines.
		$expected_errors = array_merge( $expected_errors, array(
			// Object cache bypass
			array( 'slug' => 'wpcom_uncached_get_post_meta',    'level' => 'Warning', 'description' => 'Bypassing object cache, please validate', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
			array( 'slug' => 'wpcom_uncached_get_post_by_meta', 'level' => 'Warning', 'description' => 'Bypassing object cache, please validate', 'file' => 'VIPRestrictedCommandsCheck3.inc', 'lines' => ++$line ),
		) );
		$actual_errors = $this->checkFile( 'VIPRestrictedCommandsCheck3.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}

	public function testNeedingExtraCare() {
		$expected_errors = array(
			array( 'slug' => 'wpcom_vip_load_custom_cdn', 'level' => 'Blocker', 'description' => 'This should only be used if you have a CDN already set up.', 'file' => 'VIPRestrictedCommandsCheck4.inc', 'lines' => 4 ),
		);
		$actual_errors = $this->checkFile( 'VIPRestrictedCommandsCheck4.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}

	public function testRoleModifications() {
		$line = 3;
		$expected_errors = array(
			array( 'slug' => 'get_role',    'level' => 'Warning', 'description' => "Role access; use helper functions http://lobby.vip.wordpress.com/best-practices/custom-user-roles/",       'file' => 'VIPRestrictedCommandsCheck5.inc', 'lines' => ++$line ),
			array( 'slug' => 'add_role',    'level' => 'Blocker', 'description' => "Role modification; use helper functions http://lobby.vip.wordpress.com/best-practices/custom-user-roles/", 'file' => 'VIPRestrictedCommandsCheck5.inc', 'lines' => ++$line ),
			array( 'slug' => 'remove_role', 'level' => 'Blocker', 'description' => "Role modification; use helper functions http://lobby.vip.wordpress.com/best-practices/custom-user-roles/", 'file' => 'VIPRestrictedCommandsCheck5.inc', 'lines' => ++$line ),
			array( 'slug' => 'add_cap',     'level' => 'Blocker', 'description' => "Role modification; use helper functions http://lobby.vip.wordpress.com/best-practices/custom-user-roles/", 'file' => 'VIPRestrictedCommandsCheck5.inc', 'lines' => ++$line ),
			array( 'slug' => 'remove_cap',  'level' => 'Blocker', 'description' => "Role modification; use helper functions http://lobby.vip.wordpress.com/best-practices/custom-user-roles/", 'file' => 'VIPRestrictedCommandsCheck5.inc', 'lines' => ++$line ),
		);
		$actual_errors = $this->checkFile( 'VIPRestrictedCommandsCheck5.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}

	public function testUserMeta() {
		$line = 3;
		$expected_errors = array(
			array( 'slug' => 'add_user_meta',    'level' => 'Blocker', 'description' => "Using user meta, consider user_attributes http://vip.wordpress.com/documentation/user_meta-vs-user_attributes/", 'file' => 'VIPRestrictedCommandsCheck6.inc', 'lines' => ++$line ),
			array( 'slug' => 'delete_user_meta', 'level' => 'Blocker', 'description' => "Using user meta, consider user_attributes http://vip.wordpress.com/documentation/user_meta-vs-user_attributes/", 'file' => 'VIPRestrictedCommandsCheck6.inc', 'lines' => ++$line ),
			array( 'slug' => 'get_user_meta',    'level' => 'Blocker', 'description' => "Using user meta, consider user_attributes http://vip.wordpress.com/documentation/user_meta-vs-user_attributes/", 'file' => 'VIPRestrictedCommandsCheck6.inc', 'lines' => ++$line ),
			array( 'slug' => 'update_user_meta', 'level' => 'Blocker', 'description' => "Using user meta, consider user_attributes http://vip.wordpress.com/documentation/user_meta-vs-user_attributes/", 'file' => 'VIPRestrictedCommandsCheck6.inc', 'lines' => ++$line ),
		);
		$actual_errors = $this->checkFile( 'VIPRestrictedCommandsCheck6.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}

	public function testDebugging() {
		$line = 3;
		$expected_errors = array(
			array( 'slug' => 'error_log',                  'level' => 'Blocker', 'description' => "Filesystem operation",                     'file' => 'VIPRestrictedCommandsCheck7.inc', 'lines' => ++$line ),
			array( 'slug' => 'var_dump',                   'level' => 'Warning', 'description' => "Unfiltered variable output",               'file' => 'VIPRestrictedCommandsCheck7.inc', 'lines' => ++$line ),
			array( 'slug' => 'print_r',                    'level' => 'Warning', 'description' => "Unfiltered variable output",               'file' => 'VIPRestrictedCommandsCheck7.inc', 'lines' => ++$line ),
			array( 'slug' => 'var_export',                 'level' => 'Warning', 'description' => "Unfiltered variable output",               'file' => 'VIPRestrictedCommandsCheck7.inc', 'lines' => ++$line ),
			array( 'slug' => 'wp_debug_backtrace_summary', 'level' => 'Blocker', 'description' => "Unfiltered filesystem information output", 'file' => 'VIPRestrictedCommandsCheck7.inc', 'lines' => ++$line ),
			array( 'slug' => 'debug_backtrace',            'level' => 'Blocker', 'description' => "Unfiltered filesystem information output", 'file' => 'VIPRestrictedCommandsCheck7.inc', 'lines' => ++$line ),
			array( 'slug' => 'debug_print_backtrace',      'level' => 'Blocker', 'description' => "Unfiltered filesystem information output", 'file' => 'VIPRestrictedCommandsCheck7.inc', 'lines' => ++$line ),
			array( 'slug' => 'trigger_error',              'level' => 'Blocker', 'description' => "Triggered error message not accessible",   'file' => 'VIPRestrictedCommandsCheck7.inc', 'lines' => ++$line ),
			array( 'slug' => 'set_error_handler',          'level' => 'Blocker', 'description' => "User-defined error handler not supported", 'file' => 'VIPRestrictedCommandsCheck7.inc', 'lines' => ++$line ),
		);
		$actual_errors = $this->checkFile( 'VIPRestrictedCommandsCheck7.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}

	public function testOther() {
		$line = 3;
		$expected_errors = array(
			array( 'slug' => 'date_default_timezone_set', 'level' => 'Blocker', 'description' => "Timezone manipulation", 'file' => 'VIPRestrictedCommandsCheck8.inc', 'lines' => ++$line ),
			array( 'slug' => 'error_reporting',           'level' => 'Blocker', 'description' => "Settings alteration", 'file' => 'VIPRestrictedCommandsCheck8.inc', 'lines' => ++$line ),
			array( 'slug' => 'filter_input',              'level' => 'Warning', 'description' => "Using filter_input(), use sanitize_* functions instead", 'file' => 'VIPRestrictedCommandsCheck8.inc', 'lines' => ++$line ),
			array( 'slug' => 'eval',                      'level' => 'Blocker', 'description' => "Meta programming", 'file' => 'VIPRestrictedCommandsCheck8.inc', 'lines' => ++$line ),
			array( 'slug' => 'create_function',           'level' => 'Blocker', 'description' => "Using create_function, consider annonymous functions", 'file' => 'VIPRestrictedCommandsCheck8.inc', 'lines' => ++$line ),
			array( 'slug' => 'extract',                   'level' => 'Blocker', 'description' => "Explicitly define variables rather than using extract()", 'file' => 'VIPRestrictedCommandsCheck8.inc', 'lines' => ++$line ),
			array( 'slug' => 'ini_set',                   'level' => 'Blocker', 'description' => "Settings alteration", 'file' => 'VIPRestrictedCommandsCheck8.inc', 'lines' => ++$line ),
			array( 'slug' => 'switch_theme',              'level' => 'Blocker', 'description' => "Switching theme programmatically is not allowed. Please make the update by hand after a deploy of your code", 'file' => 'VIPRestrictedCommandsCheck8.inc', 'lines' => ++$line ),
			array( 'slug' => 'wp_is_mobile',              'level' => 'Warning', 'description' => "wp_is_mobile() is not batcache-friendly, please use <a href=\"http://vip.wordpress.com/documentation/mobile-theme/#targeting-mobile-visitors\">jetpack_is_mobile()</a>", 'file' => 'VIPRestrictedCommandsCheck8.inc', 'lines' => ++$line ),
			array( 'slug' => 'show_admin_bar',            'level' => 'Blocker', 'description' => "The WordPress.com admin bar cannot be removed as it’s integral to the user experience on WordPress.com", 'file' => 'VIPRestrictedCommandsCheck8.inc', 'lines' => ++$line ),
			array( 'slug' => 'serialize',                 'level' => 'Blocker', 'description' => "Serialized data has <a href='https://www.owasp.org/index.php/PHP_Object_Injection'>known vulnerability problems</a> with Object Injection. JSON is generally a better approach for serializing data.", 'file' => 'VIPRestrictedCommandsCheck8.inc', 'lines' => ++$line ),
			array( 'slug' => 'unserialize',               'level' => 'Blocker', 'description' => "Serialized data has <a href='https://www.owasp.org/index.php/PHP_Object_Injection'>known vulnerability problems</a> with Object Injection. JSON is generally a better approach for serializing data.", 'file' => 'VIPRestrictedCommandsCheck8.inc', 'lines' => ++$line ),		);
		$actual_errors = $this->checkFile( 'VIPRestrictedCommandsCheck8.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}

	public function testWidgets() {
		$expected_errors = array(
			array( 'slug' => 'WP_Widget_Tag_Cloud', 'level' => 'Warning', 'description' => "Using WP_Widget_Tag_Cloud, use WPCOM_Tag_Cloud_Widget instead", 'file' => 'VIPRestrictedCommandsCheck9.inc', 'lines' => 4 ),
		);
		$actual_errors = $this->checkFile( 'VIPRestrictedCommandsCheck9.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}

	public function testFilesystem() {
		$line = 4;
		$expected_errors = array(
			//array( 'slug' => 'basename',  'level' => 'Note', 'description' => "Returns filename component of path", 'lines' => ++$line ),
			array( 'slug' => 'chgrp', 'level' => 'Blocker', 'description' => "Changes file group", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'chmod', 'level' => 'Blocker', 'description' => "Changes file mode",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'chown', 'level' => 'Blocker', 'description' => "Changes file owner", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),

			array( 'slug' => 'clearstatcache',  'level' => 'Blocker', 'description' => "Clears file status cache", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'set_file_buffer', 'level' => 'Warning', 'description' => "Alias of stream_set_write_buffer", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),

			array( 'slug' => 'copy',             'level' => 'Blocker', 'description' => "Copies file", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'curl_init',        'level' => 'Blocker', 'description' => "cURL used. Should use WP_HTTP class or cached functions instead", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'curl_setopt',      'level' => 'Blocker', 'description' => "cURL used. Should use WP_HTTP class or cached functions instead", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'curl_exec',        'level' => 'Blocker', 'description' => "cURL used. Should use WP_HTTP class or cached functions instead", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'curl_close',       'level' => 'Blocker', 'description' => "cURL used. Should use WP_HTTP class or cached functions instead", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'delete',           'level' => 'Blocker', 'description' => "See unlink or unset", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			//array( 'slug' => 'dirname',  'level' => 'Warning', 'description' => "Returns directory name component of path", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'disk_free_space',  'level' => 'Blocker', 'description' => "Returns available space in directory", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'disk_total_space', 'level' => 'Blocker', 'description' => "Returns the total size of a directory", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'diskfreespace',    'level' => 'Blocker', 'description' => "Alias of disk_free_space", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),

			array( 'slug' => 'fclose',  'level' => 'Warning', 'description' => "Closes an open file pointer", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'feof',    'level' => 'Warning', 'description' => "Tests for end-of-file on a file pointer", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fflush',  'level' => 'Blocker', 'description' => "Flushes the output to a file", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fgetc',   'level' => 'Warning', 'description' => "Gets character from file pointer", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fgetcsv', 'level' => 'Warning', 'description' => "Gets line from file pointer and parse for CSV fields", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fgets',   'level' => 'Warning', 'description' => "Gets line from file pointer", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fgetss',  'level' => 'Warning', 'description' => "Gets line from file pointer and strip HTML tags", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),

			//array( 'slug' => 'file_exists',  'level' => 'Warning', 'description' => "Checks whether a file or directory exists", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'file_get_contents',  'level' => 'Blocker', 'description' => "Use wpcom_vip_file_get_contents() instead", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'file_put_contents',  'level' => 'Blocker', 'description' => "Write a string to a file", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),

			array( 'slug' => 'file',      'level' => 'Warning', 'description' => "Reads entire file into an array", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fileatime', 'level' => 'Blocker', 'description' => "Gets last access time of file", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'filectime', 'level' => 'Blocker', 'description' => "Gets inode change time of file", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'filegroup', 'level' => 'Blocker', 'description' => "Gets file group", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fileinode', 'level' => 'Blocker', 'description' => "Gets file inode", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'filemtime', 'level' => 'Warning', 'description' => "Gets file modification time", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fileowner', 'level' => 'Blocker', 'description' => "Gets file owner", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fileperms', 'level' => 'Blocker', 'description' => "Gets file permissions", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'filesize',  'level' => 'Blocker', 'description' => "Gets file size; should not be called on the front-end.",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'filetype',  'level' => 'Blocker', 'description' => "Gets file type; should not be called on the front-end.",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'flock',     'level' => 'Blocker', 'description' => "Portable advisory file locking",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fnmatch',   'level' => 'Blocker', 'description' => "Match filename against a pattern",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fopen',     'level' => 'Blocker', 'description' => "Opens file or URL",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fpassthru', 'level' => 'Warning', 'description' => "Output all remaining data on a file pointer",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fputcsv',   'level' => 'Blocker', 'description' => "Format line as CSV and write to file pointer",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fputs',     'level' => 'Blocker', 'description' => "Alias of fwrite",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fread',     'level' => 'Warning', 'description' => "Binary-safe file read",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fscanf',    'level' => 'Warning', 'description' => "Parses input from a file according to a format",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fseek',     'level' => 'Warning', 'description' => "Seeks on a file pointer",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fstat',     'level' => 'Warning', 'description' => "Gets information about a file using an open file pointer",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'ftell',     'level' => 'Warning', 'description' => "Returns the current position of the file read/write pointer",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'ftruncate', 'level' => 'Blocker', 'description' => "Truncates a file to a given length",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'fwrite',    'level' => 'Blocker', 'description' => "Binary-safe file write",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'glob',      'level' => 'Blocker', 'description' => "Find pathnames matching a pattern",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'is_dir',    'level' => 'Note', 'description' => "Tells whether the filename is a directory",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'is_file',   'level' => 'Note', 'description' => "Tells whether the filename is a regular file",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'is_link',   'level' => 'Note', 'description' => "Tells whether the filename is a symbolic link",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),

			//array( 'slug' => 'is_readable',    'level' => 'Warning', 'description' => "Tells whether the filename is readable",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'is_executable',      'level' => 'Blocker', 'description' => "Tells whether the filename is executable",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'is_uploaded_file',   'level' => 'Warning', 'description' => "Tells whether the file was uploaded via HTTP POST",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'move_uploaded_file', 'level' => 'Blocker', 'description' => "Moves an uploaded file to a new location",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'is_writable',        'level' => 'Warning', 'description' => "Tells whether the filename is writable",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'is_writeable',       'level' => 'Warning', 'description' => "Alias of is_writable",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),

			array( 'slug' => 'parse_ini_file',   'level' => 'Warning', 'description' => "Parse a configuration file",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'parse_ini_string', 'level' => 'Warning', 'description' => "Parse a configuration string",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),

			array( 'slug' => 'lchgrp',   'level' => 'Blocker', 'description' => "Changes group ownership of symlink",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'lchown',   'level' => 'Blocker', 'description' => "Changes user ownership of symlink",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'link',     'level' => 'Blocker', 'description' => "Create a hard link",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'linkinfo', 'level' => 'Warning', 'description' => "Gets information about a link",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'lstat',    'level' => 'Warning', 'description' => "Gives information about a file or symbolic link",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'mkdir',    'level' => 'Blocker', 'description' => "Makes directory",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'pathinfo', 'level' => 'Warning', 'description' => "Returns information about a file path",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'pclose',   'level' => 'Blocker', 'description' => "Closes process file pointer",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'popen',    'level' => 'Blocker', 'description' => "Opens process file pointer",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'readfile', 'level' => 'Warning', 'description' => "Outputs a file",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'readlink', 'level' => 'Warning', 'description' => "Returns the target of a symbolic link",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'realpath', 'level' => 'Warning', 'description' => "Returns canonicalized absolute pathname",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'rename',   'level' => 'Blocker', 'description' => "Renames a file or directory",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'rewind',   'level' => 'Warning', 'description' => "Rewind the position of a file pointer",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'rmdir',    'level' => 'Blocker', 'description' => "Removes directory",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),

			array( 'slug' => 'stat',    'level' => 'Warning', 'description' => "Gives information about a file",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'symlink', 'level' => 'Blocker', 'description' => "Creates a symbolic link",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'tempnam', 'level' => 'Warning', 'description' => "Create file with unique file name",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'tmpfile', 'level' => 'Blocker', 'description' => "Creates a temporary file",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'touch',   'level' => 'Blocker', 'description' => "Sets access and modification time of file",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'umask',   'level' => 'Blocker', 'description' => "Changes the current umask",  'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
			array( 'slug' => 'unlink',  'level' => 'Blocker', 'description' => "Deletes a file", 'file' => 'VIPRestrictedCommandsCheck10.inc', 'lines' => ++$line ),
		);
		$actual_errors = $this->checkFile( 'VIPRestrictedCommandsCheck10.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}

	public function testProcessControl() {
		$line = 3;
		$expected_errors = array(
			array( 'slug' => 'pcntl_alarm',           'level' => 'Blocker', 'description' => "Set an alarm clock for delivery of a signal",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
			array( 'slug' => 'pcntl_exec', 	          'level' => 'Blocker', 'description' => "Executes specified program in current process space",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
			array( 'slug' => 'pcntl_fork',            'level' => 'Blocker', 'description' => "Forks the currently running process",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
			array( 'slug' => 'pcntl_getpriority',     'level' => 'Blocker', 'description' => "Get the priority of any process",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
			array( 'slug' => 'pcntl_setpriority',     'level' => 'Blocker', 'description' => "Change the priority of any process",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
			array( 'slug' => 'pcntl_signal_dispatch', 'level' => 'Blocker', 'description' => "Calls signal handlers for pending signals",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
			array( 'slug' => 'pcntl_signal',          'level' => 'Blocker', 'description' => "Installs a signal handler",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
			array( 'slug' => 'pcntl_sigprocmask',     'level' => 'Blocker', 'description' => "Sets and retrieves blocked signals",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
			array( 'slug' => 'pcntl_sigtimedwait',    'level' => 'Blocker', 'description' => "Waits for signals, with a timeout",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
			array( 'slug' => 'pcntl_sigwaitinfo',     'level' => 'Blocker', 'description' => "Waits for signals",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
			array( 'slug' => 'pcntl_wait',            'level' => 'Blocker', 'description' => "Waits on or returns the status of a forked child",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
			array( 'slug' => 'pcntl_waitpid',         'level' => 'Blocker', 'description' => "Waits on or returns the status of a forked child",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
			array( 'slug' => 'pcntl_wexitstatus',     'level' => 'Blocker', 'description' => "Returns the return code of a terminated child",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
			array( 'slug' => 'pcntl_wifexited',       'level' => 'Blocker', 'description' => "Checks if status code represents a normal exit",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
			array( 'slug' => 'pcntl_wifsignaled',     'level' => 'Blocker', 'description' => "Checks whether the status code represents a termination due to a signal",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
			array( 'slug' => 'pcntl_wifstopped',      'level' => 'Blocker', 'description' => "Checks whether the child process is currently stopped",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
			array( 'slug' => 'pcntl_wstopsig',        'level' => 'Blocker', 'description' => "Returns the signal which caused the child to stop",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
			array( 'slug' => 'pcntl_wtermsig',        'level' => 'Blocker', 'description' => "Returns the signal which caused the child to terminate",  'file' => 'VIPRestrictedCommandsCheck11.inc', 'lines' => ++$line ),
		);
		$actual_errors = $this->checkFile( 'VIPRestrictedCommandsCheck11.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}

	public function testSession() {
		$line = 3;
		$expected_errors = array(
			array( 'slug' => 'session_cache_expire', 		 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_cache_limiter', 	 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_commit', 			 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_decode', 			 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_destroy', 			 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_encode', 			 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_get_cookie_params',  'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_id', 				 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_is_registered', 	 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_module_name', 		 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_name', 				 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_regenerate_id', 	 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_register_shutdown',  'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_register', 			 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_save_path', 		 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_set_cookie_params',  'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_set_save_handler', 	 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_start', 			 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_status', 			 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_unregister', 		 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_unset', 			 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
			array( 'slug' => 'session_write_close', 		 'level' => 'Blocker', 'description' => "Using session function",  'file' => 'VIPRestrictedCommandsCheck12.inc', 'lines' => ++$line ),
		);
		$actual_errors = $this->checkFile( 'VIPRestrictedCommandsCheck12.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}

	public function testMysql() {
		$line = 3;
		$expected_errors = array(
			// direct mysql usage
			array( 'slug' => 'mysql_affected_rows', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_client_encoding', 	 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_close', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_connect', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_create_db', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_data_seek', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_db_name', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_db_query', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_drop_db', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_errno', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_error', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_escape_string', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_fetch_array', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_fetch_assoc', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_fetch_field', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_fetch_lengths', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_fetch_object', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_fetch_row', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_field_flags', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_field_len', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_field_name', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_field_seek', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_field_table', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_field_type', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_free_result', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_get_client_info', 	 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_get_host_info', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_get_proto_info', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_get_server_info', 	 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_info', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_insert_id', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_list_dbs', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_list_fields', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_list_processes', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_list_tables', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_num_fields', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_num_rows', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_pconnect', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_ping', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_query', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_real_escape_string',	 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_result', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_select_db', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_set_charset', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_stat', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_tablename', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_thread_id', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysql_unbuffered_query', 	 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),

			// mysqli http://www.php.net/manual/en/mysqli.summary.php
			array( 'slug' => 'mysqli', 							 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_affected_rows', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_get_client_info', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_get_client_version', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_connect_errno', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_connect_error', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_errno', 						 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_error', 						 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_field_count', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_get_host_info', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_get_proto_info', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_get_server_info', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_get_server_version', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_info', 						 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_insert_id', 					 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_sqlstate', 					 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_warning_count', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_autocommit', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_change_user', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_character_set_name', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_client_encoding', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_close', 						 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_commit', 					 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_connect', 					 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_debug', 						 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_dump_debug_info', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_get_charset', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_get_connection_stats',		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_get_client_stats', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_get_cache_stats', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_get_warnings', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_init', 						 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_kill', 						 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_more_results', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_multi_query', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_next_result', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_options', 					 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_set_opt', 					 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_ping', 						 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_prepare', 					 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_query', 						 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_real_connect', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_real_escape_string', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_escape_string', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_real_query', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_refresh', 					 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_rollback', 					 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_select_db', 					 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_set_charset', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_set_local_infile_default',	 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_set_local_infile_handler', 	 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_ssl_set', 					 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stat', 						 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_init', 					 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_store_result', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_thread_id', 					 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_thread_safe', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_use_result', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),

			// mysqli statements
			array( 'slug' => 'mysqli_stmt_affected_rows', 	 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_errno', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_error', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_field_count', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_insert_id', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_num_rows', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_param_count', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_param_count', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_sqlstate', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_attr_get', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_attr_set', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_bind_param', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_bind_param', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_bind_result', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_bind_result', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_close', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_data_seek', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_execute', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_execute', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_fetch', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_fetch', 					 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_free_result', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_get_result', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_get_warnings', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_more_results', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_next_result', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_prepare', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_reset', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_result_metadata', 	 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_get_metadata', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_send_long_data', 	 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_stmt_store_result', 		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_field_tell', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_num_fields', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_fetch_lengths', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_num_rows', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_data_seek', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_fetch_all', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_fetch_array', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_fetch_assoc', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_fetch_field_direct',		 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_fetch_field', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_fetch_fields', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_fetch_object', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_fetch_row', 				 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_field_seek', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_free_result', 			 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_embedded_server_end', 	 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
			array( 'slug' => 'mysqli_embedded_server_start', 	 'level' => 'Blocker', 'description' => "Direct MySQL usage, use WP APIs instead", 'file' => 'VIPRestrictedCommandsCheck13.inc', 'lines' => ++$line ),
		);
		$actual_errors = $this->checkFile( 'VIPRestrictedCommandsCheck13.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}

	public function testXml() {
		$expected_errors = array(
			array( 'slug' => 'libxml_set_external_entity_loader',  'level' => 'Blocker', 'description' => 'Modifying the XML entity loader is disabled for security reasons.', 'file' => 'VIPRestrictedCommandsCheck14.inc', 'lines' => 4 ),
		);
		$actual_errors = $this->checkFile( 'VIPRestrictedCommandsCheck14.inc' );
		$this->assertEqualErrors( $expected_errors, $actual_errors );
	}
}
