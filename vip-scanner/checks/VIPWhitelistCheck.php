<?php

class VIPWhitelistCheck extends BaseCheck
{
	function check( $files ) {
		$result = true;

		$php = $this->merge_files( $files, 'php' );

		$checks = array(
			'no-doctype-defined' => array(
				'expression' => '/<!DOCTYPE\s+html([^>]{0,})/msiU',
				'level'      => 'Warning',
				'note'       => 'No doctype defined',
			),
			'missing-language_attributes' => array(
				'expression' => '/<html.+(language_attributes){1}([^>]{0,})/msiU',
				'level'      => 'Warning',
				'note'       => 'No language_attributes() in html tag',
			),
			'missing-profile-attribute' => array(
				'expression' => '/<head.+([^>]{0,})/msiU',
				'level'      => 'Warning',
				'note'       => 'Profile attribute missing in head tag',
			),
			/*
			'meta-tag-generator-not-set' => array(
				'expression' => '/<meta\sname="generator"\scontent=".*WordPress.*"([^>]+)/msiU',
				'level'      => 'Warning',
				'note'       => 'Meta tag generator not set or not wordpress.com',
			),
			*/
			'missing-wp_head' => array(
				'expression' => '/(wp_head)+\s?\(\)/msiU',
				'level'      => 'Blocker',
				'note'       => 'wp_head() call missing',
			),
			'missing-wp_footer' => array(
				'expression' => '/(wp_footer)+\s?\(\)/msiU',
				'level'      => 'Blocker',
				'note'       => 'wp_footer() call missing',
			),
			'missing-vip-attribution-link' => array(
				'expression' => '/(vip_powered_wpcom)+s?\([^\)]*\)/msiU',
				'level'      => 'Blocker',
				'note'       => 'Attribution link missing, please use <a href="http://vip.wordpress.com/documentation/powered-by-wordpress-com-vip/">vip_powered_wpcom()</a>',
			),
		);

		foreach ( $checks as $check => $check_info ) {
			$this->increment_check_count();
			if ( ! preg_match( $check_info['expression'], $php ) ) {
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