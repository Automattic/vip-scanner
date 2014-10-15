<?php

class VIPRestrictedCommandsCheckTest extends WP_UnitTestCase {
	protected $_VIPRestrictedCommandsCheck;

	public function setUp() {
		parent::setUp();
		require_once VIP_SCANNER_DIR . '/checks/VIPRestrictedCommandsCheck.php';

		$this->_VIPRestrictedCommandsCheck = new VIPRestrictedCommandsCheck();
	}


	/**
	 * Build input for the given commands, then perform a restricted commands check to ensure they are all flagged
	 *
	 * @param  array $commands Array of command (function) names that should be caught by the scanner
	 */
	public function checkCommands( $commands ) {
		$commands_formatted = array_map( array( $this, 'add_parenthesis_to_command_name' ), $commands );

		$test_input = '<?php ' . "\n\n" . implode( "\n\n", $commands_formatted );

		$input = array( 
			'php' => array(
				'test.php' => $test_input
			)
		);

		$result = $this->_VIPRestrictedCommandsCheck->check( $input );

		$errors = $this->_VIPRestrictedCommandsCheck->get_errors();

		$error_slugs = wp_list_pluck( $errors, 'slug' );

		// Assert the scanner caught all commands
		foreach( $commands as $command ) {
			$this->assertContains( $command, $error_slugs );
		}
	}

	/**
	 * Take a command's slug (the name of the function) and add on some parenthesis and a semi-colon to
	 * make it vaild PHP for testing
	 * 
	 * @param string $command The name of a function to add parenthesis to
	 */
	public function add_parenthesis_to_command_name( $command ) {
		return $command . '();';
	}

	public function testRestrictedWPCore() {
		$restricted_commands = array(
			'update_post_caches',
			'update_option',
			'get_option',
			'add_option',
			'delete_option',
			'wp_remote_get',
			'fetch_feed',
			'wp_schedule_event',
			'wp_schedule_single_event',
			'wp_clear_scheduled_hook',
			'wp_next_scheduled',
			'wp_unschedule_event',
			'wp_get_schedule',
			'add_feed',
			'query_posts'
		);
		
		$this->checkCommands( $restricted_commands );
	}

	public function testMultisite() {
		$restricted_commands = array(
			'switch_to_blog',
			'restore_current_blog',
			'ms_is_switched',
			'wp_get_sites'
		);
		
		$this->checkCommands( $restricted_commands );
	}

	public function testUncached() {
		$restricted_commands = array(
			'get_category_by_slug',
			'wp_get_post_categories',
			'wp_get_post_tags',
			'get_cat_ID',
			'get_term_by',
			'get_page_by_title',
			'get_page_by_path',
			'wp_get_object_terms',
			'wp_get_post_terms',
			'get_posts',
			'wp_get_recent_posts',
			'wpcom_uncached_get_post_meta',
			'wpcom_uncached_get_post_by_meta'
		);

		$this->checkCommands( $restricted_commands );
	}

	public function testNeedingExtraCare() {
		$restricted_commands = array(
			'wpcom_vip_load_custom_cdn'
		);

		$this->checkCommands( $restricted_commands );
	}

	public function testRoleModifications() {
		$restricted_commands = array(
			'get_role',
			'add_role',
			'remove_role',
			'add_cap',
			'remove_cap'
		);

		$this->checkCommands( $restricted_commands );
	}

	public function testUserMeta() {
		$restricted_commands = array(
			'add_user_meta',
			'delete_user_meta',
			'get_user_meta',
			'update_user_meta'
		);

		$this->checkCommands( $restricted_commands );
	}

	public function testDebugging() {
		$restricted_commands = array(
			'error_log',
			'var_dump',
			'print_r',
			'var_export'
		);

		$this->checkCommands( $restricted_commands );
	}

	public function testOther() {
		$restricted_commands = array(
			'date_default_timezone_set',
			'error_reporting',
			'filter_input',
			'eval',
			'create_function',
			'extract',
			'ini_set',
			'wp_is_mobile'
		);

		$this->checkCommands( $restricted_commands );
	}

	public function testWidgets() {
		$restricted_commands = array(
			'WP_Widget_Tag_Cloud'
		);

		$this->checkCommands( $restricted_commands );
	}

