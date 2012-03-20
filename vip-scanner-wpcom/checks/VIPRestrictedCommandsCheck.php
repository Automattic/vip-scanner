<?php

class VIPRestrictedCommandsCheck extends BaseCheck
{
	function check( $files ) {
		$result = true;

		$checks = array(
			// wordpress functions
			"remove_filter" => array( "level" => "Warning", "note" => "Removing filters" ),
			"remove_action" => array( "level" => "Warning", "note" => "Removing actions" ),
			"add_filter" => array( "level" => "Note", "note" => "Altering filters" ),
			"add_action" => array( "level" => "Note", "note" => "Altering actions" ),

			"wp_cache_set" => array( "level" => "Warning", "note" => "Setting Cache Object" ),
			"wp_cache_get" => array( "level" => "Note", "note" => "Getting Cache Object" ),
			"wp_cache_add" => array( "level" => "Warning", "note" => "Adding Cache Object" ),
			"wp_cache_delete" => array( "level" => "Warning", "note" => "Deleting Cache Object" ),
			"set_transient" => array( "level" => "Warning", "note" => "Setting transient Object" ),
			"get_transient" => array( "level" => "Note", "note" => "Getting transient Object" ),
			"delete_transient" => array( "level" => "Warning", "note" => "Deleting transient Object" ),

			"update_post_caches" => array( "level" => "Warning", "note" => "Post cache alteration" ),

			"update_option" => array( "level" => "Warning", "note" => "Updating option" ),
			"get_option" => array( "level" => "Note", "note" => "Getting option" ),
			"add_option" => array( "level" => "Warning", "note" => "Adding Option" ),
			"delete_option" => array( "level" => "Warning", "note" => "Deleting Option" ),

			"wp_remote_get" => array( "level" => "Warning", "note" => "Remote operation" ),
			"fetch_feed" => array( "level" => "Warning", "note" => "Remote feed operation" ),

			"wp_schedule_event" => array( "level" => "Warning", "note" => "WP Cron usage" ),
			"wp_schedule_single_event" => array( "level" => "Warning", "note" => "WP Cron usage" ),
			"wp_clear_scheduled_hook" => array( "level" => "Warning", "note" => "WP Cron usage" ),
			"wp_next_scheduled" => array( "level" => "Warning", "note" => "WP Cron usage" ),
			"wp_unschedule_event" => array( "level" => "Warning", "note" => "WP Cron usage" ),
			"wp_get_schedule" => array( "level" => "Warning", "note" => "WP Cron usage" ),

			"add_feed" => array( "level" => "Warning", "note" => "Custom feed implementation" ),

			// Uncached functions
			'get_category_by_slug' => array( 'level' => 'Warning', 'note' => 'Uncached function. Should be used on a limited basis or cached' ),
			'get_cat_ID' => array( 'level' => 'Warning', 'note' => 'Uncached function. Should be used on a limited basis or cached' ),
			'get_term_by' => array( 'level' => 'Warning', 'note' => 'Uncached function. Should be used on a limited basis or cached' ),
			'get_page_by_title' => array( 'level' => 'Warning', 'note' => 'Uncached function. Should be used on a limited basis or cached' ),
			'wp_get_object_terms' => array( 'level' => 'Warning', 'note' => 'Uncached function. Should be used on a limited basis or cached' ),

			// Object cache bypass
			"wpcom_uncached_get_post_meta" => array( "level" => "Warning", "note" => "Bypassing object cache, please validate" ),
			"wpcom_uncached_get_post_by_meta" => array( "level" => "Warning", "note" => "Bypassing object cache, please validate" ),

			// Role modifications
			"get_role" => array( "level" => "Warning", "note" => "Role access" ),
			"add_role" => array( "level" => "Blocker", "note" => "Role modification" ),
 			"remove_role" => array( "level" => "Blocker", "note" => "Role modification" ),
 			"add_cap" => array( "level" => "Blocker", "note" => "Role modification" ),
 			"remove_cap" => array( "level" => "Blocker", "note" => "Role modification" ),

			// debugging
			"error_log" => array( "level" => "Blocker", "note" => "Filesystem operation" ),
			"var_dump" => array( "level" => "Warning", "note" => "Unfiltered variable output" ),
			"print_r" => array( "level" => "Warning", "note" => "Unfiltered variable output" ),
			"var_export" => array( "level" => "Warning", "note" => "Unfiltered variable output" ),

			// other
			"date_default_timezone_set" => array( "level" => "Blocker", "note" => "Timezone manipulation" ),
			"error_reporting" => array( "level" => "Blocker", "note" => "Settings alteration" ),
			'eval' => array( 'level' => 'Blocker', "note" => "Meta programming" ),
			"ini_set" => array( "level" => "Blocker", "note" => "Settings alteration" ),

			// filesystem functions
			//"basename" => array( "level" => "Note", "note" => "Returns filename component of path" ),
			"chgrp" => array( "level" => "Blocker", "note" => "Changes file group" ),
			"chmod" => array( "level" => "Blocker", "note" => "Changes file mode" ),
			"chown" => array( "level" => "Blocker", "note" => "Changes file owner" ),
			"clearstatcache" => array( "level" => "Blocker", "note" => "Clears file status cache" ),
			"copy" => array( "level" => "Blocker", "note" => "Copies file" ),
			"curl_init" => array( "level" => "Blocker", "note" => "cURL used. Should use WP_HTTP class or cached functions instead" ),
			"curl_setopt" => array( "level" => "Blocker", "note" => "cURL used. Should use WP_HTTP class or cached functions instead" ),
 			"curl_exec" => array( "level" => "Blocker", "note" => "cURL used. Should use WP_HTTP class or cached functions instead" ),
 			"curl_close" => array( "level" => "Blocker", "note" => "cURL used. Should use WP_HTTP class or cached functions instead" ),
			"delete" => array( "level" => "Blocker", "note" => "See unlink or unset" ),
			//"dirname" => array( "level" => "Warning", "note" => "Returns directory name component of path" ),
			"disk_free_space" => array( "level" => "Warning", "note" => "Returns available space in directory" ),
			"disk_total_space" => array( "level" => "Warning", "note" => "Returns the total size of a directory" ),
			"diskfreespace" => array( "level" => "Warning", "note" => "Alias of disk_free_space" ),
			"fclose" => array( "level" => "Warning", "note" => "Closes an open file pointer" ),
			"feof" => array( "level" => "Warning", "note" => "Tests for end-of-file on a file pointer" ),
			"fflush" => array( "level" => "Blocker", "note" => "Flushes the output to a file" ),
			"fgetc" => array( "level" => "Warning", "note" => "Gets character from file pointer" ),
			"fgetcsv" => array( "level" => "Warning", "note" => "Gets line from file pointer and parse for CSV fields" ),
			"fgets" => array( "level" => "Warning", "note" => "Gets line from file pointer" ),
			"fgetss" => array( "level" => "Warning", "note" => "Gets line from file pointer and strip HTML tags" ),
			//"file_exists" => array( "level" => "Warning", "note" => "Checks whether a file or directory exists" ),
			"file_get_contents" => array( "level" => "Warning", "note" => "Reads entire file into a string" ),
			"file_put_contents" => array( "level" => "Blocker", "note" => "Write a string to a file" ),
			"file" => array( "level" => "Warning", "note" => "Reads entire file into an array" ),
			"fileatime" => array( "level" => "Warning", "note" => "Gets last access time of file" ),
			"filectime" => array( "level" => "Warning", "note" => "Gets inode change time of file" ),
			"filegroup" => array( "level" => "Warning", "note" => "Gets file group" ),
			"fileinode" => array( "level" => "Warning", "note" => "Gets file inode" ),
			"filemtime" => array( "level" => "Warning", "note" => "Gets file modification time" ),
			"fileowner" => array( "level" => "Warning", "note" => "Gets file owner" ),
			"fileperms" => array( "level" => "Warning", "note" => "Gets file permissions" ),
			"filesize" => array( "level" => "Warning", "note" => "Gets file size" ),
			"filetype" => array( "level" => "Warning", "note" => "Gets file type" ),
			"flock" => array( "level" => "Warning", "note" => "Portable advisory file locking" ),
			"fnmatch" => array( "level" => "Warning", "note" => "Match filename against a pattern" ),
			"fopen" => array( "level" => "Blocker", "note" => "Opens file or URL" ),
			"fpassthru" => array( "level" => "Warning", "note" => "Output all remaining data on a file pointer" ),
			"fputcsv" => array( "level" => "Blocker", "note" => "Format line as CSV and write to file pointer" ),
			"fputs" => array( "level" => "Blocker", "note" => "Alias of fwrite" ),
			"fread" => array( "level" => "Warning", "note" => "Binary-safe file read" ),
			"fscanf" => array( "level" => "Warning", "note" => "Parses input from a file according to a format" ),
			"fseek" => array( "level" => "Warning", "note" => "Seeks on a file pointer" ),
			"fstat" => array( "level" => "Warning", "note" => "Gets information about a file using an open file pointer" ),
			"ftell" => array( "level" => "Warning", "note" => "Returns the current position of the file read/write pointer" ),
			"ftruncate" => array( "level" => "Blocker", "note" => "Truncates a file to a given length" ),
			"fwrite" => array( "level" => "Blocker", "note" => "Binary-safe file write" ),
			"glob" => array( "level" => "Warning", "note" => "Find pathnames matching a pattern" ),
			"is_dir" => array( "level" => "Warning", "note" => "Tells whether the filename is a directory" ),
			"is_executable" => array( "level" => "Warning", "note" => "Tells whether the filename is executable" ),
			"is_file" => array( "level" => "Warning", "note" => "Tells whether the filename is a regular file" ),
			"is_link" => array( "level" => "Warning", "note" => "Tells whether the filename is a symbolic link" ),
			//"is_readable" => array( "level" => "Warning", "note" => "Tells whether the filename is readable" ),
			"is_uploaded_file" => array( "level" => "Warning", "note" => "Tells whether the file was uploaded via HTTP POST" ),
			"is_writable" => array( "level" => "Warning", "note" => "Tells whether the filename is writable" ),
			"is_writeable" => array( "level" => "Warning", "note" => "Alias of is_writable" ),
			"lchgrp" => array( "level" => "Blocker", "note" => "Changes group ownership of symlink" ),
			"lchown" => array( "level" => "Blocker", "note" => "Changes user ownership of symlink" ),
			"link" => array( "level" => "Blocker", "note" => "Create a hard link" ),
			"linkinfo" => array( "level" => "Warning", "note" => "Gets information about a link" ),
			"lstat" => array( "level" => "Warning", "note" => "Gives information about a file or symbolic link" ),
			"mkdir" => array( "level" => "Blocker", "note" => "Makes directory" ),
			"move_uploaded_file" => array( "level" => "Blocker", "note" => "Moves an uploaded file to a new location" ),
			"parse_ini_file" => array( "level" => "Warning", "note" => "Parse a configuration file" ),
			"parse_ini_string" => array( "level" => "Warning", "note" => "Parse a configuration string" ),
			"pathinfo" => array( "level" => "Warning", "note" => "Returns information about a file path" ),
			"pclose" => array( "level" => "Warning", "note" => "Closes process file pointer" ),
			"popen" => array( "level" => "Blocker", "note" => "Opens process file pointer" ),
			"readfile" => array( "level" => "Warning", "note" => "Outputs a file" ),
			"readlink" => array( "level" => "Warning", "note" => "Returns the target of a symbolic link" ),
			"realpath" => array( "level" => "Warning", "note" => "Returns canonicalized absolute pathname" ),
			"rename" => array( "level" => "Blocker", "note" => "Renames a file or directory" ),
			"rewind" => array( "level" => "Warning", "note" => "Rewind the position of a file pointer" ),
			"rmdir" => array( "level" => "Blocker", "note" => "Removes directory" ),
			"session_start" => array( "level" => "Blocker", "note" => "Writes files; unreliable in a multi-server environment." ),
			"set_file_buffer" => array( "level" => "Warning", "note" => "Alias of stream_set_write_buffer" ),
			"stat" => array( "level" => "Warning", "note" => "Gives information about a file" ),
			"symlink" => array( "level" => "Blocker", "note" => "Creates a symbolic link" ),
			"tempnam" => array( "level" => "Warning", "note" => "Create file with unique file name" ),
			"tmpfile" => array( "level" => "Blocker", "note" => "Creates a temporary file" ),
			"touch" => array( "level" => "Blocker", "note" => "Sets access and modification time of file" ),
			"umask" => array( "level" => "Blocker", "note" => "Changes the current umask" ),
			"unlink" => array( "level" => "Blocker", "note" => "Deletes a file" ),

			// process control functions
			"pcntl_alarm" => array( "level" => "Blocker", "note" => "Set an alarm clock for delivery of a signal" ),
			"pcntl_exec" => array( "level" => "Blocker", "note" => "Executes specified program in current process space" ),
			"pcntl_fork" => array( "level" => "Blocker", "note" => "Forks the currently running process" ),
			"pcntl_getpriority" => array( "level" => "Blocker", "note" => "Get the priority of any process" ),
			"pcntl_setpriority" => array( "level" => "Blocker", "note" => "Change the priority of any process" ),
			"pcntl_signal_dispatch" => array( "level" => "Blocker", "note" => "Calls signal handlers for pending signals" ),
			"pcntl_signal" => array( "level" => "Blocker", "note" => "Installs a signal handler" ),
			"pcntl_sigprocmask" => array( "level" => "Blocker", "note" => "Sets and retrieves blocked signals" ),
			"pcntl_sigtimedwait" => array( "level" => "Blocker", "note" => "Waits for signals, with a timeout" ),
			"pcntl_sigwaitinfo" => array( "level" => "Blocker", "note" => "Waits for signals" ),
			"pcntl_wait" => array( "level" => "Blocker", "note" => "Waits on or returns the status of a forked child" ),
			"pcntl_waitpid" => array( "level" => "Blocker", "note" => "Waits on or returns the status of a forked child" ),
			"pcntl_wexitstatus" => array( "level" => "Blocker", "note" => "Returns the return code of a terminated child" ),
			"pcntl_wifexited" => array( "level" => "Blocker", "note" => "Checks if status code represents a normal exit" ),
			"pcntl_wifsignaled" => array( "level" => "Blocker", "note" => "Checks whether the status code represents a termination due to a signal" ),
			"pcntl_wifstopped" => array( "level" => "Blocker", "note" => "Checks whether the child process is currently stopped" ),
			"pcntl_wstopsig" => array( "level" => "Blocker", "note" => "Returns the signal which caused the child to stop" ),
			"pcntl_wtermsig" => array( "level" => "Blocker", "note" => "Returns the signal which caused the child to terminate" ),
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			foreach ( $checks as $check => $check_info ) {
				$pattern = "/\s+($check)+\s?\(+/msiU";

				$this->increment_check_count();

				if ( preg_match( $pattern, $file_content, $matches ) ) {
					$filename = $this->get_filename( $file_path );
					$error = rtrim( $matches[0], '(' );//esc_html( rtrim( $matches[0],'(') );
					$lines = $this->grep_content( rtrim( $matches[0], '(' ), $file_content );
					$this->add_error(
						$check,
						$check_info['note'],
						$check_info['level'],
						$filename,
						$lines
					);
					$result = false;
				}
			}
		}

		return $result;
	}
}