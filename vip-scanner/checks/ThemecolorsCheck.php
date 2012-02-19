<?php

/**
 * WPCOM: Themes are required to set the global $themecolors array.
 */
class ThemecolorsCheck extends BaseCheck {

	/**
	 * Merge all php files into on string and search for
	 * the $themecolors array. Set an error if not found.
	 */
	function check( $files ) {
		$result = true;

		$php = $this->merge_files( $files, 'php' );

		$this->increment_check_count();
		if ( false === strpos( $php, '$themecolors' ) ) {
			$this->add_error(
				'themecolors',
				'The <code>$themecolors</code> array could not be found in any file. Please ensure that this variable is set at <code>template_redirect</code> or earlier.',
				'blocker'
			);
			$result = false;
		}

		return $result;
	}
}