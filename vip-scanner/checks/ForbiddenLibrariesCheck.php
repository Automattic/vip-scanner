<?php
/**
 * Checks for forbidden libraries:
 * TimThumb
 * Options Framework
 */

class ForbiddenLibrariesCheck extends BaseCheck {

	function check( $files ) {
		$result = true;

		/**
		 * List of forbidden libraries
		 */
		$checks = array(
			'timthumb.php',
			'options-framework.php',
			'isotope.js',
		);

		/**
		 * Check for the libraries.
		 */
		foreach( $checks as $library ) {
			$this->increment_check_count();

			if ( $this->file_exists( $files, $library ) ) {
				$this->add_error(
					'forbidden-library',
					'Found <code>' . esc_html( $library ) . '</code> in the theme. The use of this library is not allowed.',
					BaseScanner::LEVEL_BLOCKER
				);
				$result = false;
			}
		}
		
		return $result;
	}
}
