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
			"/(pre_)?option_(blogname|siteurl|post_count)/msiU" => array( "level" => "Blocker", "note" => "possible unsafe use of pre_option_* hook"),
			'/(\xFE|\xFF|\xFE\xFF|\xFF\xFE|\xEF\xBB\xBF|\x2B\x2F\x76|\xF7\x64\x4C|\x0E\xFE\xFF|\xFB\xEE\x28|\x00\x00\xFE\xFF|\xDD\x73\x66\x73|\x84\x31\x95\x33)/' => array( 'level' => 'Blocker', 'note' => 'Byte-Order Marks should not be used in PHP files as they can cause undesired output' ),
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
