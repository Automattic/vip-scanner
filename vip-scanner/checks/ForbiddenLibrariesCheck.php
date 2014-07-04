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
		 * Check for timthumb.php.
		 */
		$this->increment_check_count();

		if ( $this->file_exists( $files, 'timthumb.php' ) ) {
			$this->add_error(
				'forbidden-library',
				'Found <code>timthumb.php</code>. The use of this library is not allowed.',
				BaseScanner::LEVEL_BLOCKER,
				$this->get_filename( $file_path )
			);
			$result = false;
		}

		/**
		 * Check for Options Framework.
		 */
		$this->increment_check_count();

		if ( $this->file_exists( $files, 'options-framework.php' ) ) {
			$this->add_error(
				'forbidden-library',
				'Found <code>options-framework.php</code>. The use of this library is not allowed.',
				BaseScanner::LEVEL_BLOCKER,
				$this->get_filename( $file_path )
			);
			$result = false;
		}
		
		return $result;
	}
}
