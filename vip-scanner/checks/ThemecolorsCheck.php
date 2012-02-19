<?php

/**
 * WPCOM: Themes are required to set the global $themecolors array.
 */
class ThemecolorsCheck extends BaseCheck {

	function check( $files ) {
		$result = true;

		/* combine all the php files into one string to make it easier to search. */
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