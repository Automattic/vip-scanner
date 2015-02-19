<?php
/**
 * Checks for correct implementation of Jetpack Features.
 * Checks for responsive video support if the theme declares responsive-layout in the tags.
 */

class JetpackCheck extends BaseCheck {

	function check( $files ) {
		$result = true;

		$php_code = $this->merge_files( $files, 'php' );

		/**
		 * Grep the tags from the stylesheets.
		 */
		$css = $this->merge_files( $files, 'css' );
		$tags = array();

		if ( preg_match('|Tags:(.*)|i', $css, $tags) ) {
			$tags = array_map( 'trim', explode( ',', wp_kses( trim( $tags[1] ), array() ) ) );
		}

		/**
		 * Check for Responsive Videos support if the theme is responsive.
		 */
		$this->increment_check_count();

		if ( in_array( 'responsive-layout', $tags ) ) {
			if ( ! preg_match( '/[\s|]add_theme_support\((\s|)("|\')jetpack-responsive-videos("|\')(\s|)\)/m', $php_code ) ) {
				$this->add_error(
					'jetpack',
					"The theme has not declared support for Jetpack Responsive Videos. Use <code>add_theme_support( 'jetpack-responsive-videos' );</code> in your <code>inc/jetpack.php</code> file",
					Basescanner::LEVEL_BLOCKER
				);
				$result = false;
			}
		}

		return $result;
	}
}