	public function testFilesystem() {
		$restricted_commands = array(
			'chgrp',
			'chmod',
			'chown',
			'clearstatcache',
			'set_file_buffer',
			'copy',
			'curl_init',
			'curl_setopt',
			'curl_exec',
			'curl_close',
			'delete',
			'disk_free_space',
			'disk_total_space',
			'diskfreespace',
			'fclose',
			'feof',
			'fflush',
			'fgetc',
			'fgetcsv',
			'fgets',
			'fgetss',
			'file_get_contents',
			'file_put_contents',
			'file',
			'fileatime',
			'filectime',
			'filegroup',
			'fileinode',
			'filemtime',
			'fileowner',
			'fileperms',
			'filesize',
			'filetype',
			'flock',
			'fnmatch',
			'fopen',
			'fpassthru',
			'fputcsv',
			'fputs',
			'fread',
			'fscanf',
			'fseek',
			'fstat',
			'ftell',
			'ftruncate',
			'fwrite',
			'glob',
			'is_dir',
			'is_file',
			'is_link',
			'is_executable',
			'is_uploaded_file',
			'move_uploaded_file',
			'is_writable',
			'is_writeable',
			'parse_ini_file',
			'parse_ini_string',
			'lchgrp',
			'lchown',
			'link',
			'linkinfo',
			'lstat',
			'mkdir',
			'pathinfo',
			'pclose',
			'popen',
			'readfile',
			'readlink',
			'realpath',
			'rename',
			'rewind',
			'rmdir',
			'stat',
			'symlink',
			'tempnam',
			'tmpfile',
			'touch',
			'umask',
			'unlink'
		);

		$this->checkCommands( $restricted_commands );
	}

	public function testProcessControl() {
		$restricted_commands = array(
			'pcntl_alarm',
			'pcntl_exec',
			'pcntl_fork',
			'pcntl_getpriority',
			'pcntl_setpriority',
			'pcntl_signal_dispatch',
			'pcntl_signal',
			'pcntl_sigprocmask',
			'pcntl_sigtimedwait',
			'pcntl_sigwaitinfo',
			'pcntl_wait',
			'pcntl_waitpid',
			'pcntl_wexitstatus',
			'pcntl_wifexited',
			'pcntl_wifsignaled',
			'pcntl_wifstopped',
			'pcntl_wstopsig',
			'pcntl_wtermsig'
		);

		$this->checkCommands( $restricted_commands );
	}

	public function testSession() {
		$restricted_commands = array(
			'session_cache_expire',
			'session_cache_limiter',
			'session_commit',
			'session_decode',
			'session_destroy',
			'session_encode',
			'session_get_cookie_params',
			'session_id',
			'ssession_is_registered',
			'session_module_name',
			'session_name',
			'session_regenerate_id',
			'session_register_shutdown',
			'session_register',
			'session_save_path',
			'session_set_cookie_params',
			'session_set_save_handler',
			'session_start',
			'session_status',
			'session_unregister',
			'session_unset',
			'session_write_close'
		);

		$this->checkCommands( $restricted_commands );
	}

	public function testXml() {
		$restricted_commands = array(
			'libxml_set_external_entity_loader'
		);

		$this->checkCommands( $restricted_commands );
	}

