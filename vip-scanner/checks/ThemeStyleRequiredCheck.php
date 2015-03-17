<?php

/**
 * Checks for required CSS classes.
 */


class ThemeStyleRequiredCheck extends BaseCheck {
	function check( $files ) {
		$result = true;

		$css = $this->merge_files( $files, 'css' );

		$classes = array(
			'alignleft',
			'alignright',
			'aligncenter',
			'wp-caption',
			'wp-caption-text',
			'gallery-caption',
		);

		foreach ( $classes as $class ) {
			$this->increment_check_count();

			if ( ! preg_match( '/\.' . $class . '/mi', $css, $matches ) ) {
				$this->add_error(
					'required-style-header-missing',
					sprintf( esc_html__( 'The %1$s CSS class needs to be added to the stylesheet.', 'vip-scanner' ),
						'<code>.' . esc_html( $class ) . '</code>'
					),
					BaseScanner::LEVEL_BLOCKER
				);
				$result = false;
			}
		}

		return $result;
	}
}