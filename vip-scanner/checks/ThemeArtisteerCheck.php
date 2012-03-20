<?php

class ThemeArtisteerCheck extends BaseCheck {
	function check( $files ) {

		$result = true;
		$this->increment_check_count();

		$php = $this->merge_files( $files, 'php' );
		if (
			strpos( $php, 'art_normalize_widget_style_tokens' ) !== false
			|| strpos( $php, 'adi_normalize_widget_style_tokens' ) !== false
			|| strpos( $php, 'm_normalize_widget_style_tokens' ) !== false
			|| strpos ( $php, "bw = '<!--- BEGIN Widget --->';" ) !== false
			|| strpos ( $php, "ew = '<!-- end_widget -->';" ) !== false
			|| strpos ( $php, "end_widget' => '<!-- end_widget -->'") !== false
		) {
			$this->add_error(
				'theme-artisteer',
				'This theme appears to have been auto-generated. Generated themes are not allowed in the themes directory.',
				'warning'
			);
			$result = false;
		}
		return $result;
	}
}