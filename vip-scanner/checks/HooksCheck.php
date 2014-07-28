<?php
/**
 * Checks for correct usage of actions and filters:
 * Check whether the init action is used.
 */

class HooksCheck extends BaseCheck {

	function check( $files ) {
		$result = true;

		$php_files = $this->filter_files( $files, 'php' );

		/**
		 * Check whether the init action is used..
		 */
		$this->increment_check_count();

		foreach( $php_files as $file_path => $file_content ) {
			if ( preg_match( '/add_action\(\s*[\'"]init[\'"]/', $file_content ) ) {
				$this->add_error(
					'hooks',
					"Found <code>add_action( 'init', [...]</code>. Themes should not use the <code>init</code> action.",
					Basescanner::LEVEL_BLOCKER,
					$this->get_filename( $file_path )
				);
				$result = false;
			}
		}

		return $result;
	}
}
