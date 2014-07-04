<?php
/**
 * Checks for deprecated constants:
 * STYLESHEETPATH
 * TEMPLATEPATH
 */

class DeprecatedConstantsCheck extends BaseCheck {

	function check( $files ) {
		$result = true;
		$this->increment_check_count();

		$checks = array(
			'STYLESHEETPATH' => 'get_stylesheet_directory()',
			'TEMPLATEPATH'   => 'get_template_directory()',
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {

			foreach ( $checks as $check => $replacement ) {
				
				/**
				 * Before a constant, there's either a start of a line, whitespace, . or (
				 * This is to avoid false positives.
				 */
				if ( preg_match( '/(?:^|[\s\.\(])' . $check . '/m', $file_content, $matches ) ) {
					$deprecated = trim( $matches[0] );

					$this->add_error(
						'deprecated',
						'The constant <code>' . esc_html( $deprecated ) . '</code> is deprecated. Use <code>' . esc_html( $replacement ) . '</code> instead.',
						BaseScanner::LEVEL_BLOCKER,
						$this->get_filename( $file_path )
					);
					$result = false;
				}
			}

		}

		return $result;
	}
}
