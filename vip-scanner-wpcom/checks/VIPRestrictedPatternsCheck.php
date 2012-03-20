<?php

class VIPRestrictedPatternsCheck extends BaseCheck
{
	function check( $files ) {
		$result = true;

		$checks = array(
			"/(kses)+/msiU" => array ( "level" => "Warning", "note" => "Working with kses" ),
			"/(\\\$wpdb->|mysql_|WP_Query)+.+(ALTER)+\s+/msiU" => array( "level" => "Blocker", "note" => "Possible database table alteration" ),
			"/(\\\$wpdb->|mysql_|WP_Query)+.+(CREATE)+\s+/msiU" => array( "level" => "Blocker", "note" => "Possible database table creation" ),
			"/(\\\$wpdb->|mysql_|WP_Query)+.+(DROP)+\s+/msiU" => array( "level" => "Blocker", "note" => "Possible database table deletion" ),
			"/(\\\$wpdb->|mysql_|WP_Query)+.+(DELETE)+\s+(FROM)+\s+/msiU" => array( "level" => "Note", "note" => "Direct database delete query" ),
			"/(\\\$wpdb->|mysql_|WP_Query)+.+(SELECT)+\s.+/msiU" => array( "level" => "Note", "note" => "Direct Database select query" ),
			"/(^GLOBAL)(\\\$wpdb->|mysql_|WP_Query)+/msiU" => array( "level" => "Warning", "note" => "Possible direct database query" ),
			"/(echo|print|\<\?\=)+.+(\\\$GLOBALS|\\\$_SERVER|\\\$_GET|\\\$_REQUEST|\\\$_POST)+/msiU" => array( "level" => "Warning", "note" => "Possible output of restricted variables" ),
			"/(echo|print|\<\?\=)+.+(get_search_query)+/msiU" => array( "level" => "Warning", "note" => "Output of search query" ),
			"/(\\\$GLOBALS|\\\$_SERVER|\\\$_GET|\\\$_REQUEST|\\\$_POST)+/msiU" => array( "level" => "Note", "note" => "Working with superglobals" ),
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			foreach ( $checks as $check => $check_info ) {
				$this->increment_check_count();

				if ( preg_match( $check, $file_content, $matches ) ) {
					$filename = $this->get_filename( $file_path );
					$error = rtrim( $matches[0], '(' );//esc_html( rtrim( $matches[0],'(') );
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