	public function testMysql() {
		$restricted_commands = array(
			'mysql_affected_rows',
			'mysql_client_encoding',
			'mysql_close',
			'mysql_connect',
			'mysql_create_db',
			'mysql_data_seek',
			'mysql_db_name',
			'mysql_db_query',
			'mysql_drop_db',
			'mysql_errno',
			'mysql_error',
			'mysql_escape_string',
			'mysql_fetch_array',
			'mysql_fetch_assoc',
			'mysql_fetch_field',
			'mysql_fetch_lengths',
			'mysql_fetch_object',
			'mysql_fetch_row',
			'mysql_field_flags',
			'mysql_field_len',
			'mysql_field_name',
			'mysql_field_seek',
			'mysql_field_table',
			'mysql_field_type',
			'mysql_free_result',
			'mysql_get_client_info',
			'mysql_get_host_info',
			'mysql_get_proto_info',
			'mysql_get_server_info',
			'mysql_info',
			'mysql_insert_id',
			'mysql_list_dbs',
			'mysql_list_fields',
			'mysql_list_processes',
			'mysql_list_tables',
			'mysql_num_fields',
			'mysql_num_rows',
			'mysql_pconnect',
			'mysql_ping',
			'mysql_query',
			'mysql_real_escape_string',
			'mysql_result',
			'mysql_select_db',
			'mysql_set_charset',
			'mysql_stat',
			'mysql_tablename',
			'mysql_thread_id',
			'mysql_unbuffered_query',
			'mysqli',
			'mysqli_affected_rows',
			'mysqli_get_client_info',
			'mysqli_get_client_version',
			'mysqli_connect_errno',
			'mysqli_connect_error',
			'mysqli_errno',
			'mysqli_error',
			'mysqli_field_count',
			'mysqli_get_host_info',
			'mysqli_get_proto_info',
			'mysqli_get_server_info',
			'mysqli_get_server_version',
			'mysqli_info',
			'mysqli_insert_id',
			'mysqli_sqlstate',
			'mysqli_warning_count',
			'mysqli_autocommit',
			'mysqli_change_user',
			'mysqli_character_set_name',
			'mysqli_client_encoding',
			'mysqli_close',
			'mysqli_commit',
			'mysqli_connect',
			'mysqli_debug',
			'mysqli_dump_debug_info',
			'mysqli_get_charset',
			'mysqli_get_connection_stats',
			'mysqli_get_client_stats',
			'mysqli_get_cache_stats',
			'mysqli_get_warnings',
			'mysqli_init',
			'mysqli_kill',
			'mysqli_more_results',
			'mysqli_multi_query',
			'mysqli_next_result',
			'mysqli_options',
			'mysqli_set_opt()',
			'mysqli_ping',
			'mysqli_prepare',
			'mysqli_query',
			'mysqli_real_connect',
			'mysqli_real_escape_string',
			'mysqli_escape_string',
			'mysqli_real_query',
			'mysqli_refresh',
			'mysqli_rollback',
			'mysqli_select_db',
			'mysqli_set_charset',
			'mysqli_set_local_infile_default',
			'mysqli_set_local_infile_handler',
			'mysqli_ssl_set',
			'mysqli_stat',
			'mysqli_stmt_init',
			'mysqli_store_result',
			'mysqli_thread_id',
			'mysqli_thread_safe',
			'mysqli_use_result',
			'mysqli_stmt_affected_rows',
			'mysqli_stmt_errno',
			'mysqli_stmt_error',
			'mysqli_stmt_field_count',
			'mysqli_stmt_insert_id',
			'mysqli_stmt_num_rows',
			'mysqli_stmt_param_count',
			'mysqli_param_count',
			'mysqli_stmt_sqlstate',
			'mysqli_stmt_attr_get',
			'mysqli_stmt_attr_set',
			'mysqli_stmt_bind_param',
			'mysqli_bind_param',
			'mysqli_stmt_bind_result',
			'mysqli_bind_result',
			'mysqli_stmt_close',
			'mysqli_stmt_data_seek',
			'mysqli_stmt_execute',
			'mysqli_execute',
			'mysqli_stmt_fetch',
			'mysqli_fetch',
			'mysqli_stmt_free_result',
			'mysqli_stmt_get_result',
			'mysqli_stmt_get_warnings',
			'mysqli_stmt_more_results',
			'mysqli_stmt_next_result',
			'mysqli_stmt_prepare',
			'mysqli_stmt_reset',
			'mysqli_stmt_result_metadata',
			'mysqli_get_metadata',
			'mysqli_stmt_send_long_data',
			'mysqli_stmt_store_result',
			'mysqli_field_tell',
			'mysqli_num_fields',
			'mysqli_fetch_lengths',
			'mysqli_num_rows',
			'mysqli_data_seek',
			'mysqli_fetch_all',
			'mysqli_fetch_array',
			'mysqli_fetch_assoc',
			'mysqli_fetch_field_direct',
			'mysqli_fetch_field',
			'mysqli_fetch_fields',
			'mysqli_fetch_object',
			'mysqli_fetch_row',
			'mysqli_field_seek',
			'mysqli_free_result',
			'mysqli_embedded_server_end',
			'mysqli_embedded_server_start'
		);

		$this->checkCommands( $restricted_commands );
	}

	public function testShowAdminBar() {
		$restricted_commands = array(
			'show_admin_bar'
		);

		$this->checkCommands( $restricted_commands );
	}
}

