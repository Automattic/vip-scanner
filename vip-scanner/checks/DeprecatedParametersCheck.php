<?php
/**
 * Checks for deprecated parameters.
 */

class DeprecatedParametersCheck extends BaseCheck {

	function check( $files ) {
		$result = true;
		$this->increment_check_count();

		$checks = array(
			'get_bloginfo' => array(
				'home'                 => 'home_url()',
				'url'                  => 'home_url()',
				'wpurl'                => 'site_url()',
				'stylesheet_directory' => 'get_stylesheet_directory_uri()',
				'template_directory'   => 'get_template_directory_uri()',
				'template_url'         => 'get_template_directory_uri()',
				'text_direction'       => 'is_rtl()',
				'feed_url'             => "get_feed_link( 'feed' ), where feed is rss, rss2 or atom",
			),
			'bloginfo' => array(
				'home'                 => 'echo esc_url( home_url() )',
				'url'                  => 'echo esc_url( home_url() )',
				'wpurl'                => 'echo esc_url( site_url() )',
				'stylesheet_directory' => 'echo esc_url( get_stylesheet_directory_uri() )',
				'template_directory'   => 'echo esc_url( get_template_directory_uri() )',
				'template_url'         => 'echo esc_url( get_template_directory_uri() )',
				'text_direction'       => 'is_rtl()',
				'feed_url'             => "echo esc_url( get_feed_link( 'feed' ) ), where feed is rss, rss2 or atom",
			),
			'get_option' => array(
				'home'     => 'home_url()',
				'site_url' => 'site_url()',
			)
		);	

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {
			
			// Loop through all functions.
			foreach ( $checks as $function => $data ) {

				// Loop through the parameters and look for all function/parameter combinations.
				foreach ( $data as $parameter => $replacement ) {

					if ( preg_match( '/' . $function . '\(\s*("|\')' . $parameter . '("|\')\s*\)/', $file_content, $matches ) ) {
						$error = trim( rtrim( $matches[0], '(' ) );
						$this->add_error(
							'deprecated',
							'The deprecated function parameter <code>' . esc_html( $error ) . '</code> was found. Use <code>' . esc_html( $replacement ) . '</code> instead.',
							BaseScanner::LEVEL_BLOCKER,
							$this->get_filename( $file_path )
						);
						$result = false;
					}
				}
			}
		}

		return $result;
	}
}
