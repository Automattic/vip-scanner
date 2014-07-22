<?php
/**
 * Checks for the theme name:
 *
 * Looks for forbidden words in the name.
 */

class ThemeNameCheck extends BaseCheck {

	function check( $files ) {

		$result = true;

		/**
		 * Extract the theme name from style.css.
		 */
		$css = $this->merge_files( $files, 'css' );

		preg_match( '|Theme Name:(.*)$|mi', $css, $theme_name );
		$name = ( isset( $theme_name[1] ) ) ? wp_kses( trim( $theme_name[1] ), array() ) : '';

		/**
		 * Check if the theme name exists.
		 */
		$this->increment_check_count();

		if ( empty( $name ) ) {
			$this->add_error(
				'theme-name',
				'The theme name needs to be indicated in style.css.',
				Basescanner::LEVEL_BLOCKER
			);
			$result = false;

			// There is no point in continuing of there is no theme name.
			return $result;
		}

		/**
		 * List of all the words (case insensitive) that can't be part of the theme name.
		 */
		$this->increment_check_count();

		$forbidden_words = array(
			'blog',
			'creative',
			'css3',
			'css 3',
			'framework',
			'html5',
			'html 5',
			'pro',
			'responsive',
			'skin',
			'template',
			'theme',
			'Twenty',
			'WordPress',
			'WordPress.com',
			'WP',
			'WP.com',
		);

		foreach ( $forbidden_words as $word ) {
			if ( preg_match( '/\s*' . $word . '(\s|$)/i', $name ) ) {
				$this->add_error(
					'theme-name',
					'Found the word <em>' . esc_html( $word ) . '</em> in the theme name. This word is not allowed, please remove it.',
					Basescanner::LEVEL_BLOCKER
				);
				$result = false;
			}
		}

		return $result;
	}
}
