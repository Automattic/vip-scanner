<?php
/**
 * Checks for jQuery:
 * Check for deprecated .live() method.
 */

class jQueryCheck extends BaseCheck {

	function check( $files ) {
		$result = true;

		$js_files = $this->filter_files( $files, 'js' );
		$js_code  = $this->merge_files( $files, 'js' );

		/**
		 * Check for deprecated .live() method.
		 */
		$this->increment_check_count();
		foreach( $js_files as $file_path => $file_content ) {
			if ( false !== strpos( $file_content, '.live(' ) ) {
				$this->add_error(
					'jquery',
					'The jQuery <code>.live()</code> method is deprecated.',
					Basescanner::LEVEL_BLOCKER,
					$this->get_filename( $file_path )
				);
				$result = false;
			}
		}

		return $result;
	}
}
