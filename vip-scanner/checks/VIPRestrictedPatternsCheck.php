<?php

class VIPRestrictedPatternsCheck extends BaseCheck
{
	function check( $files ) {
		$result = true;

		$checks = array(
			"/(\\\$isIE)+/msiU" => array( "level" => "Warning", "note" => 'Using $isIE conflicts with full page caching' ),
			"/(\\\$_REQUEST)+/msiU" => array( "level" => "Blocker", "note" => 'Using $_REQUEST is forbidden. You should use $_POST or $_GET' ),
			"/WordPress VIP/msiU" => array( "level" => "Warning", "note" => 'Please use "WordPress.com VIP" rather than "WordPress VIP"' ),
			"/(\\\$wpdb->|mysql_)+.+(ALTER)+\s+/msiU" => array( "level" => "Blocker", "note" => "Possible database table alteration" ),
			"/(\\\$wpdb->|mysql_)+.+(CREATE)+\s+/msiU" => array( "level" => "Blocker", "note" => "Possible database table creation" ),
			"/(\\\$wpdb->|mysql_)+.+(DROP)+\s+/msiU" => array( "level" => "Blocker", "note" => "Possible database table deletion" ),
			"/(\\\$wpdb->|mysql_)+.+(DELETE)+\s+(FROM)+\s+/msiU" => array( "level" => "Warning", "note" => "Direct database delete query" ),
			"/(\\\$wpdb->|mysql_)+.+(SELECT)+\s.+/msiU" => array( "level" => "Note", "note" => "Direct Database select query" ),
			"/(^GLOBAL)(\\\$wpdb->|mysql_)+/msiU" => array( "level" => "Warning", "note" => "Possible direct database query" ),
			"/(echo|print|\<\?\=)+.+(\\\$GLOBALS|\\\$_SERVER|\\\$_GET|\\\$_POST|\\\$_REQUEST)+/msiU" => array( "level" => "Warning", "note" => "Possible output of restricted variables" ),
			"/(\\\$GLOBALS|\\\$_SERVER|\\\$_GET|\\\$_POST|\\\$_REQUEST)+/msiU" => array( "level" => "Note", "note" => "Working with superglobals" ),
			"/(\\\$_SERVER\[(?!('|\"REQUEST_URI|SCRIPT_FILENAME|HTTP_HOST'|\"))([^]]+|)\])+/msiU" => array( "level" => "Blocker", "note" => 'Non whitelisted $_SERVER superglobals found in this file' ),
			"/pre_option_(blogname|siteurl|post_count)/msiU" => array( "level" => "Blocker", "note" => "possible unsafe use of pre_option_* hook"),
			"/\\\$wp_query->query_vars\[.*?\][^=]*?\;/msi" => array( "level" => "Warning", "note" => "Possible direct query_vars modification, should use get_query_vars() function" )
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			foreach ( $checks as $check => $check_info ) {
				$this->increment_check_count();

				if ( preg_match( $check, $file_content, $matches ) ) {
					$filename = $this->get_filename( $file_path );
					$lines = $this->grep_content( $matches[0], $file_content );
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
