<?php
/**
 * Checks for Masonry v3, that comes bundled with WordPress 3.9.
 * Check if the theme uses the old 'jquery-masonry' script handle.
 */

class MasonryCheck extends BaseCheck {

	function check( $files ) {
		$result = true;

		$php_files = $this->filter_files( $files, 'php' );

		/**
		 * Check for 'jquery-masonry' script handle.
		 */
		$this->increment_check_count();

		foreach( $php_files as $file_path => $file_content ) {
			if ( false !== strpos( $file_content, 'jquery-masonry' ) ) {
				$this->add_error(
					'masonry',
					'Found the <code>jquery-masonry</code> script handle. WordPress 3.9 upgraded Masonry to v3, that no longer requires jQuery. The new script handle to use is <code>masonry</code>.',
					Basescanner::LEVEL_BLOCKER,
					$this->get_filename( $file_path )
				);
				$result = false;
			}
		}

		return $result;
	}
}
