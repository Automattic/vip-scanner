<?php

class VIPWhitelistCheck extends BaseCheck
{
	function check( $files ) {
		$result = true;

		$php = $this->merge_files( $files, 'php' );

		$checks = array(
			"/<!DOCTYPE\s+html([^>]{0,})/msiU" => array( "level" => "Warning", "note" => "No doctype defined" ),
			"/<html.+(language_attributes){1}([^>]{0,})/msiU" => array( "level" => "Warning", "note" => "No language_attributes() in html tag" ),
			"/<head.+profile=\"(.+)\"([^>]{0,})/msiU" => array( "level" => "Warning", "note" => "Profile attribute missing in head tag" ),
			//"/<meta\sname=\"generator\"\scontent=\".*WordPress.*\"([^>]+)/msiU" => array( "level" => "Warning", "note" => "Meta tag generator not set or not wordpress.com" ),
			"/(wp_head)+\s?\(\)/msiU" => array( "level" => "Blocker", "note" => "wp_head() call missing" ),
			"/(wp_footer)+\s?\(\)/msiU" => array( "level" => "Blocker", "note" => "wp_footer() call missing" ),
			"/<a.+href=[\"|']?(http:\/\/en\.wordpress\.com\/vip-hosting\/).[\"|']?([^>]+).+Wordpress\.com\sVIP([^<]+)</msiU" => array( "level" => "Warning", "note" => "Attribution link missing or not well formatted" ),
		);

		foreach ( $checks as $check => $check_info ) {
			$this->increment_check_count();
			if ( ! preg_match( $check, $php ) ) {
				$this->add_error(
					$check,
					$check_info['note'],
					$check_info['level']
				);
				$result = false;
			}
		}

		return $result;
	}
}