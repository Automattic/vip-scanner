<?php

class ThemeContentWidthCheck extends BaseCheck {

	function check( $files ) {

		$result = true;
		$this->increment_check_count();

		$php = $this->merge_files( $files, 'php' );

		if ( strpos( $php, '$content_width' ) === false && !preg_match( '/add_filter\((\s|)("|\')embed_defaults/', $php ) ) {
			$this->add_error(
				'theme-content-width',
				'No content width has been defined, e.g. `if ( ! isset( $content_width ) ) $content_width = 900;`',
				'required'
			);
			$result = false;
		}
		return $result;
	}
}