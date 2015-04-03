<?php

class URISchemeCheck extends BaseCheck
{
	function sanitize_string( $string, $types = '' ) {
		if ( is_array( $types ) ) {
			foreach ( $types as $type ) {

				if ('js' == $type || 'css' == $type || 'php' == $type ) {
					/**
					 * Removes C style comment blocks
					 */
					$string = preg_replace( '/(\/\*(?:.*)?\*\/)/misU', '', $string );
				}
				
				if ( 'js' == $type || 'php' == $type ) {
					/**
					 * Removes C++ style inline comments
					 */
					$string = preg_replace( '/(?<![:])(\/\/(?:.*?)$)/mis', '', $string );
				}

				if ( 'html' == $type ) {
					/**
					 * Removes HTML comment blocks
					 */
					$string = preg_replace( '/(<!--(?:.*)?-->)/misU', '', $string );
				}

			}
		}

		return $string;
	}

	function check( $files ) {
		$result = true;

		/*
		 * CSS and JS files
		 */

		$checks = array(
			'hardcoded-http-scheme' => array(
				'expression' => '/(?<MATCHTEXT>http:\/\/)/msi',
				'level'      => 'Warning',
				'note'       => 'Hardcoded URL Scheme.  To prevent "Mixed Content" security warnings, it may be better to use <a href="http://en.wikipedia.org/wiki/Uniform_resource_locator#Protocol-relative_URLs">Protocol-Relative URLs</a>',
			),
		);

		foreach ( $this->filter_files( $files, 'css' ) as $file_path => $file_content ) {
			foreach ( $checks as $check => $check_info ) {
				$this->increment_check_count();
				$sanitized_string = $this->sanitize_string( $file_content, array( 'css') );
				if ( preg_match_all( $check_info['expression'], $sanitized_string, $matches ) ) {
					$lines = array();
					foreach ( $matches['MATCHTEXT'] as $match ) {
						$filename = $this->get_filename( $file_path );
						$lines = array_merge( $this->grep_content( $match, $sanitized_string ), $lines );
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
		}

		/*
		 * PHP Files
		 */

		$checks = array(
			'script-and-style-link-hardcoded-http-scheme' => array(
				'expression' => '/(?:wp_enqueue_script|wp_enqueue_style|wp_register_script|wp_register_style)\((?<MATCHTEXT>.*?)\);/msi',
				'match-text' => 'http://',
				'level'      => 'Warning',
				'note'       => 'Hardcoded URL Scheme.  To prevent "Mixed Content" security warnings, it may be better to use <a href="http://en.wikipedia.org/wiki/Uniform_resource_locator#Protocol-relative_URLs">Protocol-Relative URLs</a>',
			),
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			foreach ( $checks as $check => $check_info ) {
				$this->increment_check_count();
				if ( preg_match_all( $check_info['expression'], $file_content, $matches ) ) {
					$lines = array();
					foreach ( $matches['MATCHTEXT'] as $match ) {
						$sanitized_string = $this->sanitize_string( $match, array( 'php' ) );
						if ( stripos( $sanitized_string, $check_info['match-text'] ) !== false ) {
							$filename = $this->get_filename( $file_path );
							$lines = array_merge( $this->grep_content( $match, $file_content ), $lines );
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
			}
		}

		return $result;
	}
}
