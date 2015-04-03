<?php

class URISchemeCheck extends BaseCheck
{
	function sanitize_string( $string ) {
		/**
		 * Removes Javascript/CSS comment blocks
		 */
		$string = preg_replace( '/(\/\*(?:.*)?\*\/)/misU', '', $string );

		/**
		 * Removes Javascript inline comments
		 */
		$string = preg_replace( '/(?<![:])(\/\/(?:.*?)$)/mis', '', $string );

		/**
		 * Removes HTML comment blocks
		 */
		$string = preg_replace( '/(<!--(?:.*)?-->)/misU', '', $string );

		return $string;
	}

	function check( $files ) {
		$result = true;

		/*
		 * CSS and JS files
		 */

		$checks = array(
			'hardcoded-http-scheme' => array(
				'expression' => '/(?<MATCHTEXT>(http):\/\/([\da-z\.-]+[a-z\.]{2,6})(?:[\/\w \.-]*)*)/msi',
				'level'      => 'Warning',
				'note'       => 'Hardcoded URL Scheme.  To prevent "Mixed Content" security warnings, it may be better to use <a href="http://en.wikipedia.org/wiki/Uniform_resource_locator#Protocol-relative_URLs">Protocol-Relative URLs</a>',
			),
		);

		foreach ( $this->filter_files( $files,array( 'css', 'js' ) ) as $file_path => $file_content ) {
			foreach ( $checks as $check => $check_info ) {
				$this->increment_check_count();
				$sanitized_string = $this->sanitize_string( $file_content );
				if ( preg_match_all( $check_info['expression'], $sanitized_string, $matches ) ) {
					$lines = array();
					foreach ( $matches['MATCHTEXT'] as $match ) {
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
						$sanitized_string = $this->sanitize_string( $match );
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
