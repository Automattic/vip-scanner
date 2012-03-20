<?php

class ThemeCustomizeCheck extends BaseCheck {
	function check( $files ) {

		$result = true;
		$this->increment_check_count();

		$php = $this->merge_files( $files, 'php' );

		if ( strpos( $php, 'add_custom_image_header' ) === false ) {
			$this->add_error(
				'add_custom_image_header',
				'No reference to `add_custom_image_header` was found in the theme. It is recommended that the theme implement this functionality if using an image for the header.',
				'recommended'
			);
		}

		if ( strpos( $php, 'add_custom_background' ) === false ) {
			$this->add_error(
				'add_custom_background',
				'No reference to `add_custom_background()` was found in the theme. If the theme uses background images or solid colors for the background, then it is recommended that the theme implement this functionality.',
				'recommended'
			);
		}
		return $result;
	}
}