<?php
/**
 * Checks for jQuery:
 * Check for deprecated .live() method.
 * Check for deprecated $(document).on( "ready", handler ).
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
					'The jQuery <code>.live()</code> method is deprecated. Use <code>.on()</code> instead to attach event handlers.',
					Basescanner::LEVEL_BLOCKER,
					$this->get_filename( $file_path )
				);
				$result = false;
			}
		}

		/**
		 * Check for deprecated $(document).on( "ready", handler )
		 */
		$this->increment_check_count();
		foreach( $js_files as $file_path => $file_content ) {
			if ( ! preg_match( '/\$\(\s*document\s*\)\.on\(\s*[\'"]ready[\'"]\s*,\s*function\(\)/', $file_content ) ) {
				$this->add_error(
					'jquery',
					'The jQuery <code>$(document).on( "ready", handler )</code> handler is deprecated deprecated as of jQuery 1.8.',
					Basescanner::LEVEL_BLOCKER,
					$this->get_filename( $file_path )
				);
				$result = false;
			}
		}

		return $result;
	}
}
