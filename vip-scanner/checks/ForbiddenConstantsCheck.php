<?php
/**
 * Checks for constants that themes can't use:
 * PLUGINDIR
 * WP_PLUGIN_DIR
 * MUPLUGINDIR
 * WPMU_PLUGIN_DIR
 * IS_WPCOM
 */

class ForbiddenConstantsCheck extends BaseCheck {

	function check( $files ) {
		$result = true;
		$this->increment_check_count();

		$checks = array(
			'PLUGINDIR',
			'WP_PLUGIN_DIR',
			'MUPLUGINDIR',
			'WPMU_PLUGIN_DIR',
			'IS_WPCOM',
		);

		foreach ( $this->filter_files( $files, 'php' ) as $file_path => $file_content ) {

			foreach ( $checks as $check ) {
				
				/**
				 * Before a constant, there's either a start of a line, whitespace, . or (
				 * This is to avoid false positives.
				 */
				if ( preg_match( '/(?:^|[\s\.\(])' . $check . '/m', $file_content, $matches ) ) {
					$error= trim( $matches[0] );

					$this->add_error(
						'forbidden',
						'Themes cannot use the constant <code>' . esc_html( $error ) . '</code>.',
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
