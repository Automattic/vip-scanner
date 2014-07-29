<?php
/**
 * Checks for correct implementation for the use of the upcoming language packs.
 * Check for correct call to load_theme_textdomain().
 * Check if the text domain is set.
 * Check if the domain path is /languages/.
 */

class LanguagePacksCheck extends BaseCheck {

	function check( $files ) {
		$result = true;

		$php_code = $this->merge_files( $files, 'php' );
		$css_code = $this->merge_files( $files, 'css' );

		/**
		 * Check for correct call to load_theme_textdomain().
		 */
		$this->increment_check_count();

		if ( ! preg_match( '/load_theme_textdomain\(\s*[\'"][^\'"]+[\'"],\s*get_template_directory\(\)\s*\.\s*[\'"]\/languages[\'"]\s*\);/', $php_code ) ) {
			$this->add_error(
				'language-packs',
				"You need a call to <code>load_theme_textdomain( 'theme-slug', get_template_directory() . '/languages' );</code> in your theme setup function, hooked to <code>after_setup_theme</code>.",
				Basescanner::LEVEL_BLOCKER
			);
			$result = false;
		}

		/**
		 * Check for Text Domain.
		 */
		if ( ! preg_match( '/Text Domain:(.*)$/mi', $css_code ) ) {
			$this->add_error(
				'language-packs',
				'You need to indicate a text domain in your <code>style.css</code> file header.',
				Basescanner::LEVEL_BLOCKER
			);
			$result = false;
		}

		/**
		 * Check for Domain Path.
		 */
		$domain_path = array();
		preg_match( '/Domain Path:(.*)$/mi', $css_code, $domain_path );

		if ( '/languages/' != trim( $domain_path[1] ) ) {
			$this->add_error(
				'language-packs',
				'You need to indicate a domain path in your <code>style.css</code> file header: <code>Domain Path: /languages/</code>.',
				Basescanner::LEVEL_BLOCKER
			);
			$result = false;
		}

		return $result;
	}
}
